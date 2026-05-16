<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$student = null;
$student_id = $_GET['id'] ?? null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollment = trim($_POST['enrollment_number'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['date_of_birth'] ?? '');
    
    if (!$enrollment || !$first_name || !$last_name || !$email) {
        $error = 'All fields required';
    } elseif (strpos($email, '@') === false) {
        $error = 'Invalid email';
    } else {
        try {
            $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
            if ($conn) {
                if ($student_id) {
                    pg_query_params($conn,
                        'UPDATE students SET first_name = $1, last_name = $2, email = $3, phone = $4, date_of_birth = $5 WHERE student_id = $6',
                        [$first_name, $last_name, $email, $phone ?: null, $dob ?: null, $student_id]
                    );
                    $success = 'Student updated!';
                } else {
                    pg_query_params($conn,
                        'INSERT INTO students (enrollment_number, first_name, last_name, email, phone, date_of_birth) VALUES ($1, $2, $3, $4, $5, $6)',
                        [$enrollment, $first_name, $last_name, $email, $phone ?: null, $dob ?: null]
                    );
                    $success = 'Student added!';
                }
                pg_close($conn);
                header('Location: index.php?action=students');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

if ($student_id) {
    try {
        $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
        if ($conn) {
            $result = pg_query_params($conn, 'SELECT * FROM students WHERE student_id = $1', [$student_id]);
            $student = pg_fetch_assoc($result);
            pg_close($conn);
        }
    } catch (Exception $e) {
        $error = 'Error loading student';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $student_id ? 'Edit' : 'Add'; ?> Student - UniTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .navbar { background: rgba(0,0,0,0.1); }
        .container { margin-top: 20px; }
        .card { border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php?action=dashboard">📚 UniTrack</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?action=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=students">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=mark_attendance">Mark</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=view_attendance">Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-6 offset-md-3">
                <h2><?php echo $student ? 'Edit Student' : 'Add New Student'; ?></h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Enrollment Number *</label>
                                <input type="text" class="form-control" name="enrollment_number" value="<?php echo $student ? htmlspecialchars($student['enrollment_number']) : ''; ?>" <?php echo $student ? 'readonly' : 'required'; ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo $student ? htmlspecialchars($student['first_name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo $student ? htmlspecialchars($student['last_name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $student ? htmlspecialchars($student['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo $student ? htmlspecialchars($student['phone']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?php echo $student ? htmlspecialchars($student['date_of_birth']) : ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo $student ? 'Update' : 'Create'; ?></button>
                            <a href="index.php?action=students" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
