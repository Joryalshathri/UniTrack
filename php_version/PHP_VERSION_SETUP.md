# PHP Version Setup & Installation

## Project Structure

```
php_version/
├── index.php                 # Main router
├── config/
│   ├── config.php          # Configuration settings
│   ├── Database.php        # PostgreSQL database class
│   └── Helpers.php         # Helper functions
├── classes/
│   ├── User.php            # User authentication
│   ├── Student.php         # Student CRUD operations
│   └── Attendance.php      # Attendance tracking
├── pages/
│   ├── login.php           # Login page
│   ├── login_handler.php   # Login processing
│   ├── logout.php          # Logout handler
│   ├── dashboard.php       # Dashboard
│   ├── students_list.php   # Student listing
│   ├── student_detail.php  # Student details
│   ├── student_form.php    # Add/Edit student
│   ├── mark_attendance.php # Attendance marking
│   ├── attendance_report.php   # Attendance reports
│   └── reports_dashboard.php   # Reports dashboard
├── templates/
│   ├── header.php          # HTML header/navigation
│   ├── footer.php          # HTML footer
│   └── sidebar.php         # Sidebar navigation
├── assets/
│   ├── css/
│   │   └── style.css       # Custom styles
│   └── js/
│       └── script.js       # Custom JavaScript
└── logs/                   # Application logs
```

## Installation Steps

### 1. Prerequisites
- PHP 7.4 or higher
- PostgreSQL 10 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for URL rewriting)

### 2. Database Setup
```bash
# Create database and tables using the same schema.sql
psql -U postgres -d student_management -f ../database/schema.sql
```

### 3. Configuration
Edit `config/config.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', 5432);
define('DB_NAME', 'student_management');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'your_password');
define('BASE_URL', 'http://localhost/php_version');
```

### 4. Directory Permissions
```bash
chmod 755 php_version/
chmod 755 php_version/logs/
chmod 755 php_version/assets/
```

### 5. Start Application
Visit: `http://localhost/php_version/pages/login.php`

## Key Features Implemented

✅ User Authentication (Login/Logout)
✅ Student CRUD Operations
✅ Attendance Marking
✅ Attendance Reports
✅ Student Performance Analytics
✅ Daily Attendance Overview
✅ Data Export to CSV
✅ Session Management
✅ CSRF Token Protection
✅ Password Hashing (bcrypt)
✅ Role-based Access Control

## API Endpoints (JSON)

### Authentication
- POST `/pages/login_handler.php` - Login user
- GET `/pages/logout.php` - Logout user

### Students
- GET `index.php?action=students` - List students
- GET `index.php?action=student_detail&id=X` - View student
- POST `index.php?action=student_form` - Add/Edit student
- POST `/api/student_delete.php` - Delete student

### Attendance
- GET `index.php?action=mark_attendance` - Mark attendance page
- POST `/api/save_attendance.php` - Save attendance
- GET `index.php?action=attendance_report` - View reports
- GET `/api/export_attendance.php` - Export attendance CSV

### Reports
- GET `index.php?action=reports` - Reports dashboard
- GET `/api/student_performance.php` - Performance report
- GET `/api/class_overview.php` - Class overview

## Security Features

1. **Password Security**
   - Passwords hashed with bcrypt (cost factor: 10)
   - Password verification using PHP's password_verify()

2. **Session Management**
   - Configurable session timeout
   - Secure session cookies

3. **Input Validation**
   - Sanitize all user inputs
   - Email validation
   - SQL injection prevention using parameterized queries

4. **CSRF Protection**
   - Generate and verify CSRF tokens
   - Token validation on form submissions

5. **Access Control**
   - Role-based permissions (admin, teacher, student)
   - Session-based authentication
   - Automatic redirect to login for unauthorized access

## Usage Examples

### Login
```php
$user = new User();
$result = $user->login('username', 'password');
if ($result['success']) {
    // User logged in successfully
    redirect(BASE_URL . '/index.php?action=dashboard');
}
```

### Add Student
```php
$student = new Student();
$data = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'enrollment_number' => 'STU001',
    'phone_number' => '1234567890'
];
$result = $student->addStudent($data);
```

### Mark Attendance
```php
$attendance = new Attendance();
$records = [
    ['student_id' => 1, 'status' => 'present', 'remarks' => ''],
    ['student_id' => 2, 'status' => 'absent', 'remarks' => 'Sick']
];
$result = $attendance->markAttendance($records);
```

## Database Connection

The system uses PostgreSQL with connection pooling support. Database connection is handled through the singleton pattern:

```php
$db = Database::getInstance();
$users = $db->fetchAll("SELECT * FROM users WHERE role = $1", ['student']);
```

## Logging

All errors and important actions are logged to `/logs/YYYY-MM-DD.log`:

```php
logMessage('User login attempt', 'info');
logMessage('Database error occurred', 'error');
```

## Performance Optimization

1. Parameterized queries prevent SQL injection
2. Connection pooling reduces overhead
3. Pagination for large datasets
4. Index optimization on frequently queried columns
5. Query result caching where applicable

## Troubleshooting

### Connection Failed
- Verify PostgreSQL is running
- Check database credentials in config.php
- Ensure database exists and is accessible

### Session Issues
- Check PHP session.save_path is writable
- Verify session timeout settings
- Clear browser cookies if needed

### Permission Errors
- Ensure proper directory permissions
- Check file upload permissions
- Verify logs directory is writable

## Future Enhancements

- RESTful API implementation
- Mobile app integration
- Advanced analytics dashboard
- Bulk import/export functionality
- Email notifications
- SMS alerts
- Multi-language support

## License

This project is provided as-is for educational purposes.

## Support

For issues or questions, please refer to the documentation or contact the development team.
