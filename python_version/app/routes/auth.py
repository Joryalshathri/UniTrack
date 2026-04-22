"""
Authentication routes (login, logout)
"""
from flask import Blueprint, render_template, request, jsonify, session, redirect, url_for
from app.database import db
from app.forms import LoginForm
import bcrypt

auth_bp = Blueprint('auth', __name__, url_prefix='/auth')

# ============================================================================
# LOGIN ROUTE
# ============================================================================
@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    """User login"""
    if request.method == 'GET':
        return render_template('login.html')
    
    # POST request - process login
    if request.method == 'POST':
        try:
            form = LoginForm(request.form)
            
            if not form.validate():
                errors = [f"{field}: {', '.join(msgs)}" for field, msgs in form.errors.items()]
                return jsonify({'success': False, 'errors': errors}), 400
            
            username = form.username.data
            password = form.password.data
            
            # Query database for user
            query = "SELECT user_id, username, email, role, first_name, last_name, password_hash, is_active FROM users WHERE username = %s"
            user = db.fetch_one(query, (username,))
            
            if not user:
                return jsonify({'success': False, 'error': 'Invalid username or password'}), 401
            
            if not user['is_active']:
                return jsonify({'success': False, 'error': 'User account is inactive'}), 403
            
            # Verify password
            if not bcrypt.checkpw(password.encode('utf-8'), user['password_hash'].encode('utf-8')):
                return jsonify({'success': False, 'error': 'Invalid username or password'}), 401
            
            # Create session
            session['user_id'] = user['user_id']
            session['username'] = user['username']
            session['role'] = user['role']
            session['first_name'] = user['first_name']
            session['last_name'] = user['last_name']
            session['email'] = user['email']
            session.permanent = True
            
            return jsonify({'success': True, 'message': 'Login successful', 'redirect': '/dashboard'}), 200
        
        except Exception as e:
            return jsonify({'success': False, 'error': f'Error during login: {str(e)}'}), 500

# ============================================================================
# LOGOUT ROUTE
# ============================================================================
@auth_bp.route('/logout')
def logout():
    """User logout"""
    session.clear()
    return redirect(url_for('auth.login'))

# ============================================================================
# HELPER FUNCTION: Check if user is logged in
# ============================================================================
def is_logged_in():
    """Check if user is logged in"""
    return 'user_id' in session

def get_current_user():
    """Get current logged-in user data"""
    if is_logged_in():
        return {
            'user_id': session.get('user_id'),
            'username': session.get('username'),
            'role': session.get('role'),
            'first_name': session.get('first_name'),
            'last_name': session.get('last_name'),
            'email': session.get('email')
        }
    return None

def require_login(f):
    """Decorator to require login"""
    from functools import wraps
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not is_logged_in():
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated_function

def require_role(*roles):
    """Decorator to require specific role"""
    from functools import wraps
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if not is_logged_in():
                return redirect(url_for('auth.login'))
            if session.get('role') not in roles:
                return jsonify({'error': 'Insufficient permissions'}), 403
            return f(*args, **kwargs)
        return decorated_function
    return decorator
