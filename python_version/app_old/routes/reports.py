"""
Reports generation and analytics routes
"""
from flask import Blueprint, render_template, request, jsonify, send_file
from app.database import db
from app.routes.auth import require_login, require_role, get_current_user
from datetime import datetime, timedelta
from io import BytesIO
import csv

reports_bp = Blueprint('reports', __name__, url_prefix='/reports')

# ============================================================================
# MAIN REPORTS DASHBOARD
# ============================================================================
@reports_bp.route('/', methods=['GET'])
@require_login
def reports_dashboard():
    """Main reports dashboard"""
    try:
        current_user = get_current_user()
        
        # Get overall statistics
        total_students_query = "SELECT COUNT(*) as count FROM students WHERE (SELECT is_active FROM users WHERE users.user_id = students.user_id)"
        total_students = db.fetch_one(total_students_query)['count']
        
        total_users_query = "SELECT COUNT(*) as count FROM users"
        total_users = db.fetch_one(total_users_query)['count']
        
        total_attendance_query = "SELECT COUNT(*) as count FROM attendance"
        total_attendance = db.fetch_one(total_attendance_query)['count']
        
        # Get today's attendance summary
        today = datetime.now().date()
        today_attendance_query = """
            SELECT 
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
            FROM attendance
            WHERE DATE(attendance_date) = %s
        """
        today_summary = db.fetch_one(today_attendance_query, (today,))
        
        # Get average attendance percentage (last 30 days)
        thirty_days_ago = today - timedelta(days=30)
        avg_attendance_query = """
            SELECT 
                AVG(CAST(present_count AS FLOAT) / total_count * 100) as avg_percentage
            FROM (
                SELECT 
                    s.student_id,
                    COUNT(*) as total_count,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
                FROM students s
                LEFT JOIN attendance a ON s.student_id = a.student_id AND DATE(a.attendance_date) >= %s
                GROUP BY s.student_id
            ) t
        """
        avg_attendance = db.fetch_one(avg_attendance_query, (thirty_days_ago,))
        avg_percentage = round(avg_attendance['avg_percentage'], 2) if avg_attendance['avg_percentage'] else 0
        
        return render_template('reports/dashboard.html', 
                             total_students=total_students,
                             total_users=total_users,
                             total_attendance=total_attendance,
                             today_summary=today_summary,
                             avg_percentage=avg_percentage,
                             current_user=current_user)
    
    except Exception as e:
        return render_template('error.html', error=f'Error loading reports: {str(e)}'), 500

# ============================================================================
# STUDENT PERFORMANCE REPORT
# ============================================================================
@reports_bp.route('/student-performance', methods=['GET'])
@require_login
def student_performance_report():
    """Generate student performance report"""
    try:
        days = request.args.get('days', 30, type=int)
        current_user = get_current_user()
        
        start_date = datetime.now().date() - timedelta(days=days)
        
        query = """
            SELECT 
                s.student_id,
                s.enrollment_number,
                u.first_name,
                u.last_name,
                u.email,
                COUNT(*) as total_days,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_percentage
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN attendance a ON s.student_id = a.student_id AND DATE(a.attendance_date) >= %s
            WHERE u.is_active = true
            GROUP BY s.student_id, s.enrollment_number, u.first_name, u.last_name, u.email
            ORDER BY attendance_percentage DESC
        """
        
        students = db.fetch_all(query, (start_date,))
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({
                'success': True,
                'data': students,
                'period_days': days
            })
        
        return render_template('reports/student_performance.html',
                             students=students,
                             days=days,
                             current_user=current_user)
    
    except Exception as e:
        return render_template('error.html', error=f'Error generating report: {str(e)}'), 500

# ============================================================================
# CLASS ATTENDANCE OVERVIEW
# ============================================================================
@reports_bp.route('/class-overview', methods=['GET'])
@require_login
def class_overview():
    """Class-wide attendance overview"""
    try:
        days = request.args.get('days', 7, type=int)
        current_user = get_current_user()
        
        start_date = datetime.now().date() - timedelta(days=days)
        
        query = """
            SELECT 
                DATE(attendance_date) as date,
                COUNT(*) as total_marked,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                ROUND(100.0 * SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_rate
            FROM attendance
            WHERE DATE(attendance_date) >= %s
            GROUP BY DATE(attendance_date)
            ORDER BY DATE(attendance_date) DESC
        """
        
        records = db.fetch_all(query, (start_date,))
        
        # Calculate overall statistics
        overall_query = """
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
            FROM attendance
            WHERE DATE(attendance_date) >= %s
        """
        
        overall = db.fetch_one(overall_query, (start_date,))
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({
                'success': True,
                'records': records,
                'overall': overall
            })
        
        return render_template('reports/class_overview.html',
                             records=records,
                             overall=overall,
                             days=days,
                             current_user=current_user)
    
    except Exception as e:
        return render_template('error.html', error=f'Error generating report: {str(e)}'), 500

# ============================================================================
# EXPORT ATTENDANCE DATA
# ============================================================================
@reports_bp.route('/export', methods=['GET'])
@require_login
@require_role('admin', 'teacher')
def export_data():
    """Export attendance data to CSV"""
    try:
        export_type = request.args.get('type', 'attendance', type=str)
        days = request.args.get('days', 30, type=int)
        student_id = request.args.get('student_id', None, type=int)
        
        start_date = datetime.now().date() - timedelta(days=days)
        
        if export_type == 'attendance':
            query = """
                SELECT 
                    a.attendance_id,
                    DATE(a.attendance_date) as date,
                    TIME(a.attendance_date) as time,
                    s.enrollment_number,
                    u.first_name,
                    u.last_name,
                    a.status,
                    a.remarks
                FROM attendance a
                JOIN students s ON a.student_id = s.student_id
                JOIN users u ON s.user_id = u.user_id
                WHERE DATE(a.attendance_date) >= %s
            """
            params = [start_date]
            
            if student_id:
                query += " AND a.student_id = %s"
                params.append(student_id)
            
            query += " ORDER BY a.attendance_date DESC"
            records = db.fetch_all(query, params)
            
            # Create CSV
            output = BytesIO()
            writer = csv.writer(output)
            writer.writerow(['Date', 'Time', 'Enrollment Number', 'First Name', 'Last Name', 'Status', 'Remarks'])
            
            for record in records:
                writer.writerow([
                    record['date'],
                    record['time'],
                    record['enrollment_number'],
                    record['first_name'],
                    record['last_name'],
                    record['status'].upper(),
                    record['remarks'] or ''
                ])
            
            output.seek(0)
            filename = f"attendance_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
        
        elif export_type == 'summary':
            query = """
                SELECT 
                    s.enrollment_number,
                    u.first_name,
                    u.last_name,
                    u.email,
                    COUNT(*) as total_days,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                    ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_percentage
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                LEFT JOIN attendance a ON s.student_id = a.student_id AND DATE(a.attendance_date) >= %s
                GROUP BY s.student_id, s.enrollment_number, u.first_name, u.last_name, u.email
                ORDER BY u.first_name, u.last_name
            """
            records = db.fetch_all(query, [start_date])
            
            # Create CSV
            output = BytesIO()
            writer = csv.writer(output)
            writer.writerow(['Enrollment Number', 'First Name', 'Last Name', 'Email', 'Total Days', 'Present', 'Absent', 'Late', 'Attendance %'])
            
            for record in records:
                writer.writerow([
                    record['enrollment_number'],
                    record['first_name'],
                    record['last_name'],
                    record['email'],
                    record['total_days'],
                    record['present'],
                    record['absent'],
                    record['late'],
                    f"{record['attendance_percentage']:.2f}%"
                ])
            
            output.seek(0)
            filename = f"attendance_summary_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
        
        else:
            return jsonify({'success': False, 'error': 'Invalid export type'}), 400
        
        return send_file(
            output,
            mimetype='text/csv',
            as_attachment=True,
            download_name=filename
        )
    
    except Exception as e:
        return jsonify({'success': False, 'error': f'Error exporting data: {str(e)}'}), 500

# ============================================================================
# ATTENDANCE TRENDS ANALYSIS
# ============================================================================
@reports_bp.route('/trends', methods=['GET'])
@require_login
def attendance_trends():
    """Analyze attendance trends"""
    try:
        days = request.args.get('days', 60, type=int)
        current_user = get_current_user()
        
        start_date = datetime.now().date() - timedelta(days=days)
        
        # Get daily trend
        trend_query = """
            SELECT 
                DATE(attendance_date) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                ROUND(100.0 * SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as percentage
            FROM attendance
            WHERE DATE(attendance_date) >= %s
            GROUP BY DATE(attendance_date)
            ORDER BY DATE(attendance_date)
        """
        
        trends = db.fetch_all(trend_query, (start_date,))
        
        # Convert dates to strings for JSON serialization
        for trend in trends:
            trend['date'] = str(trend['date'])
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({
                'success': True,
                'data': trends
            })
        
        return render_template('reports/trends.html',
                             trends=trends,
                             days=days,
                             current_user=current_user)
    
    except Exception as e:
        return render_template('error.html', error=f'Error generating report: {str(e)}'), 500
