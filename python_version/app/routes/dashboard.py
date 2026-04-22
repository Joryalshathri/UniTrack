"""
Dashboard routes
"""
from flask import Blueprint, render_template, redirect, url_for
from app.routes.auth import require_login, get_current_user
from app.database import db

dashboard_bp = Blueprint('dashboard', __name__)

@dashboard_bp.route('/')
def index():
    """Home/Index page"""
    user = get_current_user()
    if user:
        return redirect(url_for('dashboard.dashboard'))
    return redirect(url_for('auth.login'))

@dashboard_bp.route('/dashboard')
@require_login
def dashboard():
    """Dashboard page with stats"""
    try:
        user = get_current_user()
        
        # Get statistics
        stats = {
            'total_students': 0,
            'total_users': 0,
            'total_attendance': 0,
            'present_today': 0
        }
        
        # Get student count
        result = db.fetch_one("SELECT COUNT(*) as count FROM students")
        stats['total_students'] = result['count'] if result else 0
        
        # Get user count
        result = db.fetch_one("SELECT COUNT(*) as count FROM users")
        stats['total_users'] = result['count'] if result else 0
        
        # Get attendance count
        result = db.fetch_one("SELECT COUNT(*) as count FROM attendance")
        stats['total_attendance'] = result['count'] if result else 0
        
        return render_template('dashboard.html', user=user, stats=stats)
    
    except Exception as e:
        return render_template('error.html', error=str(e)), 500
