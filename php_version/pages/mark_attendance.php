<?php
/**
 * Mark Attendance Page
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
$attendance = new Attendance();
$students = $attendance->getActiveStudents();
$error = null;
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $records = [];
    foreach ($_POST['attendance'] as $student_id => $status) {
        if ($status) {
            $records[] = [
                'student_id' => (int)$student_id,
                'status' => $status,
                'remarks' => sanitize($_POST['remarks'][$student_id] ?? '')
            ];
        }
    }

    if (!empty($records)) {
        $result = $attendance->markAttendance($records);
        if ($result['success']) {
            $success = $result['saved_count'] . ' attendance records marked successfully';
        } else {
            $error = $result['error'];
        }
    } else {
        $error = 'Please mark attendance for at least one student';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Student Management System</title>
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
        .attendance-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .attendance-table {
            margin-top: 20px;
        }
        .attendance-table table {
            margin-bottom: 0;
        }
        .attendance-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            border: none;
        }
        .attendance-table td {
            vertical-align: middle;
            border-color: #e9ecef;
        }
        .status-select {
            border: 1px solid #ddd;
            padding: 8px 12px;
            border-radius: 4px;
        }
        .remarks-input {
            border: 1px solid #ddd;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            width: 100%;
        }
        .select-all-box {
            margin-bottom: 15px;
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
                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                </h1>
                <p class="text-muted">Date: <?php echo date('F d, Y'); ?></p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="attendance-card">
            <form method="POST">
                <div class="select-all-box">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label class="form-check-label" for="selectAll">
                            Mark All as Present
                        </label>
                    </div>
                </div>

                <div class="attendance-table">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Enrollment #</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $index = 1; foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo $index++; ?></td>
                                        <td><?php echo ucfirst($student['first_name']) . ' ' . ucfirst($student['last_name']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $student['enrollment_number']; ?></span></td>
                                        <td>
                                            <select name="attendance[<?php echo $student['student_id']; ?>]" class="status-select attendance-status">
                                                <option value="">-- Select --</option>
                                                <option value="present">Present</option>
                                                <option value="absent">Absent</option>
                                                <option value="late">Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="remarks[<?php echo $student['student_id']; ?>]" class="remarks-input" placeholder="Add remarks (optional)">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Attendance
                    </button>
                    <a href="<?php echo BASE_URL . '/?action=dashboard'; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll').checked;
            const selects = document.querySelectorAll('.attendance-status');
            selects.forEach(select => {
                select.value = selectAll ? 'present' : '';
            });
        }
    </script>
</body>
</html>
