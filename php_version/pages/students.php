<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$students = [];
try {
    $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
    if ($conn) {
        $result = pg_query($conn, 'SELECT * FROM students ORDER BY student_id DESC');
        $students = pg_fetch_all($result) ?: [];
        pg_close($conn);
    }
} catch (Exception $e) {
    // error
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - UniTrack</title>
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
                    <li class="nav-item"><a class="nav-link active" href="index.php?action=students">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=mark_attendance">Mark</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=view_attendance">Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Students</h2>
                    <a href="index.php?action=add_student" class="btn btn-success">+ Add Student</a>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Enrollment</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['student_id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['enrollment_number']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="index.php?action=edit_student&id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="index.php?action=delete_student&id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
