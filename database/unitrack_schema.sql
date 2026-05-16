-- UniTrack - Simplified Student Management & Attendance System
-- PostgreSQL Database Schema
-- CS 516 Advanced Programming Language

-- Drop existing objects
DROP TABLE IF EXISTS attendance CASCADE;
DROP TABLE IF EXISTS students CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP DATABASE IF EXISTS unitrack_db;

-- Create database
CREATE DATABASE unitrack_db;
\c unitrack_db

-- Users table (Admin/Teacher)
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    student_id SERIAL PRIMARY KEY,
    enrollment_number VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE attendance (
    attendance_id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL REFERENCES students(student_id) ON DELETE CASCADE,
    attendance_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO users (username, password_hash, role, first_name, last_name) VALUES
('admin', '$2y$10$slYQmyNdGzin7olVN3/p2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMm2', 'admin', 'Admin', 'User'),
('teacher1', '$2y$10$slYQmyNdGzin7olVN3/p2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMm2', 'teacher', 'John', 'Teacher');

INSERT INTO students (enrollment_number, first_name, last_name, email, phone, date_of_birth) VALUES
('STU001', 'Ahmed', 'Hassan', 'ahmed@university.edu', '0501234567', '2002-05-15'),
('STU002', 'Fatima', 'Ali', 'fatima@university.edu', '0502234567', '2003-03-20'),
('STU003', 'Mohammed', 'Ibrahim', 'mohammed@university.edu', '0503234567', '2002-08-10'),
('STU004', 'Sarah', 'Khan', 'sarah@university.edu', '0504234567', '2003-01-25'),
('STU005', 'Layla', 'Abdullah', 'layla@university.edu', '0505234567', '2002-11-30');

INSERT INTO attendance (student_id, attendance_date, status, remarks) VALUES
(1, '2026-05-13', 'present', ''),
(1, '2026-05-14', 'absent', 'Sick'),
(2, '2026-05-13', 'present', ''),
(2, '2026-05-14', 'late', 'Traffic'),
(3, '2026-05-13', 'present', ''),
(3, '2026-05-14', 'present', ''),
(4, '2026-05-13', 'absent', ''),
(5, '2026-05-13', 'present', ''),
(5, '2026-05-14', 'present', '');
