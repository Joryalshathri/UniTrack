# PostgreSQL Database Setup Guide

## Aspect 1: PostgreSQL Schema for Students' Management & Attendance System

### Database Design Overview

This schema includes:
- **3 main tables**: `users`, `students`, `attendance`
- **2 custom types**: `user_role` (admin, teacher, student) and `attendance_status` (present, absent, late)
- **Proper relationships**: Foreign keys linking students to users, attendance to students
- **Validation constraints**: Email format, username length, phone validation
- **2 helper views**: For easier querying

---

## 📋 Schema Structure

### Table 1: `users` (Authentication & Role Management)
Stores all system users (admin, teacher, student)
- Primary Key: `user_id`
- Unique: `username`, `email`
- Roles: admin, teacher, student
- Fields: username, email, password_hash, role, name, status

### Table 2: `students` (Student Information)
Stores student-specific information, linked to users
- Primary Key: `student_id`
- Foreign Key: `user_id` (links to users table)
- Fields: enrollment_number, DOB, phone, address, city, state, postal code
- Each student has exactly one user account

### Table 3: `attendance` (Attendance Records)
Stores daily attendance with status (present, absent, late)
- Primary Key: `attendance_id`
- Foreign Keys: `student_id`, `marked_by` (teacher's user_id)
- Unique constraint: Only one record per student per day
- Status: present, absent, late

---

## 🚀 How to Set Up

### Prerequisites
- PostgreSQL installed (download from https://www.postgresql.org/download/)
- VS Code with PostgreSQL extension (optional but helpful)

### Step 1: Open PostgreSQL Command Line

**On Windows:**
1. Open Command Prompt or PowerShell
2. Navigate to PostgreSQL bin folder (typically):
   ```
   cd "C:\Program Files\PostgreSQL\15\bin"
   ```
3. Start psql (PostgreSQL command line):
   ```
   psql -U postgres
   ```
   (Default username is `postgres`, it will ask for password)

### Step 2: Run the Schema File

**Option A: Using psql command**
```bash
# In PowerShell/Command Prompt (from PostgreSQL bin folder)
psql -U postgres -f "C:\Users\jorya\OneDrive\Desktop\Advanced\Students_Management_System\database\schema.sql"
```

**Option B: Copy-paste in psql**
1. Open the `schema.sql` file in VS Code
2. Copy all content
3. Paste in the psql terminal
4. Press Enter

### Step 3: Verify Setup

After running the schema, verify tables were created:

```sql
-- Connect to the database
\c students_management_db

-- List all tables
\dt

-- Check users table
SELECT * FROM users;

-- Check students table
SELECT * FROM students;

-- Check attendance table
SELECT * FROM attendance;

-- View the attendance summary
SELECT * FROM v_attendance_summary;
```

---

## 📊 Sample Data Included

### Users Created:
- **Admin**: admin123 / admin@university.edu
- **Teachers**: 
  - teacher_john / john@university.edu
  - teacher_sarah / sarah@university.edu
- **Students**:
  - alice / student_alice
  - bob / student_bob
  - charlie / student_charlie
  - diana / student_diana
  - eve / student_eve

### Students Linked:
- 5 students with enrollment numbers (STU001-STU005)
- Each student has linked attendance records (past 10 days)

### Attendance Records:
- Sample attendance for 3 students over 5 days
- Status examples: present, absent, late

---

## 🔑 Key Features of the Schema

### 1. **Foreign Key Relationships**
- Students → Users (one-to-one: each student has one user account)
- Attendance → Students (many-to-one: each student has multiple attendance records)
- Attendance → Users (marked_by: teacher who marked attendance)

### 2. **Validation Constraints**
- Email format validation (regex)
- Username minimum length (3 chars)
- Phone number validation (if provided, min 10 digits)
- Date of birth can't be in future
- Attendance date can't be in future

### 3. **Data Integrity**
- Unique enrollment numbers for students
- One attendance record per student per date
- ON DELETE CASCADE for students when user deleted
- ON DELETE RESTRICT for teachers (can't delete if marked attendance)

### 4. **Indexes for Performance**
- Indexes on frequently searched fields (username, email, role)
- Composite index on (student_id, attendance_date) for attendance queries

### 5. **Helper Views**
- `v_student_details`: Combines student and user info
- `v_attendance_summary`: Shows attendance stats and percentage

---

## ⚙️ Important Database Connection Details

For **Python** and **PHP** implementations, use:

```
Database: students_management_db
Host: localhost
Port: 5432
User: postgres
Password: (your PostgreSQL password)
```

---

## 🔐 Security Notes

### Important for Production:
1. **Password Hashes**: The schema uses `password_hash` - actual implementations must:
   - Hash passwords using bcrypt or similar (never store plain passwords)
   - In Python: use `bcrypt` library
   - In PHP: use `password_hash()` function

2. **Sample Data**: 
   - Replace `hashed_password_*` with actual hashes
   - Delete sample users before deployment

3. **User Permissions**:
   - Create a dedicated database user (don't use postgres directly)
   - Restrict permissions by role

---

## 📝 Next Steps

Once this schema is set up and verified:
1. ✅ **DONE**: PostgreSQL Schema (current step)
2. ⏭️ **NEXT**: Python Implementation (Flask app with database connection)

---

## ❓ Troubleshooting

**Error: "database already exists"**
- The schema includes `DROP DATABASE IF EXISTS` - this is intentional for fresh setup
- Remove those lines if you want to keep existing data

**Error: "role 'postgres' does not exist"**
- Use your actual PostgreSQL username

**Can't connect to database**
- Check PostgreSQL service is running
- Verify host/port (usually localhost:5432)
- Check firewall settings

---

## 📚 SQL Commands Quick Reference

```sql
-- Connect to database
\c students_management_db

-- List all tables
\dt

-- Describe a table
\d users

-- List all views
\dv

-- Exit psql
\q

-- Show all data
SELECT * FROM users;
SELECT * FROM students;
SELECT * FROM attendance;

-- Useful queries
SELECT * FROM v_student_details;
SELECT * FROM v_attendance_summary;
```

