-- ============================================================================
-- Students' Management & Attendance System
-- PostgreSQL Database Schema
-- CS 516 Advanced Programming Language - Term Project
-- ============================================================================

-- Drop existing objects (if they exist) - for fresh setup
DROP TABLE IF EXISTS attendance CASCADE;
DROP TABLE IF EXISTS students CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP DATABASE IF EXISTS students_management_db;

-- ============================================================================
-- CREATE DATABASE
-- ============================================================================
CREATE DATABASE students_management_db;

-- Connect to the new database
\c students_management_db

-- ============================================================================
-- ROLE ENUMERATION TYPE
-- ============================================================================
CREATE TYPE user_role AS ENUM ('admin', 'teacher', 'student');

-- ============================================================================
-- ATTENDANCE STATUS TYPE
-- ============================================================================
CREATE TYPE attendance_status AS ENUM ('present', 'absent', 'late');

-- ============================================================================
-- TABLE 1: USERS (Authentication & Role Management)
-- ============================================================================
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role user_role NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT valid_email CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$'),
    CONSTRAINT valid_username CHECK (LENGTH(username) >= 3)
);

-- ============================================================================
-- TABLE 2: STUDENTS (Student Information)
-- ============================================================================
CREATE TABLE students (
    student_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    enrollment_number VARCHAR(20) NOT NULL UNIQUE,
    date_of_birth DATE,
    phone_number VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(10),
    enrollment_date DATE NOT NULL DEFAULT CURRENT_DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key
    CONSTRAINT fk_students_user FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT valid_phone CHECK (phone_number IS NULL OR LENGTH(phone_number) >= 10),
    CONSTRAINT valid_dob CHECK (date_of_birth IS NULL OR date_of_birth <= CURRENT_DATE)
);

-- ============================================================================
-- TABLE 3: ATTENDANCE (Attendance Records)
-- ============================================================================
CREATE TABLE attendance (
    attendance_id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status attendance_status NOT NULL,
    marked_by INT NOT NULL,
    remarks VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) 
        REFERENCES students(student_id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_teacher FOREIGN KEY (marked_by) 
        REFERENCES users(user_id) ON DELETE RESTRICT,
    
    -- Constraints
    CONSTRAINT valid_attendance_date CHECK (attendance_date <= CURRENT_DATE),
    CONSTRAINT unique_student_date UNIQUE (student_id, attendance_date)
);

-- ============================================================================
-- INDEXES (For better query performance)
-- ============================================================================
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_students_user_id ON students(user_id);
CREATE INDEX idx_students_enrollment_number ON students(enrollment_number);
CREATE INDEX idx_attendance_student_id ON attendance(student_id);
CREATE INDEX idx_attendance_date ON attendance(attendance_date);
CREATE INDEX idx_attendance_student_date ON attendance(student_id, attendance_date);

-- ============================================================================
-- SAMPLE DATA (SEED DATA)
-- ============================================================================

-- Insert Sample Users
INSERT INTO users (username, email, password_hash, role, first_name, last_name) VALUES
-- Admin User
('admin123', 'admin@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'admin', 'Admin', 'User'),

-- Teacher Users
('teacher_john', 'john@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'teacher', 'John', 'Smith'),
('teacher_sarah', 'sarah@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'teacher', 'Sarah', 'Johnson'),

-- Student Users
('student_alice', 'alice@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'student', 'Alice', 'Brown'),
('student_bob', 'bob@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'student', 'Bob', 'Davis'),
('student_charlie', 'charlie@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'student', 'Charlie', 'Wilson'),
('student_diana', 'diana@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'student', 'Diana', 'Martinez'),
('student_eve', 'eve@university.edu', '$2b$10$btmfy7D2JN.t9TwPCte4juDrp6hnvId4KzS9fv8Q.WtAOybro/oeq', 'student', 'Eve', 'Garcia');

-- Insert Sample Students (linked to student users)
INSERT INTO students (user_id, enrollment_number, date_of_birth, phone_number, address, city, state, postal_code) VALUES
(4, 'STU001', '2002-05-15', '5551234567', '123 Main St', 'Boston', 'MA', '02101'),
(5, 'STU002', '2002-08-22', '5551234568', '456 Oak Ave', 'Boston', 'MA', '02102'),
(6, 'STU003', '2003-01-10', '5551234569', '789 Pine Rd', 'Cambridge', 'MA', '02138'),
(7, 'STU004', '2002-12-05', '5551234570', '321 Elm St', 'Boston', 'MA', '02103'),
(8, 'STU005', '2003-03-18', '5551234571', '654 Maple Ln', 'Cambridge', 'MA', '02139');

-- Insert Sample Attendance Records (for past 10 days)
INSERT INTO attendance (student_id, attendance_date, status, marked_by) VALUES
-- Student 1 (Alice Brown)
(1, CURRENT_DATE - INTERVAL '9 days', 'present', 2),
(1, CURRENT_DATE - INTERVAL '8 days', 'present', 2),
(1, CURRENT_DATE - INTERVAL '7 days', 'absent', 2),
(1, CURRENT_DATE - INTERVAL '6 days', 'present', 2),
(1, CURRENT_DATE - INTERVAL '5 days', 'late', 2),

-- Student 2 (Bob Davis)
(2, CURRENT_DATE - INTERVAL '9 days', 'present', 2),
(2, CURRENT_DATE - INTERVAL '8 days', 'late', 2),
(2, CURRENT_DATE - INTERVAL '7 days', 'present', 2),
(2, CURRENT_DATE - INTERVAL '6 days', 'absent', 2),
(2, CURRENT_DATE - INTERVAL '5 days', 'present', 2),

-- Student 3 (Charlie Wilson)
(3, CURRENT_DATE - INTERVAL '9 days', 'absent', 3),
(3, CURRENT_DATE - INTERVAL '8 days', 'absent', 3),
(3, CURRENT_DATE - INTERVAL '7 days', 'present', 3),
(3, CURRENT_DATE - INTERVAL '6 days', 'present', 3),
(3, CURRENT_DATE - INTERVAL '5 days', 'present', 3);

-- ============================================================================
-- VIEWS (For easier queries - optional but helpful)
-- ============================================================================

-- View: Student with User Details
CREATE VIEW v_student_details AS
SELECT 
    s.student_id,
    s.enrollment_number,
    u.user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    s.date_of_birth,
    s.phone_number,
    s.address,
    s.city,
    s.state,
    s.postal_code,
    s.enrollment_date,
    s.is_active
FROM students s
JOIN users u ON s.user_id = u.user_id;

-- View: Attendance Summary
CREATE VIEW v_attendance_summary AS
SELECT 
    s.student_id,
    s.enrollment_number,
    u.first_name,
    u.last_name,
    COUNT(*) as total_classes,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
    ROUND(
        (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END)::NUMERIC / COUNT(*)) * 100, 
        2
    ) as attendance_percentage
FROM students s
JOIN users u ON s.user_id = u.user_id
LEFT JOIN attendance a ON s.student_id = a.student_id
GROUP BY s.student_id, s.enrollment_number, u.first_name, u.last_name;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
