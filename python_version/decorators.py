"""
Authentication decorators for route protection
"""
from functools import wraps
from flask import session, flash, redirect, url_for


def require_login(f):
    """Decorator to require login for route"""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            flash('Please login first', 'error')
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated_function


def require_role(role):
    """Decorator to require specific role"""
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if 'user_id' not in session:
                flash('Please login first', 'error')
                return redirect(url_for('auth.login'))
            
            if session.get('role') != role:
                flash(f'Only {role}s can access this', 'error')
                return redirect(url_for('dashboard.dashboard'))
            
            return f(*args, **kwargs)
        return decorated_function
    return decorator


def get_current_user():
    """Get current logged in user info"""
    if 'user_id' in session:
        return {
            'user_id': session.get('user_id'),
            'username': session.get('username'),
            'role': session.get('role'),
            'first_name': session.get('first_name')
        }
    return None
