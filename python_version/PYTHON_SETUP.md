# Python Flask Implementation - Setup & Run Guide

## Phase 2.0: Project Structure & Initial Setup ✅

### 📁 Python Project Structure

```
python_version/
├── app/
│   ├── __init__.py                 # Flask app factory
│   ├── database.py                 # Database connection module
│   ├── forms.py                    # Form validation (WTForms)
│   ├── utils.py                    # Utility functions (password hashing)
│   ├── routes/
│   │   ├── __init__.py
│   │   ├── auth.py                 # Login/Logout routes ✅ READY
│   │   ├── dashboard.py            # Dashboard routes ✅ READY
│   │   ├── students.py             # Student CRUD routes (placeholder)
│   │   └── attendance.py           # Attendance routes (placeholder)
│   ├── templates/
│   │   ├── login.html              # Login page ✅
│   │   ├── dashboard.html          # Dashboard page ✅
│   │   └── error.html              # Error page ✅
│   └── static/
│       ├── css/
│       └── js/
├── config.py                       # Configuration settings
├── run.py                          # Flask server entry point
├── init_db.py                      # Database initialization script
└── requirements.txt                # Python dependencies
```

---

## 🚀 Step-by-Step Setup Guide

### **Step 1: Prerequisites**

Ensure you have:
- Python 3.8+ installed (check: `python --version`)
- PostgreSQL running with database schema loaded
- VS Code with Python extension

### **Step 2: Create Virtual Environment**

Open PowerShell in the `python_version` folder and run:

```powershell
# Create virtual environment
python -m venv venv

# Activate virtual environment
.\venv\Scripts\Activate.ps1

# If you get execution policy error, run:
# Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

After activation, your prompt should show `(venv)` at the beginning.

### **Step 3: Install Dependencies**

```powershell
# Make sure virtual environment is activated
pip install --upgrade pip

# Install all dependencies from requirements.txt
pip install -r requirements.txt
```

Expected output should show all packages installing successfully:
- Flask 2.3.3
- psycopg2-binary (PostgreSQL driver)
- bcrypt (password hashing)
- WTForms (form validation)
- Flask-Session (session management)
- And others...

### **Step 4: Configure Database Connection**

The database connection reads from `config.py`. Default settings:
- Host: localhost
- Port: 5432
- Database: students_management_db
- User: postgres
- Password: (empty - set as needed)

If your PostgreSQL has a different password, either:

**Option A: Set environment variables** (temporary)
```powershell
$env:DB_PASSWORD = "your_postgres_password"
```

**Option B: Edit config.py** (not recommended for production)
Edit line in `config.py`:
```python
DB_PASSWORD = os.environ.get('DB_PASSWORD') or 'your_password_here'
```

### **Step 5: Initialize Database Passwords**

The PostgreSQL schema has placeholder password hashes. Initialize them with actual hashes:

```powershell
# Make sure venv is activated
python init_db.py
```

This script will:
- Connect to database
- Update all sample user passwords with bcrypt hashes
- Set password for all users as "password"

Expected output:
```
✅ Database connection successful
✓ admin123: password set
✓ teacher_john: password set
✓ teacher_sarah: password set
✓ student_alice: password set
...
✅ Database initialization complete!
```

### **Step 6: Start the Flask Server**

```powershell
# Make sure venv is activated
python run.py
```

Expected output:
```
🎓 Students' Management & Attendance System - Python Version
======================================================================
✅ Server starting...
📍 Access the application at: http://localhost:5000
🔐 Login page: http://localhost:5000/auth/login

⚠️  Press CTRL+C to stop the server

======================================================================

WARNING in app.run_simple
  * Running on http://127.0.0.1:5000
  * Debug mode: on
```

### **Step 7: Test the Application**

1. Open browser: http://localhost:5000/auth/login
2. Login with test credentials:
   - **Username:** admin123
   - **Password:** password

3. You should see the dashboard with statistics

---

## 🔑 Phase 2.0 Features Implemented

### ✅ **Login System** (Complete)
- Form validation (username, password required)
- Secure bcrypt password verification
- Session management
- Error handling & feedback

### ✅ **Dashboard** (Complete)
- Role-aware navigation
- System statistics (total students, users, attendance)
- Quick action buttons
- Welcome message with user info
- Responsive design with Bootstrap 5

### ✅ **Database Connection** (Complete)
- Reusable database connection class
- Context managers for safe connections
- Both single-row and multi-row queries
- Error handling & logging
- Support for dictionary cursors (easy access)

### ✅ **Project Structure** (Complete)
- Flask app factory pattern
- Blueprints for modular routes
- Configuration management
- Form validation with WTForms
- Utility functions for common tasks

---

## 📋 Next Phases (Coming)

**Phase 2.1:** Student Management (Add, Update, Delete, List, Search)  
**Phase 2.2:** Attendance Management (Mark, View)  
**Phase 2.3:** Reports & Analytics  

---

## ⚠️ Troubleshooting

### **Error: "No module named 'flask'"**
- Solution: Check virtual environment is activated (should show `(venv)` in prompt)
- Run: `pip install -r requirements.txt` again

### **Error: "connection refused" or "database not found"**
- Check PostgreSQL is running
- Verify database name: students_management_db
- Check credentials in config.py
- Ensure schema.sql was loaded successfully

### **Error: "password authentication failed"**
- PostgreSQL password is wrong
- Either set `$env:DB_PASSWORD` or update config.py
- Run `init_db.py` again after fixing password

### **Port 5000 already in use**
- Change port in `run.py` (last line):
  ```python
  app.run(host='127.0.0.1', port=5001, debug=True)
  ```

### **Session not persisting**
- Ensure `flask-session` is installed
- Check `FLASK_ENV` is set to 'development'
- Browser cookies must be enabled

---

## 🔧 File Explanations

### `config.py`
- Stores all configuration settings
- Separate configs for development, testing, production
- Loads from environment variables for sensitive data

### `app/__init__.py`
- Flask app factory function
- Registers blueprints
- Initializes extensions (session, database)
- Error handlers

### `app/database.py`
- PostgreSQL connection management
- Context managers for safe DB access
- Query methods (fetch_one, fetch_all, execute)
- Connection pooling-ready

### `app/forms.py`
- WTForms for data validation
- LoginForm, AddStudentForm, UpdateStudentForm
- Built-in validation (email, length, regex)
- Client-side validation in templates

### `app/routes/auth.py`
- Login route (GET & POST)
- Logout route
- Decorators for authentication checks
- Session management

### `app/routes/dashboard.py`
- Dashboard home page
- Statistics queries
- Role-aware display

### `app/utils.py`
- Password hashing (bcrypt)
- Password verification
- Reusable utility functions

---

## 📝 Important Notes

1. **Security:**
   - Passwords are hashed with bcrypt (10 rounds)
   - Sessions expire after 24 hours
   - CSRF protection ready (Flask-Session)
   - Secrets are loaded from environment variables

2. **Database:**
   - Each query uses fresh connection
   - Automatic transaction handling
   - Proper error handling & rollback
   - Connection closes after each operation

3. **Development vs Production:**
   - Debug mode enabled in development
   - Change to production config for deployment
   - Update SECRET_KEY in production
   - Set SESSION_COOKIE_SECURE=True for HTTPS

---

## 🎯 How to Extend

### Add a New Route
1. Create function in `app/routes/module.py`
2. Use `@module_bp.route()` decorator
3. Register blueprint in `app/__init__.py`
4. Create template in `app/templates/`

### Example:
```python
# In app/routes/example.py
@example_bp.route('/example', methods=['GET'])
@require_login
def example_route():
    return render_template('example.html')

# Register in app/__init__.py
app.register_blueprint(example_bp)
```

### Add a New Form
1. Define form class in `app/forms.py`
2. Inherit from WTForms.Form
3. Add validators
4. Use in route: `form = YourForm(request.form)`

---

## 📊 Current Status

✅ **Completed:**
- Project structure
- Database connection layer
- Login authentication system
- Dashboard with statistics
- Form validation framework
- HTML templates with Bootstrap 5

⏳ **Next:**
- Student CRUD operations
- Attendance marking
- Report generation
- Search & filter functionality

---

**Phase 2.0 Complete!** Ready to proceed to Phase 2.1 (Student Management)
