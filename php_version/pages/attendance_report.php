<?php
/**
 * Attendance Report Page
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
$attendance = new Attendance();
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : null;

$records = $attendance->getAttendanceRecords($days, null, $status);
$summary = $attendance->getAttendanceSummary($days);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
        }
        .sidebar h4 {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar a i {
            width: 20px;
            margin-right: 10px;
        }
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .user-info small {
            display: block;
            opacity: 0.8;
            margin-top: 5px;
        }
        .logout-btn {
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
        }
        .report-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .stat-badge {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 5px;
            font-weight: 600;
        }
        .stat-badge.success {
            background-color: #d4edda;
            color: #155724;
        }
        .stat-badge.danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .stat-badge.warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .table-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .table th {
            border: none;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .table td {
            border-color: #e9ecef;
            vertical-align: middle;
        }
        .status-present {
            color: #28a745;
            font-weight: 600;
        }
        .status-absent {
            color: #dc3545;
            font-weight: 600;
        }
        .status-late {
            color: #ffc107;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>
            <i class="fas fa-graduation-cap"></i> SMMS
        </h4>

        <div class="user-info">
            <strong><?php echo ucfirst($currentUser['first_name']); ?> <?php echo ucfirst($currentUser['last_name']); ?></strong>
            <small><?php echo ucfirst($currentUser['role']); ?></small>
            <small><?php echo $currentUser['email']; ?></small>
        </div>

        <a href="<?php echo BASE_URL . '/?action=dashboard'; ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL . '/?action=students'; ?>">
            <i class="fas fa-users"></i> Students
        </a>
        <a href="<?php echo BASE_URL . '/?action=mark_attendance'; ?>">
            <i class="fas fa-clipboard-check"></i> Mark Attendance
        </a>
        <a href="<?php echo BASE_URL . '/?action=attendance_report'; ?>">
            <i class="fas fa-file-alt"></i> Attendance Report
        </a>
        <a href="<?php echo BASE_URL . '/?action=reports'; ?>">
            <i class="fas fa-bar-chart"></i> Reports
        </a>

        <div class="logout-btn">
            <a href="<?php echo BASE_URL . '/?action=logout'; ?>" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="content">
        <div class="row mb-4">
            <div class="col">
                <h1 class="mb-0"><i class="fas fa-file-alt"></i> Attendance Report</h1>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="report-card">
            <h5 class="mb-3">Summary (Last <?php echo $days; ?> Days)</h5>
            <div>
                <span class="stat-badge success">
                    <i class="fas fa-check"></i> Present: <?php echo array_sum(array_column($summary, 'present_days')); ?>
                </span>
                <span class="stat-badge danger">
                    <i class="fas fa-times"></i> Absent: <?php echo array_sum(array_column($summary, 'absent_days')); ?>
                </span>
                <span class="stat-badge warning">
                    <i class="fas fa-clock"></i> Late: <?php echo array_sum(array_column($summary, 'late_days')); ?>
                </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="report-card">
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="action" value="attendance_report">
                <select name="days" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                    <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 days</option>
                    <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 days</option>
                    <option value="60" <?php echo $days == 60 ? 'selected' : ''; ?>>Last 60 days</option>
                    <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 days</option>
                </select>
                <select name="status" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="present" <?php echo $status == 'present' ? 'selected' : ''; ?>>Present</option>
                    <option value="absent" <?php echo $status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                    <option value="late" <?php echo $status == 'late' ? 'selected' : ''; ?>>Late</option>
                </select>
            </form>
        </div>

        <!-- Records Table -->
        <div class="table-card">
            <h5 class="mb-3">Attendance Records</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student Name</th>
                            <th>Enrollment #</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No attendance records found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                                    <td><?php echo ucfirst($record['first_name']) . ' ' . ucfirst($record['last_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $record['enrollment_number']; ?></span></td>
                                    <td>
                                        <span class="status-<?php echo strtolower($record['status']); ?>">
                                            <?php echo ucfirst($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $record['remarks'] ?? '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Summary Table -->
        <div class="table-card" style="margin-top: 30px;">
            <h5 class="mb-3">Student Summary</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Enrollment #</th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summary)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No data available
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summary as $stud): ?>
                                <tr>
                                    <td><?php echo ucfirst($stud['first_name']) . ' ' . ucfirst($stud['last_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $stud['enrollment_number']; ?></span></td>
                                    <td><?php echo $stud['total_days'] ?? 0; ?></td>
                                    <td class="status-present"><?php echo $stud['present_days'] ?? 0; ?></td>
                                    <td class="status-absent"><?php echo $stud['absent_days'] ?? 0; ?></td>
                                    <td class="status-late"><?php echo $stud['late_days'] ?? 0; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($stud['attendance_percentage'] ?? 0) >= 80 ? 'bg-success' : (($stud['attendance_percentage'] ?? 0) >= 50 ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?php echo $stud['attendance_percentage'] ?? 0; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
