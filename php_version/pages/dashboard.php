<?php
/**
 * Dashboard Page
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Management System</title>
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
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .stat-card h6 {
            color: #999;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }
        .stat-card .stat-icon {
            font-size: 32px;
            opacity: 0.1;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        .stat-card {
            position: relative;
        }
        .stat-card.primary { border-left: 4px solid #667eea; }
        .stat-card.info { border-left: 4px solid #17a2b8; }
        .stat-card.success { border-left: 4px solid #28a745; }
        .stat-card.warning { border-left: 4px solid #ffc107; }
        .action-buttons {
            margin-top: 30px;
        }
        .action-buttons .btn {
            margin: 10px;
            padding: 10px 20px;
        }
        .quick-links {
            margin-top: 30px;
        }
        .quick-links p {
            color: #999;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .logout-btn {
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
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

        <!-- Navigation Links -->
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
            <i class="fas fa-chart-line"></i> Dashboard
        </h1>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <h6>Total Students</h6>
                    <div class="stat-value">
                        <?php
                        $student = new Student();
                        echo $student->getTotalStudents();
                        ?>
                    </div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <h6>Total Users</h6>
                    <div class="stat-value">
                        <?php
                        $db = Database::getInstance();
                        $result = $db->fetch("SELECT COUNT(*) as count FROM users");
                        echo $result['count'] ?? 0;
                        ?>
                    </div>
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <h6>Total Records</h6>
                    <div class="stat-value">
                        <?php
                        $result = $db->fetch("SELECT COUNT(*) as count FROM attendance");
                        echo $result['count'] ?? 0;
                        ?>
                    </div>
                    <div class="stat-icon"><i class="fas fa-history"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <h6>Today's Attendance</h6>
                    <div class="stat-value">
                        <?php
                        $today = date('Y-m-d');
                        $result = $db->fetch("SELECT COUNT(*) as count FROM attendance WHERE DATE(attendance_date) = $1", [$today]);
                        echo $result['count'] ?? 0;
                        ?>
                    </div>
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4" style="margin-top: 30px;">
            <div class="col-md-12">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="action-buttons">
                    <a href="<?php echo BASE_URL . '/?action=student_form'; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Student
                    </a>
                    <a href="<?php echo BASE_URL . '/?action=mark_attendance'; ?>" class="btn btn-success">
                        <i class="fas fa-clipboard-check"></i> Mark Attendance
                    </a>
                    <a href="<?php echo BASE_URL . '/?action=attendance_report'; ?>" class="btn btn-info">
                        <i class="fas fa-file-alt"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
