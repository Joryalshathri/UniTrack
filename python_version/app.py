import os
from flask import Flask, render_template, request, redirect, url_for, session, flash
import psycopg2
from psycopg2.extras import RealDictCursor
import bcrypt
from datetime import datetime

app = Flask(__name__, template_folder=os.path.join(os.path.dirname(__file__), 'templates'))
app.secret_key = 'unitrack-secret-key-2026'

# Database connection
def get_db():
    conn = psycopg2.connect(
        host='localhost',
        database='students_management_db',
        user='postgres',
        password='8611622'
    )
    return conn

# ============= AUTHENTICATION =============

@app.route('/')
def index():
    if 'user_id' in session:
        return redirect(url_for('dashboard'))
    return redirect(url_for('login'))

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '').strip()
        
        if not username or not password:
            flash('Username and password required', 'error')
            return redirect(url_for('login'))
        
        try:
            conn = get_db()
            cursor = conn.cursor(cursor_factory=RealDictCursor)
            cursor.execute(
                'SELECT user_id, username, password_hash, role, first_name FROM users WHERE username = %s',
                (username,)
            )
            user = cursor.fetchone()
            cursor.close()
            conn.close()
            
            if user and bcrypt.checkpw(password.encode(), user['password_hash'].encode()):
                session['user_id'] = user['user_id']
                session['username'] = user['username']
                session['role'] = user['role']
                session['first_name'] = user['first_name']
                flash(f'Welcome {user["first_name"]}!', 'success')
                return redirect(url_for('dashboard'))
            else:
                flash('Invalid username or password', 'error')
        except Exception as e:
            app.logger.error(f'Login error: {str(e)}')
            flash('Database error', 'error')
        
        return redirect(url_for('login'))
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.clear()
    flash('Logged out successfully', 'success')
    return redirect(url_for('login'))

def login_required(f):
    def wrap(*args, **kwargs):
        if 'user_id' not in session:
            flash('Please login first', 'error')
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    wrap.__name__ = f.__name__
    return wrap

# ============= DASHBOARD =============

@app.route('/dashboard')
@login_required
def dashboard():
    try:
        conn = get_db()
        cursor = conn.cursor(cursor_factory=RealDictCursor)
        
        cursor.execute('SELECT COUNT(*) as total FROM students')
        student_count = cursor.fetchone()['total']
        
        cursor.execute('SELECT COUNT(*) as total FROM attendance')
        attendance_count = cursor.fetchone()['total']
        
        cursor.close()
        conn.close()
        
        return render_template('dashboard.html', 
                             student_count=student_count, 
                             attendance_count=attendance_count,
                             username=session.get('first_name'))
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('login'))

# ============= STUDENT CRUD =============

@app.route('/students')
@login_required
def list_students():
    try:
        conn = get_db()
        cursor = conn.cursor(cursor_factory=RealDictCursor)
        cursor.execute('SELECT * FROM students ORDER BY student_id DESC')
        students = cursor.fetchall()
        cursor.close()
        conn.close()
        return render_template('students/list.html', students=students)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard'))

@app.route('/students/add', methods=['GET', 'POST'])
@login_required
def add_student():
    if request.method == 'POST':
        enrollment = request.form.get('enrollment_number', '').strip()
        first_name = request.form.get('first_name', '').strip()
        last_name = request.form.get('last_name', '').strip()
        email = request.form.get('email', '').strip()
        phone = request.form.get('phone', '').strip()
        dob = request.form.get('date_of_birth', '').strip()
        
        # Validation
        if not enrollment or not first_name or not last_name or not email:
            flash('All fields required', 'error')
            return redirect(url_for('add_student'))
        
        if '@' not in email or '.' not in email:
            flash('Invalid email', 'error')
            return redirect(url_for('add_student'))
        
        try:
            conn = get_db()
            cursor = conn.cursor()
            cursor.execute(
                '''INSERT INTO students (enrollment_number, first_name, last_name, email, phone, date_of_birth) 
                   VALUES (%s, %s, %s, %s, %s, %s)''',
                (enrollment, first_name, last_name, email, phone if phone else None, dob if dob else None)
            )
            conn.commit()
            cursor.close()
            conn.close()
            flash('Student added successfully', 'success')
            return redirect(url_for('list_students'))
        except Exception as e:
            flash(f'Error: {str(e)}', 'error')
            return redirect(url_for('add_student'))
    
    return render_template('students/form.html', student=None)

@app.route('/students/edit/<int:student_id>', methods=['GET', 'POST'])
@login_required
def edit_student(student_id):
    try:
        conn = get_db()
        cursor = conn.cursor(cursor_factory=RealDictCursor)
        
        if request.method == 'POST':
            first_name = request.form.get('first_name', '').strip()
            last_name = request.form.get('last_name', '').strip()
            email = request.form.get('email', '').strip()
            phone = request.form.get('phone', '').strip()
            dob = request.form.get('date_of_birth', '').strip()
            
            if not first_name or not last_name or not email:
                flash('All fields required', 'error')
                return redirect(url_for('edit_student', student_id=student_id))
            
            if '@' not in email or '.' not in email:
                flash('Invalid email', 'error')
                return redirect(url_for('edit_student', student_id=student_id))
            
            cursor.execute(
                '''UPDATE students SET first_name = %s, last_name = %s, email = %s, phone = %s, date_of_birth = %s 
                   WHERE student_id = %s''',
                (first_name, last_name, email, phone if phone else None, dob if dob else None, student_id)
            )
            conn.commit()
            flash('Student updated successfully', 'success')
            cursor.close()
            conn.close()
            return redirect(url_for('list_students'))
        
        cursor.execute('SELECT * FROM students WHERE student_id = %s', (student_id,))
        student = cursor.fetchone()
        cursor.close()
        conn.close()
        
        return render_template('students/form.html', student=student)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('list_students'))

@app.route('/students/delete/<int:student_id>', methods=['POST'])
@login_required
def delete_student(student_id):
    try:
        conn = get_db()
        cursor = conn.cursor()
        cursor.execute('DELETE FROM students WHERE student_id = %s', (student_id,))
        conn.commit()
        cursor.close()
        conn.close()
        flash('Student deleted successfully', 'success')
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
    
    return redirect(url_for('list_students'))

# ============= ATTENDANCE =============

@app.route('/attendance/mark', methods=['GET', 'POST'])
@login_required
def mark_attendance():
    try:
        conn = get_db()
        cursor = conn.cursor(cursor_factory=RealDictCursor)
        
        if request.method == 'POST':
            attendance_date = request.form.get('attendance_date')
            for key, value in request.form.items():
                if key.startswith('status_'):
                    student_id = key.replace('status_', '')
                    cursor.execute(
                        '''INSERT INTO attendance (student_id, attendance_date, status) 
                           VALUES (%s, %s, %s)''',
                        (student_id, attendance_date, value)
                    )
            conn.commit()
            flash('Attendance marked successfully', 'success')
            cursor.close()
            conn.close()
            return redirect(url_for('mark_attendance'))
        
        cursor.execute('SELECT * FROM students ORDER BY student_id')
        students = cursor.fetchall()
        cursor.close()
        conn.close()
        
        return render_template('attendance/mark.html', students=students, today=datetime.now().strftime('%Y-%m-%d'))
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard'))

@app.route('/attendance/view')
@login_required
def view_attendance():
    try:
        conn = get_db()
        cursor = conn.cursor(cursor_factory=RealDictCursor)
        
        student_id = request.args.get('student_id', '')
        
        query = '''SELECT a.attendance_id, a.attendance_date, a.status, a.remarks, s.first_name, s.last_name 
                   FROM attendance a 
                   JOIN students s ON a.student_id = s.student_id'''
        params = []
        
        if student_id:
            query += ' WHERE a.student_id = %s'
            params.append(student_id)
        
        query += ' ORDER BY a.attendance_date DESC'
        
        cursor.execute(query, params)
        records = cursor.fetchall()
        
        cursor.execute('SELECT * FROM students ORDER BY first_name')
        students = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return render_template('attendance/view.html', records=records, students=students)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard'))

if __name__ == '__main__':
    app.run(debug=True, host='localhost', port=5000)
