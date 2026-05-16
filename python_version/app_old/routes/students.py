"""
Student management routes (CRUD operations)
"""
from flask import Blueprint, render_template, request, jsonify, redirect, url_for, session
from app.database import db
from app.forms import AddStudentForm, UpdateStudentForm
from app.routes.auth import require_login, require_role, get_current_user
from datetime import datetime
import bcrypt

students_bp = Blueprint('students', __name__, url_prefix='/students')

# ============================================================================
# LIST STUDENTS
# ============================================================================
@students_bp.route('/', methods=['GET'])
@require_login
def list_students():
    """Get list of all students with pagination and search"""
    try:
        page = request.args.get('page', 1, type=int)
        search = request.args.get('search', '', type=str)
        per_page = 10
        offset = (page - 1) * per_page
        
        # Build query with optional search
        if search:
            search_query = f"%{search}%"
            count_query = """
                SELECT COUNT(*) as total FROM students s
                JOIN users u ON s.user_id = u.user_id
                WHERE u.first_name ILIKE %s OR u.last_name ILIKE %s 
                   OR s.enrollment_number ILIKE %s OR u.email ILIKE %s
            """
            count = db.fetch_one(count_query, (search_query, search_query, search_query, search_query))
            total = count['total']
            
            query = """
                SELECT s.student_id, s.enrollment_number, s.date_of_birth, s.phone_number,
                       s.address, s.city, s.state, s.postal_code,
                       u.user_id, u.first_name, u.last_name, u.email
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                WHERE u.first_name ILIKE %s OR u.last_name ILIKE %s 
                   OR s.enrollment_number ILIKE %s OR u.email ILIKE %s
                ORDER BY s.student_id DESC
                LIMIT %s OFFSET %s
            """
            students = db.fetch_all(query, (search_query, search_query, search_query, search_query, per_page, offset))
        else:
            count_query = "SELECT COUNT(*) as total FROM students"
            count = db.fetch_one(count_query)
            total = count['total']
            
            query = """
                SELECT s.student_id, s.enrollment_number, s.date_of_birth, s.phone_number,
                       s.address, s.city, s.state, s.postal_code,
                       u.user_id, u.first_name, u.last_name, u.email
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                ORDER BY s.student_id DESC
                LIMIT %s OFFSET %s
            """
            students = db.fetch_all(query, (per_page, offset))
        
        total_pages = (total + per_page - 1) // per_page
        current_user = get_current_user()
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({
                'success': True,
                'students': students,
                'total': total,
                'page': page,
                'total_pages': total_pages
            })
        
        return render_template('students/list.html', students=students, page=page, 
                             total_pages=total_pages, total=total, search=search, 
                             current_user=current_user)
    
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

# ============================================================================
# VIEW STUDENT DETAILS
# ============================================================================
@students_bp.route('/<int:student_id>', methods=['GET'])
@require_login
def view_student(student_id):
    """View student details and attendance"""
    try:
        query = """
            SELECT s.student_id, s.enrollment_number, s.date_of_birth, s.phone_number,
                   s.address, s.city, s.state, s.postal_code, s.enrollment_date,
                   u.user_id, u.first_name, u.last_name, u.email, u.username
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE s.student_id = %s
        """
        student = db.fetch_one(query, (student_id,))
        
        if not student:
            return render_template('error.html', error='Student not found'), 404
        
        # Get recent attendance
        attendance_query = """
            SELECT attendance_id, attendance_date, status, remarks
            FROM attendance
            WHERE student_id = %s
            ORDER BY attendance_date DESC
            LIMIT 30
        """
        attendance = db.fetch_all(attendance_query, (student_id,))
        
        current_user = get_current_user()
        
        return render_template('students/detail.html', student=student, attendance=attendance, 
                             current_user=current_user)
    
    except Exception as e:
        return render_template('error.html', error=str(e)), 500

# ============================================================================
# ADD NEW STUDENT
# ============================================================================
@students_bp.route('/add', methods=['GET', 'POST'])
@require_login
@require_role('admin', 'teacher')
def add_student():
    """Add a new student"""
    if request.method == 'GET':
        form = AddStudentForm()
        current_user = get_current_user()
        return render_template('students/form.html', form=form, action='add', current_user=current_user)
    
    # POST request
    try:
        form = AddStudentForm(request.form)
        
        if not form.validate():
            errors = [f"{field}: {', '.join(msgs)}" for field, msgs in form.errors.items()]
            if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
                return jsonify({'success': False, 'errors': errors}), 400
            current_user = get_current_user()
            return render_template('students/form.html', form=form, action='add', 
                                 errors=errors, current_user=current_user), 400
        
        # Check if username already exists
        user_check = db.fetch_one("SELECT user_id FROM users WHERE username = %s", (form.username.data,))
        if user_check:
            error = "Username already exists"
            if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
                return jsonify({'success': False, 'error': error}), 400
            current_user = get_current_user()
            return render_template('students/form.html', form=form, action='add', 
                                 error=error, current_user=current_user), 400
        
        # Hash password (default password is enrollment number)
        password = form.enrollment_number.data
        password_hash = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')
        
        # Create user account
        user_insert = """
            INSERT INTO users (username, email, first_name, last_name, password_hash, role, is_active)
            VALUES (%s, %s, %s, %s, %s, 'student', true)
            RETURNING user_id
        """
        user = db.fetch_one(user_insert, (
            form.username.data, form.email.data, form.first_name.data, 
            form.last_name.data, password_hash
        ))
        user_id = user['user_id']
        
        # Create student record
        student_insert = """
            INSERT INTO students (user_id, enrollment_number, date_of_birth, phone_number,
                                 address, city, state, postal_code, enrollment_date)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, CURRENT_DATE)
            RETURNING student_id
        """
        student = db.fetch_one(student_insert, (
            user_id, form.enrollment_number.data, form.date_of_birth.data,
            form.phone_number.data, form.address.data, form.city.data,
            form.state.data, form.postal_code.data
        ))
        student_id = student['student_id']
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({
                'success': True, 
                'message': f'Student added successfully! (ID: {student_id})',
                'redirect': f'/students/{student_id}'
            }), 201
        
        return redirect(url_for('students.view_student', student_id=student_id))
    
    except Exception as e:
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({'success': False, 'error': f'Error adding student: {str(e)}'}), 500
        return render_template('error.html', error=f'Error adding student: {str(e)}'), 500

# ============================================================================
# EDIT STUDENT
# ============================================================================
@students_bp.route('/<int:student_id>/edit', methods=['GET', 'POST'])
@require_login
@require_role('admin', 'teacher')
def edit_student(student_id):
    """Edit student information"""
    try:
        # Get student data
        query = """
            SELECT s.student_id, s.enrollment_number, s.date_of_birth, s.phone_number,
                   s.address, s.city, s.state, s.postal_code,
                   u.user_id, u.first_name, u.last_name
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE s.student_id = %s
        """
        student = db.fetch_one(query, (student_id,))
        
        if not student:
            return render_template('error.html', error='Student not found'), 404
        
        if request.method == 'GET':
            form = UpdateStudentForm()
            # Pre-fill form
            form.first_name.data = student['first_name']
            form.last_name.data = student['last_name']
            form.date_of_birth.data = student['date_of_birth']
            form.phone_number.data = student['phone_number']
            form.address.data = student['address']
            form.city.data = student['city']
            form.state.data = student['state']
            form.postal_code.data = student['postal_code']
            
            current_user = get_current_user()
            return render_template('students/form.html', form=form, action='edit', 
                                 student=student, current_user=current_user)
        
        # POST request - update student
        form = UpdateStudentForm(request.form)
        
        if not form.validate():
            errors = [f"{field}: {', '.join(msgs)}" for field, msgs in form.errors.items()]
            if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
                return jsonify({'success': False, 'errors': errors}), 400
            current_user = get_current_user()
            return render_template('students/form.html', form=form, action='edit', 
                                 student=student, errors=errors, current_user=current_user), 400
        
        # Update user first/last name
        user_update = """
            UPDATE users SET first_name = %s, last_name = %s
            WHERE user_id = %s
        """
        db.execute(user_update, (form.first_name.data, form.last_name.data, student['user_id']))
        
        # Update student record
        student_update = """
            UPDATE students SET date_of_birth = %s, phone_number = %s,
                               address = %s, city = %s, state = %s, postal_code = %s
            WHERE student_id = %s
        """
        db.execute(student_update, (
            form.date_of_birth.data, form.phone_number.data, form.address.data,
            form.city.data, form.state.data, form.postal_code.data, student_id
        ))
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({'success': True, 'message': 'Student updated successfully!'}), 200
        
        return redirect(url_for('students.view_student', student_id=student_id))
    
    except Exception as e:
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({'success': False, 'error': f'Error updating student: {str(e)}'}), 500
        return render_template('error.html', error=f'Error updating student: {str(e)}'), 500

# ============================================================================
# DELETE STUDENT
# ============================================================================
@students_bp.route('/<int:student_id>/delete', methods=['POST'])
@require_login
@require_role('admin')
def delete_student(student_id):
    """Delete a student"""
    try:
        # Get user_id associated with student
        query = "SELECT user_id FROM students WHERE student_id = %s"
        student = db.fetch_one(query, (student_id,))
        
        if not student:
            return jsonify({'success': False, 'error': 'Student not found'}), 404
        
        # Delete student (user will cascade delete due to FK constraint)
        db.execute("DELETE FROM students WHERE student_id = %s", (student_id,))
        db.execute("DELETE FROM users WHERE user_id = %s", (student['user_id'],))
        
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({'success': True, 'message': 'Student deleted successfully!'}), 200
        
        return redirect(url_for('students.list_students'))
    
    except Exception as e:
        if request.headers.get('X-Requested-With') == 'XMLHttpRequest':
            return jsonify({'success': False, 'error': f'Error deleting student: {str(e)}'}), 500
        return render_template('error.html', error=f'Error deleting student: {str(e)}'), 500
        db.execute("DELETE FROM users WHERE user_id = %s", (student['user_id'],))
        
        return jsonify({'success': True, 'message': 'Student deleted successfully!', 'redirect': '/students/'}), 200
    
    except Exception as e:
        return jsonify({'success': False, 'error': f'Error deleting student: {str(e)}'}), 500
