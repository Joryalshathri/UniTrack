"""
Student model class for student operations
"""
from database import Database


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
        """Add new student"""
        query = """
            INSERT INTO students (enrollment_number, first_name, last_name, email, phone_number, date_of_birth)
            VALUES (%s, %s, %s, %s, %s, %s)
        """
        params = [enrollment_number, first_name, last_name, email, phone_number, date_of_birth]
        return self.db.execute(query, params)
    
    def update_student(self, student_id, first_name, last_name, email, phone_number=None, date_of_birth=None):
        """Update student information"""
        query = """
            UPDATE students 
            SET first_name = %s, last_name = %s, email = %s, phone_number = %s, date_of_birth = %s
            WHERE student_id = %s
        """
        params = [first_name, last_name, email, phone_number, date_of_birth, student_id]
        return self.db.execute(query, params)
    
    def delete_student(self, student_id):
        """Delete student"""
        query = "DELETE FROM students WHERE student_id = %s"
        return self.db.execute(query, [student_id])
    
    def get_student_count(self):
        """Get total student count"""
        query = "SELECT COUNT(*) as count FROM students"
        return self.db.fetch_count(query)
