<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$records = [];
$students = [];
$student_id = $_GET['student_id'] ?? '';

try {
    $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
    if ($conn) {
        $query = 'SELECT a.attendance_id, a.attendance_date, a.status, a.remarks, s.first_name, s.last_name FROM attendance a JOIN students s ON a.student_id = s.student_id';
        $params = [];
        
        if ($student_id) {
            $query .= ' WHERE a.student_id = $1';
            $params[] = $student_id;
        }
        
        $query .= ' ORDER BY a.attendance_date DESC';
        
        $result = pg_query_params($conn, $query, $params);
        $records = pg_fetch_all($result) ?: [];
        
        $result = pg_query($conn, 'SELECT * FROM students ORDER BY first_name');
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
    <title>Attendance Records - UniTrack</title>
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
                    <li class="nav-item"><a class="nav-link active" href="index.php?action=view_attendance">Records</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-12">
                <h2>Attendance Records</h2>
                <div class="card mt-3">
                    <div class="card-body">
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Filter by Student:</label>
                                    <select class="form-select" name="student_id" onchange="this.form.submit();">
                                        <option value="">All Students</option>
                                        <?php foreach ($students as $s): ?>
                                        <option value="<?php echo $s['student_id']; ?>" <?php echo $student_id == $s['student_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['attendance_date']); ?></td>
                                        <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                        <td>
                                            <span class="badge <?php
                                                echo $record['status'] === 'present' ? 'bg-success' : 
                                                    ($record['status'] === 'absent' ? 'bg-danger' : 'bg-warning');
                                            ?>">
                                                <?php echo strtoupper($record['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($records) === 0): ?>
                            <p class="text-center text-muted mt-3">No attendance records found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
