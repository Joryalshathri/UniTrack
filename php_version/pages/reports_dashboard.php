<?php
/**
 * Reports Dashboard Page
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
$attendance = new Attendance();
$student = new Student();

// Get statistics
$db = Database::getInstance();
$totalStudents = $student->getTotalStudents();
$totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users")['count'];
$totalAttendance = $db->fetch("SELECT COUNT(*) as count FROM attendance")['count'];

// Get today's attendance summary
$today = date('Y-m-d');
$todayAttendance = $db->fetch("
    SELECT 
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
    FROM attendance
    WHERE DATE(attendance_date) = $1
", [$today]);

// Get top performers
$topPerformers = $db->fetchAll("
    SELECT s.enrollment_number, u.first_name, u.last_name,
           COUNT(*) as total_days,
           SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
           ROUND(100.0 * SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(*), 2) as attendance_percentage
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN attendance a ON s.student_id = a.student_id AND DATE(a.attendance_date) >= NOW() - INTERVAL '30 days'
    WHERE u.is_active = true
    GROUP BY s.student_id, s.enrollment_number, u.first_name, u.last_name
    ORDER BY attendance_percentage DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Student Management System</title>
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
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }
        .stat-card h6 {
            color: #999;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }
        .stat-card.primary {
            border-left-color: #667eea;
        }
        .stat-card.info {
            border-left-color: #17a2b8;
        }
        .stat-card.success {
            border-left-color: #28a745;
        }
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        .report-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .report-card h5 {
            margin-bottom: 20px;
            font-weight: 600;
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
        .quick-link-btn {
            padding: 20px;
            text-align: center;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #333;
            display: block;
            transition: all 0.3s ease;
        }
        .quick-link-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
            text-decoration: none;
            color: #667eea;
        }
        .quick-link-btn i {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
            display: block;
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
        <h1 class="mb-4">
            <i class="fas fa-chart-line"></i> Reports & Analytics
        </h1>

        <!-- Key Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <h6>Total Students</h6>
                    <div class="stat-value"><?php echo $totalStudents; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <h6>Total Users</h6>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <h6>Total Records</h6>
                    <div class="stat-value"><?php echo $totalAttendance; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <h6>Today's Attendance</h6>
                    <div class="stat-value"><?php echo ($todayAttendance['present'] ?? 0) + ($todayAttendance['absent'] ?? 0) + ($todayAttendance['late'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row my-4">
            <div class="col-md-3">
                <a href="<?php echo BASE_URL . '/?action=students'; ?>" class="quick-link-btn">
                    <i class="fas fa-users"></i>
                    <strong>Students</strong>
                    <small>Manage student records</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL . '/?action=mark_attendance'; ?>" class="quick-link-btn">
                    <i class="fas fa-clipboard-check"></i>
                    <strong>Mark Attendance</strong>
                    <small>Record daily attendance</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL . '/?action=attendance_report'; ?>" class="quick-link-btn">
                    <i class="fas fa-file-alt"></i>
                    <strong>Attendance Report</strong>
                    <small>View attendance records</small>
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL . '/?action=dashboard'; ?>" class="quick-link-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    <strong>Dashboard</strong>
                    <small>System overview</small>
                </a>
            </div>
        </div>

        <!-- Today's Summary -->
        <div class="report-card">
            <h5><i class="fas fa-calendar-day"></i> Today's Attendance Summary</h5>
            <div class="row">
                <div class="col-md-3">
                    <h6 class="text-muted">Present</h6>
                    <h3 class="text-success"><?php echo $todayAttendance['present'] ?? 0; ?></h3>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted">Absent</h6>
                    <h3 class="text-danger"><?php echo $todayAttendance['absent'] ?? 0; ?></h3>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted">Late</h6>
                    <h3 class="text-warning"><?php echo $todayAttendance['late'] ?? 0; ?></h3>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted">Total Marked</h6>
                    <h3><?php echo ($todayAttendance['present'] ?? 0) + ($todayAttendance['absent'] ?? 0) + ($todayAttendance['late'] ?? 0); ?></h3>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="report-card">
            <h5><i class="fas fa-star"></i> Top Performers (Last 30 Days)</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Enrollment #</th>
                            <th>Present</th>
                            <th>Total Days</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topPerformers)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No data available</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topPerformers as $perf): ?>
                                <tr>
                                    <td><?php echo ucfirst($perf['first_name']) . ' ' . ucfirst($perf['last_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $perf['enrollment_number']; ?></span></td>
                                    <td><span class="text-success fw-bold"><?php echo $perf['present_days'] ?? 0; ?></span></td>
                                    <td><?php echo $perf['total_days'] ?? 0; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($perf['attendance_percentage'] ?? 0) >= 80 ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $perf['attendance_percentage'] ?? 0; ?>%
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
