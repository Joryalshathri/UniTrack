"""
Database wrapper class for centralized database operations
"""
import psycopg2
from psycopg2.extras import RealDictCursor


class Database:
    """Singleton database connection wrapper"""
    _instance = None
    
    def __init__(self, host='localhost', database='students_management_db', 
                 user='postgres', password='8611622'):
        self.host = host
        self.database = database
        self.user = user
        self.password = password
    
    @staticmethod
    def get_instance():
        """Get singleton instance"""
        if Database._instance is None:
            Database._instance = Database()
        return Database._instance
    
    def get_connection(self):
        """Create and return database connection"""
        return psycopg2.connect(
            host=self.host,
            database=self.database,
            user=self.user,
            password=self.password
        )
    
    def fetch_one(self, query, params=None):
        """Fetch single record"""
        try:
            conn = self.get_connection()
            cursor = conn.cursor(cursor_factory=RealDictCursor)
            cursor.execute(query, params or [])
            result = cursor.fetchone()
            cursor.close()
            conn.close()
            return result
        except Exception as e:
            raise Exception(f"Database fetch_one error: {str(e)}")
    
    def fetch_all(self, query, params=None):
        """Fetch all records"""
        try:
            conn = self.get_connection()
            cursor = conn.cursor(cursor_factory=RealDictCursor)
            cursor.execute(query, params or [])
            results = cursor.fetchall()
            cursor.close()
            conn.close()
            return results
        except Exception as e:
            raise Exception(f"Database fetch_all error: {str(e)}")
    
    def execute(self, query, params=None):
        """Execute INSERT/UPDATE/DELETE query"""
        try:
            conn = self.get_connection()
            cursor = conn.cursor()
            cursor.execute(query, params or [])
            conn.commit()
            cursor.close()
            conn.close()
            return True
        except Exception as e:
            if 'conn' in locals():
                conn.rollback()
                conn.close()
            raise Exception(f"Database execute error: {str(e)}")
    
    def fetch_count(self, query, params=None):
        """Fetch count from query"""
        result = self.fetch_one(query, params)
        return result['count'] if result else 0
