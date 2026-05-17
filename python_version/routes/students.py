"""
Student management routes blueprint
"""
from flask import Blueprint, render_template, request, redirect, url_for, flash
from decorators import require_login, require_role
from database import Database
from models import Student

students_bp = Blueprint('students', __name__, url_prefix='/students')
db = Database.get_instance()
student_model = Student()


@students_bp.route('/')
@require_login
def list_students():
    """Display all students using direct SQL"""
    try:
        query = """
            SELECT s.student_id, s.enrollment_number, s.phone_number,
                   u.first_name, u.last_name, u.email
            FROM students s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.is_active = true
            ORDER BY u.first_name, u.last_name
        """
        students = db.fetch_all(query)
        return render_template('students/list.html', students=students)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('dashboard.dashboard'))


@students_bp.route('/add', methods=['GET', 'POST'])
@require_role('admin')
def add_student():
    """Add new student"""
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
            return redirect(url_for('students.add_student'))
        
        if '@' not in email or '.' not in email:
            flash('Invalid email', 'error')
            return redirect(url_for('students.add_student'))
        
        try:
            student_model.add_student(enrollment, first_name, last_name, email, 
                                     phone if phone else None, dob if dob else None)
            flash('Student added successfully', 'success')
            return redirect(url_for('students.list_students'))
        except Exception as e:
            flash(f'Error: {str(e)}', 'error')
            return redirect(url_for('students.add_student'))
    
    return render_template('students/form.html', student=None)


@students_bp.route('/edit/<int:student_id>', methods=['GET', 'POST'])
@require_login
def edit_student(student_id):
    """Edit student information"""
    try:
        if request.method == 'POST':
            first_name = request.form.get('first_name', '').strip()
            last_name = request.form.get('last_name', '').strip()
            email = request.form.get('email', '').strip()
            phone = request.form.get('phone', '').strip()
            dob = request.form.get('date_of_birth', '').strip()
            
            if not first_name or not last_name or not email:
                flash('All fields required', 'error')
                return redirect(url_for('students.edit_student', student_id=student_id))
            
            if '@' not in email or '.' not in email:
                flash('Invalid email', 'error')
                return redirect(url_for('students.edit_student', student_id=student_id))
            
            student_model.update_student(student_id, first_name, last_name, email,
                                        phone if phone else None, dob if dob else None)
            flash('Student updated successfully', 'success')
            return redirect(url_for('students.list_students'))
        
        student = student_model.get_student_by_id(student_id)
        return render_template('students/form.html', student=student)
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('students.list_students'))


@students_bp.route('/delete/<int:student_id>', methods=['POST'])
@require_login
def delete_student(student_id):
    """Delete student"""
    try:
        student_model.delete_student(student_id)
        flash('Student deleted successfully', 'success')
    except Exception as e:
        flash(f'Error: {str(e)}', 'error')
    
    return redirect(url_for('students.list_students'))
