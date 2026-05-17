"""
Attendance management routes blueprint
"""
from flask import Blueprint, render_template, request, redirect, url_for, flash, session, jsonify
from decorators import require_login, require_role
from datetime import datetime
from database import Database

attendance_bp = Blueprint('attendance', __name__, url_prefix='/attendance')
db = Database.get_instance()


@attendance_bp.route('/mark', methods=['GET', 'POST'])
@require_login
@require_role('admin', 'teacher')
def mark_attendance():
    """Mark attendance for students"""
    try:
        if request.method == 'POST':
            # Extract JSON data
            data = request.json if request.is_json else request.form
            attendance_records = data.get('attendance', [])
            attendance_date = data.get('attendance_date')
            
            if not attendance_records:
                return jsonify({'success': False, 'error': 'No attendance records provided'}), 400
            
            if not attendance_date:
                return jsonify({'success': False, 'error': 'No attendance date provided'}), 400
            
            saved_count = 0
            errors = []
            valid_statuses = ['present', 'absent', 'late']
            
            for record in attendance_records:
                try:
                    student_id = record.get('student_id')
                    status = record.get('status')
                    remarks = record.get('remarks', '')
                    
                    # Validate student ID
                    if not student_id or not str(student_id).isdigit():
                        errors.append(f'Invalid student ID: {student_id}')
                        continue
                    
                    # Validate status
                    if status not in valid_statuses:
                        errors.append(f'Invalid status for student {student_id}: {status}')
                        continue
                    
                    # Check if student exists
                    student_check = db.fetch_one(
                        'SELECT student_id FROM students WHERE student_id = %s', 
                        [student_id]
                    )
                    if not student_check:
                        errors.append(f'Student {student_id} not found')
                        continue
                    
                    # Check if attendance already marked for this date
                    check_query = '''
                        SELECT attendance_id FROM attendance
                        WHERE student_id = %s AND DATE(attendance_date) = %s
                    '''
                    existing = db.fetch_one(check_query, [student_id, attendance_date])
                    
                    if existing:
                        # Update existing record
                        update_query = '''
                            UPDATE attendance
                            SET status = %s, remarks = %s, marked_by = %s
                            WHERE attendance_id = %s
                        '''
                        db.execute(update_query, [status, remarks, session['user_id'], existing['attendance_id']])
                    else:
                        # Insert new record
                        insert_query = '''
                            INSERT INTO attendance (student_id, attendance_date, status, marked_by, remarks)
                            VALUES (%s, %s, %s, %s, %s)
                        '''
                        db.execute(insert_query, [student_id, attendance_date, status, session['user_id'], remarks])
                    
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
        
        # GET request - display form
        # Get all active students
        query = '''
            SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number, u.email
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.is_active = true
            ORDER BY u.first_name, u.last_name
        '''
        students = db.fetch_all(query)
        
        # Check if attendance already marked today
        today = datetime.now().date()
        check_query = '''
            SELECT COUNT(*) as count FROM attendance
            WHERE DATE(attendance_date) = %s
        '''
        count = db.fetch_one(check_query, [today])
        marked_today = count['count'] > 0 if count else False
        
        return render_template('attendance/mark.html', students=students, 
                             today=datetime.now().strftime('%Y-%m-%d'),
                             marked_today=marked_today)
    except Exception as e:
        if request.method == 'POST':
            return jsonify({'success': False, 'error': f'Error: {str(e)}'}), 500
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard.dashboard'))


@attendance_bp.route('/view')
@require_login
def view_attendance():
    """View attendance records"""
    try:
        student_id = request.args.get('student_id', '')
        
        query = '''
            SELECT a.attendance_id, a.attendance_date, a.status, a.remarks, s.first_name, s.last_name 
            FROM attendance a 
            JOIN students s ON a.student_id = s.student_id
        '''
        params = []
        
        if student_id:
            query += ' WHERE a.student_id = %s'
            params.append(student_id)
        
        query += ' ORDER BY a.attendance_date DESC'
        
        records = db.fetch_all(query, params)
        
        # Get all students for filter dropdown
        query = 'SELECT * FROM students ORDER BY first_name'
        students = db.fetch_all(query)
        
        return render_template('attendance/view.html', records=records, students=students)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard.dashboard'))


@attendance_bp.route('/analytics')
@require_role('admin')
def analytics():
    """View attendance analytics (admin only)"""
    try:
        # Get attendance summary per student
        query = '''
            SELECT 
                s.student_id,
                s.enrollment_number,
                u.first_name,
                u.last_name,
                COUNT(*) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 2) as attendance_percentage
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN attendance a ON s.student_id = a.student_id
            GROUP BY s.student_id, s.enrollment_number, u.first_name, u.last_name
            ORDER BY attendance_percentage DESC NULLS LAST
        '''
        
        attendance_summary = db.fetch_all(query)
        
        # Get overall statistics
        query = '''
            SELECT 
                COUNT(DISTINCT s.student_id) as total_students,
                COUNT(a.attendance_id) as total_records,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as total_absent,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as total_late,
                ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(a.attendance_id), 0), 2) as overall_percentage
            FROM students s
            LEFT JOIN attendance a ON s.student_id = a.student_id
        '''
        overall_stats = db.fetch_one(query)
        
        return render_template('analytics.html', 
                             attendance_summary=attendance_summary,
                             overall_stats=overall_stats)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard.dashboard'))
