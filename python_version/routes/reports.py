"""
Reports management routes blueprint
"""
from flask import Blueprint, render_template, request, redirect, url_for, flash
from decorators import require_login
from datetime import datetime, timedelta
from database import Database

reports_bp = Blueprint('reports', __name__, url_prefix='/reports')
db = Database.get_instance()


@reports_bp.route('/attendance')
@require_login
def attendance_report():
    """Attendance report with filtering"""
    try:
        days = request.args.get('days', 30, type=int)
        status = request.args.get('status', '')
        start_date = datetime.now().date() - timedelta(days=days)

        query = """
            SELECT a.attendance_date, a.status, a.remarks,
                   s.enrollment_number, u.first_name, u.last_name
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            JOIN users u ON s.user_id = u.user_id
            WHERE DATE(a.attendance_date) >= %s
        """
        params = [str(start_date)]

        if status:
            query += " AND a.status = %s"
            params.append(status)

        query += " ORDER BY a.attendance_date DESC"
        records = db.fetch_all(query, params)
        
        return render_template('reports/attendance.html', records=records, days=days, status=status)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard.dashboard'))
