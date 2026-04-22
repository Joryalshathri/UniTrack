"""
Main entry point for the Flask application
Run this file to start the server
"""
import os
import sys
from app import create_app
from config import DevelopmentConfig

# Get the base directory
BASE_DIR = os.path.abspath(os.path.dirname(__file__))

if __name__ == '__main__':
    # Create Flask app
    app = create_app(DevelopmentConfig)
    
    print("\n" + "="*70)
    print("🎓 Students' Management & Attendance System - Python Version")
    print("="*70)
    print("\n✅ Server starting...")
    print("📍 Access the application at: http://localhost:5000")
    print("🔐 Login page: http://localhost:5000/auth/login")
    print("\n📝 Default credentials:")
    print("   Admin: admin123 / (check database for password)")
    print("   Teacher: teacher_john / (check database for password)")
    print("   Student: student_alice / (check database for password)")
    print("\n⚠️  Press CTRL+C to stop the server\n")
    print("="*70 + "\n")
    
    # Run the Flask development server
    app.run(
        host='127.0.0.1',
        port=5000,
        debug=True,
        use_reloader=True
    )
