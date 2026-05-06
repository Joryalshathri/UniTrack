<?php
/**
 * Student Detail Page
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$student_id) {
    redirect(BASE_URL . '/?action=students');
}

$studentObj = new Student();
$student = $studentObj->getStudentById($student_id);

if (!$student) {
    redirect(BASE_URL . '/?action=students&error=' . urlencode('Student not found'));
}

// Get attendance summary
$attendance = new Attendance();
$attendanceData = $attendance->getAttendanceSummary(30, $student_id);
$attendanceSummary = $attendanceData[0] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $student['first_name'] . ' ' . $student['last_name']; ?> - Student Details</title>
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
        .detail-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .stat-box.success {
            background: #d4edda;
            color: #155724;
        }
        .stat-box.danger {
            background: #f8d7da;
            color: #721c24;
        }
        .stat-box.warning {
            background: #fff3cd;
            color: #856404;
        }
        .attendance-records {
            max-height: 400px;
            overflow-y: auto;
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
                <h1 class="mb-0">
                    <i class="fas fa-user-graduate"></i> 
                    <?php echo ucfirst($student['first_name']) . ' ' . ucfirst($student['last_name']); ?>
                </h1>
            </div>
            <div class="col-auto">
                <a href="<?php echo BASE_URL . '/?action=student_form&id=' . $student['student_id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="<?php echo BASE_URL . '/?action=students'; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Student Information -->
        <div class="detail-card">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Student Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Enrollment Number</label>
                    <p class="fw-bold"><?php echo $student['enrollment_number']; ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Username</label>
                    <p class="fw-bold"><?php echo $student['username']; ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Email</label>
                    <p class="fw-bold"><?php echo $student['email']; ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Phone Number</label>
                    <p class="fw-bold"><?php echo $student['phone_number'] ?? 'N/A'; ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Date of Birth</label>
                    <p class="fw-bold"><?php echo $student['date_of_birth'] ?? 'N/A'; ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Address</label>
                    <p class="fw-bold"><?php echo ($student['address'] . ', ' . $student['city']) ?? 'N/A'; ?></p>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="detail-card">
            <h5 class="mb-3"><i class="fas fa-calendar-check"></i> Attendance Summary (Last 30 Days)</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-box success">
                        <h6>Present</h6>
                        <h3><?php echo $attendanceSummary['present_days'] ?? 0; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box danger">
                        <h6>Absent</h6>
                        <h3><?php echo $attendanceSummary['absent_days'] ?? 0; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box warning">
                        <h6>Late</h6>
                        <h3><?php echo $attendanceSummary['late_days'] ?? 0; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: #e2e3e5; color: #383d41;">
                        <h6>Percentage</h6>
                        <h3><?php echo $attendanceSummary['attendance_percentage'] ?? 0; ?>%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
