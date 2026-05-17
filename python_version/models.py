"""
Student model class for student operations
"""
from database import Database
import bcrypt


class Student:
    """Student management class"""
    
    def __init__(self):
        self.db = Database.get_instance()
    
    def get_all_students(self):
        """Get all active students"""
        query = """
            SELECT s.student_id, s.enrollment_number, s.phone_number,
                   u.first_name, u.last_name, u.email, u.is_active
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.is_active = true
            ORDER BY u.first_name, u.last_name
        """
        return self.db.fetch_all(query)
    
    def get_student_by_id(self, student_id):
        """Get student by ID"""
        query = """
            SELECT s.student_id, s.enrollment_number, s.phone_number,
                   u.first_name, u.last_name, u.email, u.is_active, s.date_of_birth
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE s.student_id = %s
        """
        return self.db.fetch_one(query, [student_id])
    
    def add_student(self, enrollment_number, first_name, last_name, email, phone_number=None, date_of_birth=None):
        """Add new student - creates both user and student records"""
        try:
            # First create user account
            password_hash = bcrypt.hashpw(enrollment_number.encode(), bcrypt.gensalt()).decode()
            user_query = """
                INSERT INTO users (username, first_name, last_name, email, role, password_hash)
                VALUES (%s, %s, %s, %s, %s, %s)
                RETURNING user_id
            """
            user = self.db.fetch_one(user_query, [enrollment_number, first_name, last_name, email, 'student', password_hash])
            
            if not user:
                raise Exception("Failed to create user account")
            
            user_id = user['user_id']
            
            # Then create student record linked to user
            student_query = """
                INSERT INTO students (enrollment_number, user_id, phone_number, date_of_birth)
                VALUES (%s, %s, %s, %s)
            """
            return self.db.execute(student_query, [enrollment_number, user_id, phone_number, date_of_birth])
        except Exception as e:
            raise Exception(f"Error adding student: {str(e)}")
    
    def update_student(self, student_id, first_name, last_name, email, phone_number=None, date_of_birth=None):
        """Update student information"""
        try:
            # Update user table
            user_update_query = """
                UPDATE users SET first_name = %s, last_name = %s, email = %s
                WHERE user_id = (SELECT user_id FROM students WHERE student_id = %s)
            """
            self.db.execute(user_update_query, [first_name, last_name, email, student_id])
            
            # Update student table
            student_update_query = """
                UPDATE students 
                SET phone_number = %s, date_of_birth = %s
                WHERE student_id = %s
            """
            return self.db.execute(student_update_query, [phone_number, date_of_birth, student_id])
        except Exception as e:
            raise Exception(f"Error updating student: {str(e)}")
    
    def delete_student(self, student_id):
        """Delete student"""
        query = "DELETE FROM students WHERE student_id = %s"
        return self.db.execute(query, [student_id])
    
    def get_student_count(self):
        """Get total student count"""
        query = "SELECT COUNT(*) as count FROM students"
        return self.db.fetch_count(query)
