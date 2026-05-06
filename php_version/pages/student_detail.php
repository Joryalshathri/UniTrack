<?php
/**
 * Student Detail - View Single Student Profile
 * Displays student information and recent attendance records
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
$student_id = $_GET['id'] ?? null;
$error = '';

if (!$student_id) {
    $error = 'Student ID is required';
    redirect(BASE_URL . '?action=students_list');
}

// Fetch student information
$result = pg_query_params(
    $db->getConnection(),
    'SELECT s.student_id, s.enrollment_number, s.address, s.city, s.state, s.postal_code, 
            s.created_at, u.user_id, u.first_name, u.last_name, u.email, u.phone, u.role, u.is_active
     FROM students s 
     JOIN users u ON s.user_id = u.user_id 
     WHERE s.student_id = $1',
    [$student_id]
);

if (pg_num_rows($result) === 0) {
    $error = 'Student not found';
    redirect(BASE_URL . '?action=students_list');
}

$student = pg_fetch_assoc($result);

// Fetch recent attendance records (last 30 days)
$attendance_result = pg_query_params(
    $db->getConnection(),
    'SELECT a.attendance_id, a.attendance_date, a.status, a.remarks, a.created_at
     FROM attendance a
     WHERE a.student_id = $1 AND DATE(a.attendance_date) >= CURRENT_DATE - INTERVAL \'30 days\'
     ORDER BY a.attendance_date DESC',
    [$student_id]
);

$attendance_records = [];
while ($row = pg_fetch_assoc($attendance_result)) {
    $attendance_records[] = $row;
}

// Calculate attendance statistics
$stats_result = pg_query_params(
    $db->getConnection(),
    'SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = \'late\' THEN 1 ELSE 0 END) as late
     FROM attendance
     WHERE student_id = $1',
    [$student_id]
);

$stats = pg_fetch_assoc($stats_result);
$total_records = (int)$stats['total'];
$present_count = (int)($stats['present'] ?? 0);
$absent_count = (int)($stats['absent'] ?? 0);
$late_count = (int)($stats['late'] ?? 0);
$attendance_percentage = $total_records > 0 ? round(($present_count / $total_records) * 100, 2) : 0;

?>
<?php require_once 'templates/header.php'; ?>

<div class="main-content">
    <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>
                <p class="text-muted">Enrollment: <?php echo htmlspecialchars($student['enrollment_number']); ?></p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>?action=student_form&id=<?php echo $student_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="<?php echo BASE_URL; ?>?action=delete_student&id=<?php echo $student_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <a href="<?php echo BASE_URL; ?>?action=students_list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted"><small>FIRST NAME</small></p>
                                <p class="h6"><?php echo htmlspecialchars($student['first_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted"><small>LAST NAME</small></p>
                                <p class="h6"><?php echo htmlspecialchars($student['last_name']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted"><small>ENROLLMENT NUMBER</small></p>
                                <p class="h6"><?php echo htmlspecialchars($student['enrollment_number']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted"><small>ROLE</small></p>
                                <p class="h6">
                                    <span class="badge bg-info"><?php echo ucfirst($student['role']); ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted"><small>EMAIL</small></p>
                                <p class="h6"><a href="mailto:<?php echo htmlspecialchars($student['email']); ?>"><?php echo htmlspecialchars($student['email']); ?></a></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted"><small>PHONE</small></p>
                                <p class="h6"><a href="tel:<?php echo htmlspecialchars($student['phone']); ?>"><?php echo htmlspecialchars($student['phone']); ?></a></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted"><small>STATUS</small></p>
                                <p class="h6">
                                    <?php if ($student['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted"><small>CREATED</small></p>
                                <p class="h6"><?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                            </div>
                        </div>

                        <?php if (!empty($student['address'])): ?>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-muted"><small>ADDRESS</small></p>
                                    <p class="h6">
                                        <?php echo htmlspecialchars($student['address']); ?><br>
                                        <?php 
                                            echo htmlspecialchars($student['city']); 
                                            if (!empty($student['state'])) echo ', ' . htmlspecialchars($student['state']);
                                            if (!empty($student['postal_code'])) echo ' ' . htmlspecialchars($student['postal_code']);
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Attendance Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h2 class="text-success"><?php echo $attendance_percentage; ?>%</h2>
                            <p class="text-muted">Attendance Rate</p>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Present</small>
                                <small class="text-success"><strong><?php echo $present_count; ?></strong></small>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $total_records > 0 ? ($present_count / $total_records) * 100 : 0; ?>%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Absent</small>
                                <small class="text-danger"><strong><?php echo $absent_count; ?></strong></small>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-danger" style="width: <?php echo $total_records > 0 ? ($absent_count / $total_records) * 100 : 0; ?>%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Late</small>
                                <small class="text-warning"><strong><?php echo $late_count; ?></strong></small>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo $total_records > 0 ? ($late_count / $total_records) * 100 : 0; ?>%"></div>
                            </div>
                        </div>

                        <hr>
                        <p class="text-muted text-center"><small>Total Records: <strong><?php echo $total_records; ?></strong></small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance Records -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Recent Attendance (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <?php if (count($attendance_records) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Recorded At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $record): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></strong>
                                        </td>
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
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No attendance records found in the last 30 days.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
