"""
Database connection and utility functions
"""
import psycopg2
from psycopg2.extras import RealDictCursor
from contextlib import contextmanager
from flask import current_app
import traceback

class DatabaseConnection:
    """Manages PostgreSQL database connections"""
    
    def __init__(self, app=None):
        self.app = app
        if app:
            self.init_app(app)
    
    def init_app(self, app):
        """Initialize database with Flask app"""
        self.app = app
    
    @contextmanager
    def get_connection(self):
        """
        Context manager for database connections
        Usage:
            with db.get_connection() as conn:
                cur = conn.cursor()
                cur.execute(query)
        """
        conn = None
        try:
            # Get connection parameters from config
            conn = psycopg2.connect(
                host=self.app.config['DB_HOST'],
                port=self.app.config['DB_PORT'],
                database=self.app.config['DB_NAME'],
                user=self.app.config['DB_USER'],
                password=self.app.config['DB_PASSWORD'],
                cursor_factory=RealDictCursor
            )
            yield conn
            conn.commit()
        except psycopg2.DatabaseError as e:
            if conn:
                conn.rollback()
            print(f"Database error: {e}")
            raise
        except Exception as e:
            if conn:
                conn.rollback()
            print(f"Unexpected error: {e}")
            traceback.print_exc()
            raise
        finally:
            if conn:
                conn.close()
    
    @contextmanager
    def get_dict_cursor(self):
        """Get a dictionary cursor (returns results as dictionaries)"""
        with self.get_connection() as conn:
            cursor = conn.cursor(cursor_factory=RealDictCursor)
            try:
                yield cursor
            finally:
                cursor.close()
    
    def fetch_one(self, query, params=None):
        """Fetch a single row as dictionary"""
        with self.get_dict_cursor() as cur:
            cur.execute(query, params or ())
            return cur.fetchone()
    
    def fetch_all(self, query, params=None):
        """Fetch all rows as list of dictionaries"""
        with self.get_dict_cursor() as cur:
            cur.execute(query, params or ())
            return cur.fetchall()
    
    def execute(self, query, params=None):
        """Execute a query (INSERT, UPDATE, DELETE)"""
        with self.get_connection() as conn:
            cur = conn.cursor()
            try:
                cur.execute(query, params or ())
                conn.commit()
                return cur.rowcount  # Return number of affected rows
            finally:
                cur.close()
    
    def execute_and_return_id(self, query, params=None):
        """Execute INSERT and return the inserted ID"""
        with self.get_connection() as conn:
            cur = conn.cursor()
            try:
                cur.execute(query + " RETURNING id, *", params or ())
                result = cur.fetchone()
                conn.commit()
                return result
            finally:
                cur.close()
    
    def test_connection(self):
        """Test database connection"""
        try:
            with self.get_connection() as conn:
                cur = conn.cursor()
                cur.execute("SELECT 1")
                return True, "Connection successful"
        except Exception as e:
            return False, f"Connection failed: {str(e)}"

# Create global database instance
db = DatabaseConnection()
