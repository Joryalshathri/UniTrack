"""
Flask application factory
"""
from flask import Flask
from flask_session import Session
from config import config_object
from app.database import db

def create_app(config=None):
    """Create and configure Flask app"""
    
    app = Flask(__name__)
    
    # Load configuration
    if config is None:
        config = config_object
    app.config.from_object(config)
    
    # Initialize database
    db.init_app(app)
    
    # Initialize session
    Session(app)
    
    # Register blueprints (routes)
    from app.routes.auth import auth_bp
    from app.routes.dashboard import dashboard_bp
    from app.routes.students import students_bp
    from app.routes.attendance import attendance_bp
    
    app.register_blueprint(auth_bp)
    app.register_blueprint(dashboard_bp)
    app.register_blueprint(students_bp)
    app.register_blueprint(attendance_bp)
    
    # Error handlers
    @app.errorhandler(404)
    def not_found(error):
        return {'error': 'Page not found'}, 404
    
    @app.errorhandler(500)
    def server_error(error):
        return {'error': 'Server error'}, 500
    
    return app
