"""
Form validation using WTForms
"""
from wtforms import StringField, PasswordField, SelectField, DateField, IntegerField
from wtforms.validators import DataRequired, Email, Length, Optional, Regexp
from wtforms.form import Form

class LoginForm(Form):
    """User login form"""
    username = StringField('Username', [
        DataRequired(message='Username is required'),
        Length(min=3, message='Username must be at least 3 characters')
    ])
    password = PasswordField('Password', [
        DataRequired(message='Password is required'),
        Length(min=4, message='Password must be at least 4 characters')
    ])

class AddStudentForm(Form):
    """Form for adding a new student"""
    username = StringField('Username', [
        DataRequired(message='Username is required'),
        Length(min=3, max=50, message='Username must be 3-50 characters')
    ])
    email = StringField('Email', [
        DataRequired(message='Email is required'),
        Email(message='Invalid email address')
    ])
    first_name = StringField('First Name', [
        DataRequired(message='First name is required'),
        Length(min=2, message='First name must be at least 2 characters')
    ])
    last_name = StringField('Last Name', [
        DataRequired(message='Last name is required'),
        Length(min=2, message='Last name must be at least 2 characters')
    ])
    enrollment_number = StringField('Enrollment Number', [
        DataRequired(message='Enrollment number is required'),
        Length(min=4, max=20, message='Enrollment number must be 4-20 characters')
    ])
    date_of_birth = DateField('Date of Birth', [Optional()])
    phone_number = StringField('Phone Number', [
        Optional(),
        Regexp(r'^\d{10,}$', message='Phone number must be at least 10 digits')
    ])
    address = StringField('Address', [Optional()])
    city = StringField('City', [Optional()])
    state = StringField('State', [Optional()])
    postal_code = StringField('Postal Code', [Optional()])

class UpdateStudentForm(Form):
    """Form for updating student information"""
    first_name = StringField('First Name', [
        DataRequired(message='First name is required'),
        Length(min=2, message='First name must be at least 2 characters')
    ])
    last_name = StringField('Last Name', [
        DataRequired(message='Last name is required'),
        Length(min=2, message='Last name must be at least 2 characters')
    ])
    date_of_birth = DateField('Date of Birth', [Optional()])
    phone_number = StringField('Phone Number', [
        Optional(),
        Regexp(r'^\d{10,}$', message='Phone number must be at least 10 digits')
    ])
    address = StringField('Address', [Optional()])
    city = StringField('City', [Optional()])
    state = StringField('State', [Optional()])
    postal_code = StringField('Postal Code', [Optional()])

class MarkAttendanceForm(Form):
    """Form for marking attendance"""
    student_id = IntegerField('Student ID', [DataRequired()])
    status = SelectField('Attendance Status', [
        DataRequired(message='Please select attendance status')
    ], choices=[
        ('', 'Select status...'),
        ('present', 'Present'),
        ('absent', 'Absent'),
        ('late', 'Late')
    ])
    remarks = StringField('Remarks', [Optional()])
