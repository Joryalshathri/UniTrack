"""
Database initialization script
Creates tables and inserts sample data with properly hashed passwords
Run this AFTER creating the database schema
"""
import sys
import os
sys.path.insert(0, os.path.dirname(__file__))

from app.database import db
from config import DevelopmentConfig
from app.utils import hash_password
from app import create_app

def initialize_database():
    """Initialize database with proper password hashes"""
    
    # Create Flask app to get config
    app = create_app(DevelopmentConfig)
    
    with app.app_context():
        try:
            print("\n" + "="*70)
            print("🔧 Initializing Database with Hashed Passwords")
            print("="*70)
            
            # Check if database exists
            success, message = db.test_connection()
            if not success:
                print(f"❌ {message}")
                return False
            
            print("✅ Database connection successful")
            
            # List of users to update with hashed passwords
            users = [
                ('admin', 'password'),
                ('teacher_ahmad', 'password'),
                ('student_ali', 'password'),
                ('student_fatima', 'password'),
                ('student_mohammad', 'password'),
                ('student_noor', 'password'),
                ('student_sara', 'password'),
            ]
            
            print("\n📝 Updating passwords...")
            for username, password in users:
                hashed = hash_password(password)
                query = "UPDATE users SET password_hash = %s WHERE username = %s"
                db.execute(query, (hashed, username))
                print(f"  ✓ {username}: password set")
            
            print("\n✅ Database initialization complete!")
            print("\n📋 Sample Login Credentials:")
            print("   ├─ Admin:    admin / password")
            print("   ├─ Teacher:  teacher_ahmad / password")
            print("   └─ Student:  student_ali / password")
            print("\n" + "="*70)
            
            return True
            
        except Exception as e:
            print(f"❌ Error: {str(e)}")
            import traceback
            traceback.print_exc()
            return False

if __name__ == '__main__':
    initialize_database()
