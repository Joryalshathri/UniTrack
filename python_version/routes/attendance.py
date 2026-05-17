"""
Attendance management routes blueprint
"""
from flask import Blueprint, render_template, request, redirect, url_for, flash, session
from decorators import require_login, require_role
from datetime import datetime
from database import Database

attendance_bp = Blueprint('attendance', __name__, url_prefix='/attendance')
db = Database.get_instance()


@attendance_bp.route('/mark', methods=['GET', 'POST'])
@require_login
def mark_attendance():
    """Mark attendance for students"""
    try:
        if request.method == 'POST':
            attendance_date = request.form.get('attendance_date')
            
            for key, value in request.form.items():
                if key.startswith('status_'):
                    student_id = key.replace('status_', '')
                    query = '''
                        INSERT INTO attendance (student_id, attendance_date, status, marked_by) 
                        VALUES (%s, %s, %s, %s)
                    '''
                    db.execute(query, [student_id, attendance_date, value, session['user_id']])
            
            flash('Attendance marked successfully', 'success')
            return redirect(url_for('attendance.mark_attendance'))
        
        # Get all active students
        query = '''
            SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number, u.email
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.is_active = true
            ORDER BY u.first_name, u.last_name
        '''
        students = db.fetch_all(query)
        
        return render_template('attendance/mark.html', students=students, 
                             today=datetime.now().strftime('%Y-%m-%d'))
    except Exception as e:
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
