<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: pages/login.php');
    exit;
}

$student_count = 0;
$attendance_count = 0;

try {
    $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
    if ($conn) {
        $result = pg_query($conn, 'SELECT COUNT(*) as total FROM students');
        $student_count = pg_fetch_assoc($result)['total'];
        
        $result = pg_query($conn, 'SELECT COUNT(*) as total FROM attendance');
        $attendance_count = pg_fetch_assoc($result)['total'];
        
        pg_close($conn);
    }
} catch (Exception $e) {
    // error
}
?>

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UniTrack</title>
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
                    <li class="nav-item"><a class="nav-link active" href="index.php?action=dashboard">Dashboard</a></li>
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
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Students</h5>
                        <h2><?php echo $student_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Attendance Records</h5>
                        <h2><?php echo $attendance_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Welcome</h5>
                        <p><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Student Management</h5>
                        <a href="index.php?action=students" class="btn btn-primary">View Students</a>
                        <a href="index.php?action=add_student" class="btn btn-success">Add Student</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Attendance</h5>
                        <a href="index.php?action=mark_attendance" class="btn btn-primary">Mark Attendance</a>
                        <a href="index.php?action=view_attendance" class="btn btn-info">View Records</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                        <h6 class="card-title mt-3 text-muted">Total Students</h6>
                        <h2 class="text-primary"><?php echo $total_students['count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie text-success" style="font-size: 2rem;"></i>
                        <h6 class="card-title mt-3 text-muted">Total Users</h6>
                        <h2 class="text-success"><?php echo $total_users['count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-check text-info" style="font-size: 2rem;"></i>
                        <h6 class="card-title mt-3 text-muted">Total Records</h6>
                        <h2 class="text-info"><?php echo $total_attendance['count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt text-warning" style="font-size: 2rem;"></i>
                        <h6 class="card-title mt-3 text-muted">Today</h6>
                        <h2 class="text-warning"><?php echo date('d M'); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Attendance Summary -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Today's Attendance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h6 class="text-muted">PRESENT</h6>
                                <h2 class="text-success"><?php echo $today_summary['present'] ?? 0; ?></h2>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted">ABSENT</h6>
                                <h2 class="text-danger"><?php echo $today_summary['absent'] ?? 0; ?></h2>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted">LATE</h6>
                                <h2 class="text-warning"><?php echo $today_summary['late'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-link"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <a href="<?php echo BASE_URL; ?>?action=students" class="text-decoration-none">
                                    <i class="fas fa-list"></i> View Students
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="<?php echo BASE_URL; ?>?action=mark_attendance" class="text-decoration-none">
                                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="<?php echo BASE_URL; ?>?action=attendance_report" class="text-decoration-none">
                                    <i class="fas fa-chart-bar"></i> Attendance Report
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="<?php echo BASE_URL; ?>?action=reports" class="text-decoration-none">
                                    <i class="fas fa-analytics"></i> Analytics
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> System Information</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Application:</dt>
                            <dd class="col-sm-8"><?php echo APP_NAME; ?></dd>
                            
                            <dt class="col-sm-4">Version:</dt>
                            <dd class="col-sm-8"><?php echo APP_VERSION; ?></dd>
                            
                            <dt class="col-sm-4">Environment:</dt>
                            <dd class="col-sm-8"><?php echo ENVIRONMENT; ?></dd>
                            
                            <dt class="col-sm-4">Your Role:</dt>
                            <dd class="col-sm-8"><span class="badge bg-primary"><?php echo strtoupper($_SESSION['role']); ?></span></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
