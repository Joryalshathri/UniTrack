<?php
/**
 * Student Form Page (Add/Edit)
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
$edit_mode = false;
$student = [
    'student_id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'username' => '',
    'enrollment_number' => '',
    'date_of_birth' => '',
    'phone_number' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'postal_code' => ''
];

if (isset($_GET['id'])) {
    $edit_mode = true;
    $student_id = (int)$_GET['id'];
    $studentObj = new Student();
    $studentData = $studentObj->getStudentById($student_id);
    if ($studentData) {
        $student = $studentData;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'username' => sanitize($_POST['username'] ?? ''),
        'enrollment_number' => sanitize($_POST['enrollment_number'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? '',
        'phone_number' => sanitize($_POST['phone_number'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'state' => sanitize($_POST['state'] ?? ''),
        'postal_code' => sanitize($_POST['postal_code'] ?? '')
    ];

    $studentObj = new Student();
    if ($edit_mode) {
        $result = $studentObj->updateStudent($student['student_id'], $data);
    } else {
        $result = $studentObj->addStudent($data);
    }

    if ($result['success']) {
        redirect(BASE_URL . '/?action=students&success=' . urlencode($edit_mode ? 'Student updated' : 'Student added'));
    } else {
        $error = $result['error'] ?? 'An error occurred';
    }
}

$error = isset($error) ? $error : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit Student' : 'Add Student'; ?> - Student Management System</title>
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
        .form-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-section-title {
            font-weight: 600;
            color: #333;
            margin-top: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
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
                    <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $edit_mode ? 'Edit Student' : 'Add New Student'; ?>
                </h1>
            </div>
        </div>

        <div class="form-card">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Personal Information -->
                <h5 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo $student['first_name']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="last_name" value="<?php echo $student['last_name']; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo $student['email']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone_number" value="<?php echo $student['phone_number']; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth" value="<?php echo $student['date_of_birth']; ?>">
                    </div>
                </div>

                <!-- Account Information -->
                <h5 class="form-section-title"><i class="fas fa-lock"></i> Account Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username <?php echo !$edit_mode ? '*' : ''; ?></label>
                        <input type="text" class="form-control" name="username" value="<?php echo $student['username']; ?>" <?php echo !$edit_mode ? 'required' : 'disabled'; ?>>
                        <?php if ($edit_mode): ?>
                            <small class="text-muted">Username cannot be changed</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Enrollment Number <?php echo !$edit_mode ? '*' : ''; ?></label>
                        <input type="text" class="form-control" name="enrollment_number" value="<?php echo $student['enrollment_number']; ?>" <?php echo !$edit_mode ? 'required' : 'disabled'; ?>>
                        <?php if ($edit_mode): ?>
                            <small class="text-muted">Enrollment number cannot be changed</small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Address Information -->
                <h5 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Address</h5>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address" value="<?php echo $student['address']; ?>">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" value="<?php echo $student['city']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">State/Province</label>
                        <input type="text" class="form-control" name="state" value="<?php echo $student['state']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Postal Code</label>
                        <input type="text" class="form-control" name="postal_code" value="<?php echo $student['postal_code']; ?>">
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_mode ? 'Update' : 'Add'; ?> Student
                    </button>
                    <a href="<?php echo BASE_URL . '/?action=students'; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
