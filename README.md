# Students' Management & Attendance System
## CS 516 Advanced Programming Language - Term Project

### Project Overview
A **server-client application** implementing a Students' Management & Attendance System in both **Python (Flask)** and **PHP (Core PHP)**, using a shared **PostgreSQL database**.

---

## 📁 Project Structure

```
Students_Management_System/
├── database/
│   └── schema.sql                 # PostgreSQL schema (COMPLETED ✅)
├── python_version/
│   └── [to be created]
├── php_version/
│   └── [to be created]
├── DATABASE_SETUP.md              # Database setup guide
└── README.md                       # This file
```

---

## 🎯 Project Status

### ✅ Completed
1. **Database Schema** - PostgreSQL schema with:
   - Users table (admin, teacher, student)
   - Students table (linked to users)
   - Attendance table (with status tracking)
   - Validation constraints & foreign keys
   - Helper views for reporting
   - Sample seed data

### 📋 To Do
2. **Python Version** (step-by-step)
   - [ ] Project structure & setup
   - [ ] Database connection
   - [ ] Login system
   - [ ] Add/Update/Delete student
   - [ ] List/Search students
   - [ ] Mark attendance
   - [ ] View attendance

3. **PHP Version** (step-by-step)
   - [ ] Project structure & setup
   - [ ] Database connection
   - [ ] Login system
   - [ ] Add/Update/Delete student
   - [ ] List/Search students
   - [ ] Mark attendance
   - [ ] View attendance

4. **Documentation & Presentation**
   - [ ] Comparison report
   - [ ] Presentation points
   - [ ] Screenshots
   - [ ] How to run guide

---

## 🔧 Technology Stack

| Component | Technology |
|-----------|------------|
| Database | PostgreSQL (shared) |
| Python App | Flask + psycopg2 |
| PHP App | Core PHP + mysqli/PDO |
| IDE | VS Code |
| OS | Windows |

---

## 📊 System Requirements

### Database Tables

**users** - Authentication & User Management
- user_id, username, email, password_hash, role, first_name, last_name, is_active

**students** - Student Information
- student_id, user_id, enrollment_number, date_of_birth, phone_number, address, city, state, postal_code

**attendance** - Attendance Tracking
- attendance_id, student_id, attendance_date, status (present/absent/late), marked_by, remarks

### Supported Roles
- **Admin**: Manage all users, students, system
- **Teacher**: Mark attendance, view student info
- **Student**: View own profile, view own attendance

---

## 🚀 Getting Started

### Step 1: Set Up Database
1. Follow [DATABASE_SETUP.md](DATABASE_SETUP.md)
2. Verify all tables created in PostgreSQL
3. Confirm sample data loaded

### Step 2: Python Implementation
Coming next - step by step guide

### Step 3: PHP Implementation  
Coming after Python is complete

---

## 📝 Features & Requirements

### ✅ User Authentication
- Login for admin, teacher, student
- Role-based access control
- Session management

### ✅ Student Management
- Add student (admin/teacher)
- Update student info (admin/student own profile)
- Delete student (admin only)
- View students (all roles, filtered by permissions)
- Search students

### ✅ Attendance Tracking
- Mark attendance (teacher only)
- View attendance (teacher, student own records)
- Status: Present, Absent, Late
- Historical records

### ✅ Reports
- Attendance summary with percentage
- Student list with filters
- Attendance records by student

---

## 💾 Database Connection Details

```
Host: localhost
Port: 5432
Database: students_management_db
User: postgres
Password: [your PostgreSQL password]
```

---

## 📚 Key Design Decisions

### 1. Shared Database
Both Python and PHP implementations use the same PostgreSQL schema to ensure consistency and demonstrate language-independent data layer.

### 2. Role-Based Access
Three distinct roles with appropriate permissions:
- Admin: Full system access
- Teacher: Student management and attendance
- Student: View own data

### 3. Data Validation
- Input validation in application layer
- Database constraints for data integrity
- Email format validation
- Date validations

### 4. One User Per Student
Each student has one corresponding user account for authentication, maintaining referential integrity.

### 5. Attendance Uniqueness
Only one attendance record per student per date prevents duplicate entries.

---

## 📖 How to Use This Repository

1. **Start with DATABASE_SETUP.md** for PostgreSQL setup
2. **Follow the folder structure** for organized code
3. **Each implementation section has its own README** with detailed instructions
4. **Use sample data** for testing

---

## ⚡ Quick Commands

### PostgreSQL
```bash
# Connect to database
psql -U postgres -d students_management_db

# Load schema
psql -U postgres -f database/schema.sql

# List tables
\dt

# Exit
\q
```

---

## 📞 Support & Troubleshooting

See DATABASE_SETUP.md for common issues and solutions.

---

## 📅 Project Timeline

- **Phase 1**: Database Schema ✅
- **Phase 2**: Python Implementation (Flask)
- **Phase 3**: PHP Implementation
- **Phase 4**: Testing & Comparison
- **Phase 5**: Documentation & Presentation

---

## 👥 Team Considerations

This project structure allows easy sharing with team members:
- Each folder is independent
- Database setup is shared
- Documentation is comprehensive
- Code follows best practices

---

## 📝 Final Submission Checklist

- [ ] Database schema with seed data
- [ ] Python implementation (all features)
- [ ] PHP implementation (all features)
- [ ] Test cases for both
- [ ] Database backup
- [ ] Setup instructions
- [ ] Project report
- [ ] Presentation slides
- [ ] Screenshots of all features
- [ ] Source code organized and commented

---

**Status**: Phase 1 (Database) ✅ Complete
**Next**: Phase 2 (Python Implementation) - Ready to start

---

*Project created for CS 516 Advanced Programming Language - Term Project*
