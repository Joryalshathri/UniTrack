"""
Attendance management routes (marking, viewing, reports)
"""
from flask import Blueprint, render_template, request, jsonify, session
from app.database import db
from app.forms import MarkAttendanceForm
from app.routes.auth import require_login, require_role, get_current_user
from datetime import datetime, timedelta

attendance_bp = Blueprint('attendance', __name__, url_prefix='/attendance')

# ============================================================================
# MARK ATTENDANCE
# ============================================================================
@attendance_bp.route('/mark', methods=['GET', 'POST'])
@require_login
@require_role('admin', 'teacher')
def mark_attendance():
    """Mark attendance for students"""
    if request.method == 'GET':
        # Get all active students
        query = """
            SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number, u.email
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.is_active = true
            ORDER BY u.first_name, u.last_name
        """
        students = db.fetch_all(query)
        current_user = get_current_user()
        
        # Check if attendance already marked today
        today = datetime.now().date()
        check_query = """
            SELECT COUNT(*) as count FROM attendance
            WHERE DATE(attendance_date) = %s
        """
        count = db.fetch_one(check_query, (today,))
        marked_today = count['count'] > 0
        
        return render_template('attendance/mark.html', students=students, 
                             marked_today=marked_today, current_user=current_user)
    
    # POST request - save attendance
    try:
        data = request.json if request.is_json else request.form
        attendance_records = data.get('attendance', [])
        
        if not attendance_records:
            return jsonify({'success': False, 'error': 'No attendance records provided'}), 400
        
        saved_count = 0
        errors = []
        
        for record in attendance_records:
            try:
                student_id = record.get('student_id')
                status = record.get('status')
                remarks = record.get('remarks', '')
                
                if not student_id or not status:
                    continue
                
                # Check if student exists
                student_check = db.fetch_one("SELECT student_id FROM students WHERE student_id = %s", (student_id,))
                if not student_check:
                    errors.append(f"Student ID {student_id} not found")
                    continue
                
                # Check if attendance already marked for today
                today = datetime.now().date()
                check_query = """
                    SELECT attendance_id FROM attendance
                    WHERE student_id = %s AND DATE(attendance_date) = %s
                """
                existing = db.fetch_one(check_query, (student_id, today))
                
                if existing:
                    # Update existing record
                    update_query = """
                        UPDATE attendance
                        SET status = %s, remarks = %s, marked_by = %s
                        WHERE attendance_id = %s
                    """
                    db.execute(update_query, (status, remarks, session.get('user_id'), existing['attendance_id']))
                else:
                    # Insert new record
                    insert_query = """
                        INSERT INTO attendance (student_id, attendance_date, status, marked_by, remarks)
                        VALUES (%s, CURRENT_TIMESTAMP, %s, %s, %s)
                    """
                    db.execute(insert_query, (student_id, status, session.get('user_id'), remarks))
                
                saved_count += 1
            
            except Exception as e:
                errors.append(f"Error for student {record.get('student_id')}: {str(e)}")
        
        message = f"Attendance saved for {saved_count} student(s)"
        if errors:
            message += f". {len(errors)} error(s) occurred"
        
        return jsonify({
            'success': True,
            'message': message,
            'saved_count': saved_count,
            'errors': errors
        }), 200
    
    except Exception as e:
        return jsonify({'success': False, 'error': f'Error saving attendance: {str(e)}'}), 500

# ============================================================================
# VIEW ATTENDANCE REPORT
# ============================================================================
@attendance_bp.route('/report', methods=['GET'])
@require_login
def attendance_report():
    """View attendance report for all students"""
    try:
        # Get filter parameters
        days = request.args.get('days', 30, type=int)
        student_id = request.args.get('student_id', None, type=int)
        status_filter = request.args.get('status', None, type=str)
        
        # Calculate date range
        end_date = datetime.now().date()
        start_date = end_date - timedelta(days=days)
        
        # Build query
        where_clause = "WHERE DATE(a.attendance_date) BETWEEN %s AND %s"
        params = [start_date, end_date]
        
        if student_id:
            where_clause += " AND a.student_id = %s"
            params.append(student_id)
        
        if status_filter:
            where_clause += " AND a.status = %s"
            params.append(status_filter)
        
        # Get attendance records
        query = f"""
            SELECT a.attendance_id, a.attendance_date, a.status, a.remarks,
                   s.student_id, s.enrollment_number,
                   u.first_name, u.last_name, u.email,
                   m.first_name as marked_by_first, m.last_name as marked_by_last
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN users m ON a.marked_by = m.user_id
            {where_clause}
            ORDER BY a.attendance_date DESC, u.first_name, u.last_name
        """
        
        records = db.fetch_all(query, params)
        
        # Get attendance summary
        summary_query = f"""
            SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number,
                   COUNT(*) as total_days,
                   SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                   SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                   SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                   ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_percentage
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            JOIN users u ON s.user_id = u.user_id
            {where_clause}
            GROUP BY s.student_id, u.first_name, u.last_name, s.enrollment_number
            ORDER BY attendance_percentage DESC, u.first_name, u.last_name
        """
        
        summary = db.fetch_all(summary_query, params)
        
        # Get list of all students for filter dropdown
        students_list = db.fetch_all("""
            SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.is_active = true
            ORDER BY u.first_name, u.last_name
        """)
        
        current_user = get_current_user()
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({
                'success': True,
                'records': records,
                'summary': summary
            })
        
        return render_template('attendance/report.html', records=records, summary=summary,
                             students_list=students_list, days=days, student_id=student_id,
                             status_filter=status_filter, current_user=current_user)
    
    except Exception as e:
        return render_template('error.html', error=f'Error generating report: {str(e)}'), 500

# ============================================================================
# GET ATTENDANCE FOR SPECIFIC DATE
# ============================================================================
@attendance_bp.route('/for-date', methods=['GET'])
@require_login
def attendance_for_date():
    """Get attendance for a specific date"""
    try:
        date_str = request.args.get('date', None)
        
        if not date_str:
            return jsonify({'success': False, 'error': 'Date parameter required'}), 400
        
        query = """
            SELECT a.attendance_id, a.student_id, a.status, a.remarks,
                   u.first_name, u.last_name, s.enrollment_number
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            JOIN users u ON s.user_id = u.user_id
            WHERE DATE(a.attendance_date) = %s
            ORDER BY u.first_name, u.last_name
        """
        
        records = db.fetch_all(query, (date_str,))
        
        return jsonify({
            'success': True,
            'attendance': records,
            'date': date_str
        }), 200
    
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

# ============================================================================
# EDIT ATTENDANCE RECORD
# ============================================================================
@attendance_bp.route('/<int:attendance_id>/edit', methods=['POST'])
@require_login
@require_role('admin', 'teacher')
def edit_attendance(attendance_id):
    """Edit attendance record"""
    try:
        # Check if record exists
        check = db.fetch_one("SELECT attendance_id FROM attendance WHERE attendance_id = %s", (attendance_id,))
        if not check:
            return jsonify({'success': False, 'error': 'Attendance record not found'}), 404
        
        data = request.json if request.is_json else request.form
        status = data.get('status')
        remarks = data.get('remarks', '')
        
        if not status or status not in ['present', 'absent', 'late']:
            return jsonify({'success': False, 'error': 'Invalid status'}), 400
        
        update_query = """
            UPDATE attendance
            SET status = %s, remarks = %s
            WHERE attendance_id = %s
        """
        
        db.execute(update_query, (status, remarks, attendance_id))
        
        return jsonify({
            'success': True,
            'message': 'Attendance record updated successfully!'
        }), 200
    
    except Exception as e:
        return jsonify({'success': False, 'error': f'Error updating attendance: {str(e)}'}), 500

# ============================================================================
# DELETE ATTENDANCE RECORD
# ============================================================================
@attendance_bp.route('/<int:attendance_id>/delete', methods=['POST'])
@require_login
@require_role('admin')
def delete_attendance(attendance_id):
    """Delete attendance record"""
    try:
        check = db.fetch_one("SELECT attendance_id FROM attendance WHERE attendance_id = %s", (attendance_id,))
        if not check:
            return jsonify({'success': False, 'error': 'Attendance record not found'}), 404
        
        db.execute("DELETE FROM attendance WHERE attendance_id = %s", (attendance_id,))
        
        return jsonify({
            'success': True,
            'message': 'Attendance record deleted successfully!'
        }), 200
    
    except Exception as e:
        return jsonify({'success': False, 'error': f'Error deleting attendance: {str(e)}'}), 500
