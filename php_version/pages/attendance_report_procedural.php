<?php
/**
 * Attendance Report - Procedural Approach
 * Demonstrates procedural pattern from documentation
 * Display attendance records with filtering by date range and status
 */

require_once '../config/config.php';
require_once '../config/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$days = $_GET['days'] ?? 30;
$status = $_GET['status'] ?? '';
$startDate = date('Y-m-d', strtotime("-$days days"));

// Get database instance
$db = Database::getInstance();

// Build SQL query with filters
$sql = "SELECT a.attendance_date, a.status, a.remarks,
               s.enrollment_number, u.first_name, u.last_name
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        WHERE DATE(a.attendance_date) >= $1";
$params = [$startDate];
$paramIndex = 2;

// Add status filter if provided
if (!empty($status)) {
    $sql .= " AND a.status = $$paramIndex";
    $params[] = $status;
    $paramIndex++;
}

$sql .= " ORDER BY a.attendance_date DESC";

// Execute query
$records = $db->fetchAll($sql, $params);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - Procedural Approach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Attendance Report</h1>
        <p class="text-muted">Procedural approach with filtering example from documentation</p>
        
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="days" class="form-label">Days Range</label>
                        <select class="form-select" name="days" id="days">
                            <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 days</option>
                            <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 days</option>
                            <option value="60" <?php echo $days == 60 ? 'selected' : ''; ?>>Last 60 days</option>
                            <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 days</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="">All Status</option>
                            <option value="present" <?php echo $status == 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo $status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="late" <?php echo $status == 'late' ? 'selected' : ''; ?>>Late</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <?php if (empty($records)): ?>
            <div class="alert alert-info">No attendance records found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Enrollment</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($record['attendance_date']))); ?></td>
                                <td><?php echo htmlspecialchars($record['enrollment_number']); ?></td>
                                <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $record['status'] == 'present' ? 'success' : 
                                             ($record['status'] == 'absent' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($record['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <a href="attendance_report.php" class="btn btn-secondary mt-3">Back to OOP Version</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
