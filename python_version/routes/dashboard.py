"""
Dashboard routes blueprint
"""
from flask import Blueprint, render_template, redirect, url_for, flash, session
from decorators import require_login
from database import Database

dashboard_bp = Blueprint('dashboard', __name__)
db = Database.get_instance()


@dashboard_bp.route('/')
def index():
    """Home page redirect"""
    if 'user_id' in session:
        return redirect(url_for('dashboard.dashboard'))
    return redirect(url_for('auth.login'))


@dashboard_bp.route('/dashboard')
@require_login
def dashboard():
    """Main dashboard"""
    try:
        query = 'SELECT COUNT(*) as total FROM students'
        student_count = db.fetch_one(query)['total']
        
        query = 'SELECT COUNT(*) as total FROM attendance'
        attendance_count = db.fetch_one(query)['total']
        
        return render_template('dashboard.html', 
                             student_count=student_count, 
                             attendance_count=attendance_count,
                             username=session.get('username'),
                             first_name=session.get('first_name'),
                             role=session.get('role'))
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('auth.login'))
