# 📚 UniTrack

## University Project: Comparing Modern vs Traditional Programming Languages

This project demonstrates how the **same system** can be implemented in two different programming languages:
- **Python (Flask)** - Modern, Framework-driven approach
- **PHP** - Traditional, Procedural web language

---

## 🎯 Project Overview

**UniTrack** is a comprehensive student management and attendance tracking system designed for educational institutions. It streamlines:
- 👥 **Student Information Management** (Create, Read, Update, Delete)
- ✅ **Attendance Marking** (Bulk operations & record management)
- 📊 **Attendance Tracking** (View records & statistics)
- 🔐 **Secure Authentication** (Password hashing with bcrypt)

---

## 🏫 Purpose

This is an **Advanced Programming Language** university project that showcases:

1. **Language Comparison**
   - How different languages solve the same problem
   - Syntax differences and similarities
   - Framework vs procedural approaches

2. **Database Design**
   - Relational database schema
   - Foreign key relationships
   - Data integrity constraints

3. **Web Development Patterns**
   - Authentication & authorization
   - CRUD operations
   - Database connectivity

---

## 📋 Features

### Core Functionality
✅ User Authentication (Admin, Teacher, Student roles)
✅ Student Management (Add, Edit, Delete, View)
✅ Attendance Marking (Single & Bulk operations)
✅ Attendance Records (View & Filter by student)
✅ Dashboard Statistics (Total students, attendance count)
✅ Secure Password Hashing (bcrypt)

### Database
✅ PostgreSQL 18
✅ 3 Tables: users, students, attendance
✅ Role-based access control
✅ Parameterized queries (SQL injection prevention)

---

## 🛠️ Technology Stack

### Python Version (Modern Approach)
- **Framework:** Flask
- **Database:** PostgreSQL with psycopg2
- **Security:** bcrypt password hashing
- **Frontend:** Jinja2 templates + Bootstrap 5

### PHP Version (Traditional Approach)
- **Language:** PHP 7.4+
- **Database:** PostgreSQL with direct connection
- **Security:** bcrypt password hashing
- **Frontend:** HTML/PHP + Bootstrap 5

### Both Versions Share
- PostgreSQL Database
- Bootstrap 5 UI Framework
- Same business logic
- Identical feature set

---

## 🚀 Quick Start

### Prerequisites
- Python 3.8+ (for Python version)
- PostgreSQL 18
- Git

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/Joryalshathri/Student-Mangment-System.git
cd Students_Management_System
```

**2. Set up database**
```bash
# Create and populate PostgreSQL database
cd database
python setup_db.py  # Automatically creates tables and sample data
```

**3. Run Python Version**
```bash
cd python_version
python app.py
# Access at http://localhost:5000
```

**4. Run PHP Version** (requires PHP installed)
```bash
cd php_version
php -S localhost:8000
# Access at http://localhost:8000
```

---

## 📊 Project Structure

```
Students_Management_System/
├── database/
│   ├── schema.sql                  # PostgreSQL database schema
│   └── (3 tables: users, students, attendance)
│
├── python_version/
│   ├── app.py                      # Flask application
│   ├── config.py                   # Database configuration
│   └── templates/
│       ├── base.html               # Base template
│       ├── login.html              # Login page
│       ├── dashboard.html          # Dashboard
│       └── students/               # Student management pages
│
├── php_version/
│   ├── index.php                   # Router
│   ├── config/
│   │   └── config.php              # Configuration
│   ├── classes/
│   │   ├── User.php
│   │   ├── Student.php
│   │   └── Attendance.php
│   └── pages/                      # Page handlers
│
└── README.md                        # This file
```

---

## 🔐 Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password |
| Teacher | teacher_john | password |

---

## 💻 Language Comparison

### Authentication (Login)

**Python (Modern):**
```python
@app.route('/login', methods=['POST'])
def login():
    username = request.form.get('username')
    password = request.form.get('password')
    
    if user and bcrypt.checkpw(password.encode(), user['password_hash'].encode()):
        session['user_id'] = user['user_id']
        return redirect(url_for('dashboard'))
```

**PHP (Traditional):**
```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    
    if (verifyPassword($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        header('Location: dashboard.php');
    }
}
```

### Key Differences
- **Python:** Framework handles routing, decorators for structure
- **PHP:** Manual routing, procedural control flow

---

## 📈 Database Schema

### Users Table
```
user_id (PK) | username | email | password_hash | role | first_name | last_name
```

### Students Table
```
student_id (PK) | user_id (FK) | enrollment_number | date_of_birth | phone_number
```

### Attendance Table
```
attendance_id (PK) | student_id (FK) | attendance_date | status | marked_by (FK) | remarks
```

---

## ✨ Key Features Demonstrated

### 1. **Data Validation**
- Input sanitization
- Email format validation
- Required field checking

### 2. **Security**
- Bcrypt password hashing
- Parameterized SQL queries (SQL injection prevention)
- Session management
- Role-based access control

### 3. **Database Operations**
- CRUD operations
- Joins across multiple tables
- Foreign key relationships
- Cascade deletes

### 4. **User Interface**
- Responsive Bootstrap 5 design
- Form handling
- Error messages & flash notifications
- Navigation between features

---

## 🎓 Learning Outcomes

By comparing these implementations, you'll learn:

✅ How frameworks abstract database operations (Flask ORM vs direct SQL)
✅ Template engines (Jinja2 vs PHP mixed markup)
✅ Different approaches to request handling
✅ Security best practices in both languages
✅ Database design principles
✅ MVC/MVT architecture patterns

---

## 🐛 Troubleshooting

### Python: ModuleNotFoundError
```bash
pip install -r requirements.txt
```

### PostgreSQL Connection Error
```bash
# Update password in python_version/app.py:
password='8611622'  # Your PostgreSQL password
```

### PHP: Command not found
```bash
# PHP not installed, download from https://www.php.net/
# Or use Python version instead
```

---

## 📝 License

This is a university project for educational purposes.

---

## 👨‍💻 Author

Created for Advanced Programming Languages Course

**Project Goal:** Demonstrate equivalent functionality across different programming paradigms

---

## 🔗 GitHub

Repository: [Joryalshathri/Student-Mangment-System](https://github.com/Joryalshathri/Student-Mangment-System)

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review code comments in both versions
3. Compare side-by-side implementations

---

**Version:** 1.0
**Last Updated:** May 2026
**Status:** ✅ Production Ready
