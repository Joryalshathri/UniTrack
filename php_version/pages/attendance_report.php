<?php
/**
 * Attendance Report
 * View and filter attendance records
 */

session_start();
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/Helpers.php';

// Check authentication
if (!isLoggedIn()) {
    redirect(BASE_URL . '?action=login');
}

$db = Database::getInstance();

// Get filters from query parameters
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$student_id = $_GET['student_id'] ?? '';
$status = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build dynamic query
$where_parts = ['DATE(a.attendance_date) >= $1 AND DATE(a.attendance_date) <= $2'];
$params = [$date_from, $date_to];
$param_count = 2;

if (!empty($student_id)) {
    $param_count++;
    $where_parts[] = "a.student_id = $" . $param_count;
    $params[] = $student_id;
}

if (!empty($status)) {
    $param_count++;
    $where_parts[] = "a.status = $" . $param_count;
    $params[] = $status;
}

$where_clause = implode(' AND ', $where_parts);

// Get total records count
$count_result = pg_query_params(
    $db->getConnection(),
    "SELECT COUNT(*) as total FROM attendance a WHERE {$where_clause}",
    $params
);
$count_row = pg_fetch_assoc($count_result);
$total_records = (int)$count_row['total'];
$total_pages = ceil($total_records / $per_page);

// Get attendance records
$param_count += 2;
$query = "SELECT a.attendance_id, DATE(a.attendance_date) as date, a.status, a.remarks, a.created_at,
                 s.enrollment_number, u.first_name, u.last_name
          FROM attendance a
          JOIN students s ON a.student_id = s.student_id
          JOIN users u ON s.user_id = u.user_id
          WHERE {$where_clause}
          ORDER BY a.attendance_date DESC, u.first_name, u.last_name
          LIMIT $" . ($param_count - 1) . " OFFSET $" . $param_count;

$params[] = $per_page;
$params[] = $offset;

$result = pg_query_params($db->getConnection(), $query, $params);
$records = [];
while ($row = pg_fetch_assoc($result)) {
    $records[] = $row;
}

// Get all students for filter dropdown
$students_result = pg_query(
    $db->getConnection(),
    'SELECT s.student_id, s.enrollment_number, u.first_name, u.last_name 
     FROM students s 
     JOIN users u ON s.user_id = u.user_id 
     WHERE u.is_active = true
     ORDER BY u.first_name, u.last_name'
);
$students = [];
while ($row = pg_fetch_assoc($students_result)) {
    $students[] = $row;
}

// Calculate statistics
$stats_result = pg_query_params(
    $db->getConnection(),
    "SELECT 
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late
     FROM attendance a WHERE {$where_clause}",
    array_slice($params, 0, count($params) - 2)
);

$stats = pg_fetch_assoc($stats_result);
$stats_present = (int)($stats['present'] ?? 0);
$stats_absent = (int)($stats['absent'] ?? 0);
$stats_late = (int)($stats['late'] ?? 0);

?>
<?php require_once 'templates/header.php'; ?>

<div class="main-content">
    <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Attendance Report</h1>
                <p class="text-muted">View and filter attendance records</p>
            </div>
            <a href="<?php echo BASE_URL; ?>?action=mark_attendance" class="btn btn-primary">
                <i class="fas fa-clipboard-check"></i> Mark Attendance
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-success mb-0"><?php echo $stats_present; ?></h3>
                        <p class="text-muted mb-0">Present</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-danger mb-0"><?php echo $stats_absent; ?></h3>
                        <p class="text-muted mb-0">Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-warning mb-0"><?php echo $stats_late; ?></h3>
                        <p class="text-muted mb-0">Late</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-info mb-0"><?php echo $total_records; ?></h3>
                        <p class="text-muted mb-0">Total Records</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="action" value="attendance_report">
                    
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date:</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date:</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="student_id" class="form-label">Student:</label>
                        <select class="form-select" id="student_id" name="student_id">
                            <option value="">— All Students —</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['student_id']; ?>" 
                                        <?php echo $s['student_id'] == $student_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['enrollment_number'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">— All —</option>
                            <option value="present" <?php echo $status === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo $status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="late" <?php echo $status === 'late' ? 'selected' : ''; ?>>Late</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="<?php echo BASE_URL; ?>?action=attendance_report" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Records Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Attendance Records (<?php echo $total_records; ?> total)</h5>
            </div>
            <div class="card-body">
                <?php if (count($records) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Enrollment</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Recorded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><strong><?php echo date('M d, Y', strtotime($record['date'])); ?></strong></td>
                                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['enrollment_number']); ?></td>
                                        <td>
                                            <?php 
                                                $status_class = [
                                                    'present' => 'success',
                                                    'absent' => 'danger',
                                                    'late' => 'warning'
                                                ][$record['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo !empty($record['remarks']) ? htmlspecialchars($record['remarks']) : '<span class="text-muted">—</span>'; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>?action=attendance_report&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&student_id=<?php echo urlencode($student_id); ?>&status=<?php echo urlencode($status); ?>&page=1">
                                            First
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>?action=attendance_report&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&student_id=<?php echo urlencode($student_id); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $page - 1; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <li class="page-item active">
                                    <span class="page-link">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                                </li>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>?action=attendance_report&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&student_id=<?php echo urlencode($student_id); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $page + 1; ?>">
                                            Next
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>?action=attendance_report&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&student_id=<?php echo urlencode($student_id); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $total_pages; ?>">
                                            Last
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No attendance records found for the selected criteria.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
