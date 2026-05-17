"""
Authentication routes blueprint
"""
from flask import Blueprint, render_template, request, redirect, url_for, session, flash
import bcrypt
from database import Database

auth_bp = Blueprint('auth', __name__)
db = Database.get_instance()


@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    """User login"""
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '').strip()
        
        if not username or not password:
            flash('Username and password required', 'error')
            return redirect(url_for('auth.login'))
        
        try:
            query = 'SELECT user_id, username, password_hash, role, first_name FROM users WHERE username = %s'
            user = db.fetch_one(query, [username])
            
            if user and bcrypt.checkpw(password.encode(), user['password_hash'].encode()):
                session['user_id'] = user['user_id']
                session['username'] = user['username']
                session['role'] = user['role']
                session['first_name'] = user['first_name']
                flash(f'Welcome {user["first_name"]}!', 'success')
                return redirect(url_for('dashboard.dashboard'))
            else:
                flash('Invalid username or password', 'error')
        except Exception as e:
            flash('Database error', 'error')
        
        return redirect(url_for('auth.login'))
    
    return render_template('login.html')


@auth_bp.route('/logout')
def logout():
    """User logout"""
    session.clear()
    flash('Logged out successfully', 'success')
    return redirect(url_for('auth.login'))
