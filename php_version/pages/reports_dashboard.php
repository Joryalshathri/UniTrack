<?php
/**
 * Reports Dashboard
 * System-wide analytics and statistics
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

// Get system statistics
$stats_result = pg_query($db->getConnection(), 
    'SELECT 
        (SELECT COUNT(*) FROM students) as total_students,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM attendance) as total_records
    FROM users LIMIT 1'
);

$stats = pg_fetch_assoc($stats_result);
$total_students = (int)$stats['total_students'];
$total_users = (int)$stats['total_users'];
$total_records = (int)$stats['total_records'];

// Get today's attendance summary
$today = date('Y-m-d');
$today_result = pg_query_params(
    $db->getConnection(),
    'SELECT 
        SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late
     FROM attendance WHERE DATE(attendance_date) = $1',
    [$today]
);

$today_stats = pg_fetch_assoc($today_result);
$today_present = (int)($today_stats['present'] ?? 0);
$today_absent = (int)($today_stats['absent'] ?? 0);
$today_late = (int)($today_stats['late'] ?? 0);
$today_total = $today_present + $today_absent + $today_late;

// Get average attendance rate
$avg_result = pg_query($db->getConnection(),
    'SELECT 
        ROUND(100.0 * COUNT(CASE WHEN status = \'present\' THEN 1 END) / 
            NULLIF(COUNT(*), 0), 2) as avg_rate
     FROM attendance'
);

$avg_stats = pg_fetch_assoc($avg_result);
$avg_rate = (float)($avg_stats['avg_rate'] ?? 0);

// Get student performance (top 5)
$performance_result = pg_query($db->getConnection(),
    'SELECT 
        u.first_name, u.last_name, s.enrollment_number,
        COUNT(*) as total_records,
        SUM(CASE WHEN a.status = \'present\' THEN 1 ELSE 0 END) as present_count,
        ROUND(100.0 * SUM(CASE WHEN a.status = \'present\' THEN 1 ELSE 0 END) / 
            NULLIF(COUNT(*), 0), 2) as attendance_rate
     FROM students s
     JOIN users u ON s.user_id = u.user_id
     LEFT JOIN attendance a ON s.student_id = a.student_id
     GROUP BY s.student_id, u.first_name, u.last_name, s.enrollment_number
     ORDER BY attendance_rate DESC, u.first_name, u.last_name
     LIMIT 5'
);

$top_performers = [];
while ($row = pg_fetch_assoc($performance_result)) {
    $top_performers[] = $row;
}

// Get recent attendance records
$recent_result = pg_query($db->getConnection(),
    'SELECT 
        DATE(a.attendance_date) as date,
        u.first_name, u.last_name, s.enrollment_number,
        a.status, a.remarks
     FROM attendance a
     JOIN students s ON a.student_id = s.student_id
     JOIN users u ON s.user_id = u.user_id
     ORDER BY a.created_at DESC
     LIMIT 10'
);

$recent_records = [];
while ($row = pg_fetch_assoc($recent_result)) {
    $recent_records[] = $row;
}

?>
<?php require_once 'templates/header.php'; ?>

<div class="main-content">
    <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="mb-4">
            <h1>Reports & Analytics Dashboard</h1>
            <p class="text-muted">System-wide statistics and performance metrics</p>
        </div>

        <!-- Key Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-primary"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $total_students; ?></h3>
                        <p class="text-muted mb-0">Total Students</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-tie fa-3x text-info"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $total_users; ?></h3>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-clipboard-list fa-3x text-success"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $total_records; ?></h3>
                        <p class="text-muted mb-0">Total Records</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-pie fa-3x text-warning"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $avg_rate; ?>%</h3>
                        <p class="text-muted mb-0">Avg. Attendance Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Summary -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Today's Summary (<?php echo date('M d, Y'); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <h4 class="text-success mb-0"><?php echo $today_present; ?></h4>
                                    <p class="text-muted mb-0"><small>Present</small></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <h4 class="text-danger mb-0"><?php echo $today_absent; ?></h4>
                                    <p class="text-muted mb-0"><small>Absent</small></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <h4 class="text-warning mb-0"><?php echo $today_late; ?></h4>
                                    <p class="text-muted mb-0"><small>Late</small></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <h4 class="text-info mb-0"><?php echo $today_total; ?></h4>
                                    <p class="text-muted mb-0"><small>Total Marked</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout for Top Performers and Recent Records -->
        <div class="row mb-4">
            <!-- Top Performers -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Top Performers</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($top_performers) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Attendance %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_performers as $performer): ?>
                                            <tr>
                                                <td>
                                                    <small><?php echo htmlspecialchars($performer['first_name'] . ' ' . $performer['last_name']); ?></small>
                                                </td>
                                                <td>
                                                    <small class="text-success"><strong><?php echo $performer['attendance_rate'] ?? 0; ?>%</strong></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="<?php echo BASE_URL; ?>?action=student_performance_report" class="btn btn-sm btn-outline-success mt-2">
                                View All →
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-0"><small>No performance data available</small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_records) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_records as $record): ?>
                                            <tr>
                                                <td>
                                                    <small><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M d', strtotime($record['date'])); ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $status_class = [
                                                            'present' => 'success',
                                                            'absent' => 'danger',
                                                            'late' => 'warning'
                                                        ][$record['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?> badge-sm">
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="<?php echo BASE_URL; ?>?action=attendance_report" class="btn btn-sm btn-outline-info mt-2">
                                View All →
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-0"><small>No recent activity</small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-link"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="<?php echo BASE_URL; ?>?action=students_list" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> View All Students
                            </a>
                            <a href="<?php echo BASE_URL; ?>?action=mark_attendance" class="btn btn-outline-success">
                                <i class="fas fa-clipboard-check"></i> Mark Attendance
                            </a>
                            <a href="<?php echo BASE_URL; ?>?action=attendance_report" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar"></i> View Attendance
                            </a>
                            <a href="<?php echo BASE_URL; ?>?action=student_detail" class="btn btn-outline-warning">
                                <i class="fas fa-user-circle"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
