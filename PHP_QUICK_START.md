# 🚀 Quick Start Guide - PHP Version

## Running the PHP Version

### Prerequisites
- PHP 7.4+
- PostgreSQL 10+
- PostgreSQL PHP extension enabled (php-pgsql)
- Web server (Apache, Nginx, or PHP built-in server)

### Quick Setup (5 minutes)

#### 1. Verify PostgreSQL is running and database exists
```bash
# For Windows (PowerShell)
# The database should already be created from Phase 1
```

#### 2. Update configuration if needed
Edit `php_version/config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', 5432);
define('DB_NAME', 'student_management');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'postgres');  // Update if different
define('BASE_URL', 'http://localhost:8000/php_version');
```

#### 3. Run PHP built-in server (simplest for testing)
```bash
# Navigate to php_version directory
cd php_version

# Start server on port 8000
php -S localhost:8000
```

#### 4. Open in browser
```
http://localhost:8000/
```

#### 5. Login with test credentials
- **Username:** admin
- **Password:** admin123

---

## Directory Structure

```
php_version/
├── index.php                    ← Entry point (router)
├── config/                      
│   ├── config.php              ← Configuration settings
│   ├── Database.php            ← Database connection
│   └── Helpers.php             ← Helper functions
├── classes/
│   ├── User.php                ← Authentication
│   ├── Student.php             ← Student CRUD
│   └── Attendance.php          ← Attendance tracking
├── pages/                       ← All page templates
│   ├── login.php
│   ├── dashboard.php
│   ├── students_list.php
│   ├── mark_attendance.php
│   ├── attendance_report.php
│   ├── reports_dashboard.php
│   └── ... (10 pages total)
└── logs/                        ← Auto-created log files
```

---

## Workflow

### 1. Login
- Navigate to `http://localhost:8000/`
- Enter credentials (admin / admin123)
- Session valid for 1 hour

### 2. Dashboard
- View system statistics
- See quick action buttons
- Access all main features

### 3. Manage Students
- **View:** Click "Students" → Search/Browse
- **Add:** Click "Add Student" button → Fill form
- **Edit:** Click "Edit" on any student
- **Delete:** Click "Delete" on any student

### 4. Mark Attendance
- Click "Mark Attendance"
- Select status for each student (Present/Absent/Late)
- Add optional remarks
- Click "Save Attendance"

### 5. View Reports
- **Attendance Report:** Click "Attendance Report" for detailed records
- **Reports Dashboard:** Click "Reports" for analytics
- Filter by date range, status, student

---

## Troubleshooting

### Database Connection Error
**Error:** "Database Connection Failed"
- ✅ Ensure PostgreSQL is running
- ✅ Check credentials in `config/config.php`
- ✅ Verify database `student_management` exists
- ✅ Check if `pg_connect` is available (`php -m | grep pgsql`)

### Session Errors
**Error:** "User not logged in"
- ✅ Clear browser cookies
- ✅ Ensure cookies are enabled
- ✅ Check session timeout (1 hour default)

### Display Issues
**Error:** "Styles not loading correctly"
- ✅ Bootstrap CDN requires internet connection
- ✅ Check browser console for errors
- ✅ Try different browser

### File Upload/Log Errors
**Error:** "Cannot write to logs directory"
- ✅ Ensure `php_version/logs/` directory exists
- ✅ Check directory permissions (chmod 755)
- ✅ Run PHP with appropriate user permissions

---

## Features Available

### ✅ Student Management
- Create student accounts
- Search and filter students
- View detailed student profiles
- Edit student information
- Delete student records
- Pagination support (10 per page)

### ✅ Attendance Tracking
- Mark attendance for multiple students
- Bulk operations with 50+ students
- Add remarks to attendance records
- View attendance history
- Filter by date range and status

### ✅ Reports & Analytics
- Dashboard with key metrics
- Student attendance reports
- Class overview summaries
- Attendance percentage calculations
- Top performers list

### ✅ Security
- Bcrypt password hashing
- Session management (1 hr timeout)
- CSRF token protection
- Input sanitization
- SQL injection prevention

---

## Test Data

The database includes pre-loaded test data:
- **Users:** 8 users (admin, teachers, students)
- **Students:** 5 students
- **Attendance:** 19 sample records

### Test Accounts
| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | admin |
| teacher | teacher123 | teacher |
| student | student123 | student |

---

## Performance Tips

1. **Database Indexing** - Already optimized in schema
2. **Pagination** - Default 10 students per page
3. **Session Timeout** - Prevents memory leaks (1 hour)
4. **Query Optimization** - Parameterized queries prevent SQL injection
5. **Logging** - Daily log files for audit trail

---

## Comparing Python vs PHP Versions

| Feature | Flask (Python) | PHP |
|---------|---|---|
| Framework | Flask | Object-Oriented PHP |
| Database | psycopg2 | pg_connect |
| Sessions | Flask-Session | PHP Sessions |
| Templates | Jinja2 | PHP (embedded) |
| Form Handling | WTForms | Manual/HTML forms |
| Security | Built-in CSRF | Manual CSRF tokens |
| Performance | Slightly slower | Slightly faster |
| **Functionality** | **100% Identical** | **100% Identical** |

---

## Deployment Checklist

- [ ] PostgreSQL database created
- [ ] PHP 7.4+ installed
- [ ] config.php credentials updated
- [ ] logs/ directory writable
- [ ] BASE_URL configured for production
- [ ] Test login works
- [ ] Student management working
- [ ] Attendance marking functional
- [ ] Reports generating correctly
- [ ] Backups scheduled

---

**For more detailed documentation, see:**
- `PHP_VERSION_SETUP.md` - Complete setup guide
- `PHASE_3_COMPLETION.md` - Detailed completion report
- `PROJECT_STATUS.md` - Overall project status
