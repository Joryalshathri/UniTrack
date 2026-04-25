<?php
/**
 * Attendance Management Class
 */

class Attendance {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Mark attendance for students
     */
    public function markAttendance($records) {
        try {
            $savedCount = 0;
            foreach ($records as $record) {
                if (empty($record['student_id']) || empty($record['status'])) {
                    continue;
                }

                $today = date('Y-m-d');
                $checkSql = "SELECT attendance_id FROM attendance 
                            WHERE student_id = $1 AND DATE(attendance_date) = $2";
                $existing = $this->db->fetch($checkSql, [$record['student_id'], $today]);

                if ($existing) {
                    // Update existing
                    $updateSql = "UPDATE attendance 
                                 SET status = $1, remarks = $2, marked_by = $3
                                 WHERE attendance_id = $4";
                    $this->db->execute($updateSql, [
                        $record['status'],
                        $record['remarks'] ?? '',
                        $_SESSION['user_id'],
                        $existing['attendance_id']
                    ]);
                } else {
                    // Insert new
                    $insertSql = "INSERT INTO attendance (student_id, attendance_date, status, marked_by, remarks)
                                 VALUES ($1, CURRENT_TIMESTAMP, $2, $3, $4)";
                    $this->db->execute($insertSql, [
                        $record['student_id'],
                        $record['status'],
                        $_SESSION['user_id'],
                        $record['remarks'] ?? ''
                    ]);
                }
                $savedCount++;
            }

            return ['success' => true, 'saved_count' => $savedCount];
        } catch (Exception $e) {
            logMessage('Mark attendance error: ' . $e->getMessage(), 'error');
            return ['success' => false, 'error' => 'Error saving attendance'];
        }
    }

    /**
     * Get attendance records with filters
     */
    public function getAttendanceRecords($days = 30, $student_id = null, $status = null) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $sql = "SELECT a.attendance_id, a.attendance_date, a.status, a.remarks,
                       s.student_id, s.enrollment_number,
                       u.first_name, u.last_name, u.email,
                       m.first_name as marked_by_first, m.last_name as marked_by_last
                FROM attendance a
                JOIN students s ON a.student_id = s.student_id
                JOIN users u ON s.user_id = u.user_id
                LEFT JOIN users m ON a.marked_by = m.user_id
                WHERE DATE(a.attendance_date) >= $1";
        
        $params = [$startDate];
        $paramIndex = 2;

        if ($student_id) {
            $sql .= " AND a.student_id = $$paramIndex";
            $params[] = $student_id;
            $paramIndex++;
        }

        if ($status) {
            $sql .= " AND a.status = $$paramIndex";
            $params[] = $status;
        }

        $sql .= " ORDER BY a.attendance_date DESC, u.first_name, u.last_name";
        
        // Replace parameter placeholders
        for ($i = 0; $i < count($params); $i++) {
            $sql = str_replace('$' . ($i + 1), '$' . ($i + 1), $sql);
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get attendance summary
     */
    public function getAttendanceSummary($days = 30, $student_id = null) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $sql = "SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number,
                       COUNT(*) as total_days,
                       SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                       SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                       SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                       ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_percentage
                FROM attendance a
                JOIN students s ON a.student_id = s.student_id
                JOIN users u ON s.user_id = u.user_id
                WHERE DATE(a.attendance_date) >= $1";
        
        $params = [$startDate];

        if ($student_id) {
            $sql .= " AND a.student_id = $2";
            $params[] = $student_id;
        }

        $sql .= " GROUP BY s.student_id, u.first_name, u.last_name, s.enrollment_number
                 ORDER BY attendance_percentage DESC, u.first_name, u.last_name";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get daily attendance overview
     */
    public function getDailyOverview($days = 7) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $sql = "SELECT DATE(attendance_date) as date,
                       COUNT(*) as total_marked,
                       SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                       SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                       SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                       ROUND(100.0 * SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_rate
                FROM attendance
                WHERE DATE(attendance_date) >= $1
                GROUP BY DATE(attendance_date)
                ORDER BY DATE(attendance_date) DESC";

        return $this->db->fetchAll($sql, [$startDate]);
    }

    /**
     * Get all active students for marking
     */
    public function getActiveStudents() {
        $sql = "SELECT s.student_id, u.first_name, u.last_name, s.enrollment_number, u.email
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                WHERE u.is_active = true
                ORDER BY u.first_name, u.last_name";
        return $this->db->fetchAll($sql);
    }
}
?>
