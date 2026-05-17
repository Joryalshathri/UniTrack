"""
UniTrack Students Management System - Modular Flask Application
Refactored with blueprints, decorators, and database wrapper class
"""
import os
from flask import Flask, render_template, session, redirect, url_for

# Import blueprints
from routes.auth import auth_bp
from routes.dashboard import dashboard_bp
from routes.students import students_bp
from routes.attendance import attendance_bp
from routes.reports import reports_bp

def create_app():
    """Application factory"""
    app = Flask(__name__, template_folder=os.path.join(os.path.dirname(__file__), 'templates'))
    app.secret_key = 'unitrack-secret-key-2026'
    
    # Register blueprints
    app.register_blueprint(auth_bp, url_prefix='/auth')
    app.register_blueprint(dashboard_bp)
    app.register_blueprint(students_bp)
    app.register_blueprint(attendance_bp)
    app.register_blueprint(reports_bp)
    
    # Home route redirect
    @app.route('/')
    def index():
        if 'user_id' in session:
            return redirect(url_for('dashboard.dashboard'))
        return redirect(url_for('auth.login'))
    
    # Error handlers
    @app.errorhandler(404)
    def not_found(error):
        return render_template('error.html', message='Page not found'), 404
    
    @app.errorhandler(500)
    def server_error(error):
        return render_template('error.html', message='Server error'), 500
    
    return app


if __name__ == '__main__':
    app = create_app()
    app.run(debug=True, host='localhost', port=5000)
