<?php
/**
 * Student Management Class
 */

class Student {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all students with pagination
     */
    public function getAllStudents($page = 1, $perPage = 10, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        if ($search) {
            $search = "%$search%";
            $sql = "SELECT s.student_id, s.enrollment_number, s.date_of_birth, s.phone_number,
                           s.address, s.city, s.state, s.postal_code,
                           u.user_id, u.first_name, u.last_name, u.email
                    FROM students s
                    JOIN users u ON s.user_id = u.user_id
                    WHERE u.first_name ILIKE $1 OR u.last_name ILIKE $1 
                       OR s.enrollment_number ILIKE $1 OR u.email ILIKE $1
                    ORDER BY s.student_id DESC
                    LIMIT $2 OFFSET $3";
            return $this->db->fetchAll($sql, [$search, $perPage, $offset]);
        }

        $sql = "SELECT s.student_id, s.enrollment_number, s.date_of_birth, s.phone_number,
                       s.address, s.city, s.state, s.postal_code,
                       u.user_id, u.first_name, u.last_name, u.email
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                ORDER BY s.student_id DESC
                LIMIT $1 OFFSET $2";
        return $this->db->fetchAll($sql, [$perPage, $offset]);
    }

    /**
     * Get student by ID
     */
    public function getStudentById($student_id) {
        $sql = "SELECT s.*, u.user_id, u.first_name, u.last_name, u.email, u.username
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                WHERE s.student_id = $1";
        return $this->db->fetch($sql, [$student_id]);
    }

    /**
     * Get total students count
     */
    public function getTotalStudents($search = '') {
        if ($search) {
            $search = "%$search%";
            $sql = "SELECT COUNT(*) as total FROM students s
                    JOIN users u ON s.user_id = u.user_id
                    WHERE u.first_name ILIKE $1 OR u.last_name ILIKE $1 
                       OR s.enrollment_number ILIKE $1 OR u.email ILIKE $1";
            $result = $this->db->fetch($sql, [$search]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM students";
            $result = $this->db->fetch($sql);
        }
        return $result['total'] ?? 0;
    }

    /**
     * Add new student
     */
    public function addStudent($data) {
        try {
            // Check if username exists
            $check = $this->db->fetch("SELECT user_id FROM users WHERE username = $1", [$data['username']]);
            if ($check) {
                return ['success' => false, 'error' => 'Username already exists'];
            }

            // Create user account
            $passwordHash = hashPassword($data['enrollment_number']);
            $userSql = "INSERT INTO users (username, email, first_name, last_name, password_hash, role, is_active)
                        VALUES ($1, $2, $3, $4, $5, 'student', true) RETURNING user_id";
            $user = $this->db->fetch($userSql, [
                $data['username'],
                $data['email'],
                $data['first_name'],
                $data['last_name'],
                $passwordHash
            ]);

            $userId = $user['user_id'];

            // Create student record
            $studentSql = "INSERT INTO students (user_id, enrollment_number, date_of_birth, phone_number,
                                               address, city, state, postal_code, enrollment_date)
                          VALUES ($1, $2, $3, $4, $5, $6, $7, $8, CURRENT_DATE) RETURNING student_id";
            $student = $this->db->fetch($studentSql, [
                $userId,
                $data['enrollment_number'],
                $data['date_of_birth'] ?? null,
                $data['phone_number'] ?? null,
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null
            ]);

            return ['success' => true, 'student_id' => $student['student_id']];
        } catch (Exception $e) {
            logMessage('Add student error: ' . $e->getMessage(), 'error');
            return ['success' => false, 'error' => 'Error adding student'];
        }
    }

    /**
     * Update student
     */
    public function updateStudent($student_id, $data) {
        try {
            $sql = "UPDATE students 
                    SET date_of_birth = $1, phone_number = $2, address = $3, city = $4, state = $5, postal_code = $6
                    WHERE student_id = $7";
            $this->db->execute($sql, [
                $data['date_of_birth'] ?? null,
                $data['phone_number'] ?? null,
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $student_id
            ]);

            // Update user info
            $userSql = "UPDATE users SET first_name = $1, last_name = $2 WHERE user_id = (SELECT user_id FROM students WHERE student_id = $3)";
            $this->db->execute($userSql, [$data['first_name'], $data['last_name'], $student_id]);

            return ['success' => true, 'message' => 'Student updated successfully'];
        } catch (Exception $e) {
            logMessage('Update student error: ' . $e->getMessage(), 'error');
            return ['success' => false, 'error' => 'Error updating student'];
        }
    }

    /**
     * Delete student
     */
    public function deleteStudent($student_id) {
        try {
            $sql = "SELECT user_id FROM students WHERE student_id = $1";
            $student = $this->db->fetch($sql, [$student_id]);

            if (!$student) {
                return ['success' => false, 'error' => 'Student not found'];
            }

            $this->db->execute("DELETE FROM students WHERE student_id = $1", [$student_id]);
            $this->db->execute("DELETE FROM users WHERE user_id = $1", [$student['user_id']]);

            return ['success' => true, 'message' => 'Student deleted successfully'];
        } catch (Exception $e) {
            logMessage('Delete student error: ' . $e->getMessage(), 'error');
            return ['success' => false, 'error' => 'Error deleting student'];
        }
    }
}
?>
