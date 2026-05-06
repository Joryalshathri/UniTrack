<?php
/**
 * Dashboard Page
 */

require_once 'templates/header.php';

// Check if logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '?action=login');
}

try {
    $db = Database::getInstance();
    
    // Get statistics
    $total_students = $db->fetchOne("SELECT COUNT(*) as count FROM students WHERE user_id IN (SELECT user_id FROM users WHERE is_active = true)");
    $total_users = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    $total_attendance = $db->fetchOne("SELECT COUNT(*) as count FROM attendance");
    
    // Get today's attendance summary
    $today = date('Y-m-d');
    $today_summary = $db->fetchOne(
        "SELECT 
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
        FROM attendance
        WHERE DATE(attendance_date) = $1",
        [$today]
    );
} catch (Exception $e) {
    $error = "Error loading statistics: " . $e->getMessage();
}
?>

<div class="main-content">
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line"></i> Dashboard
                </h1>
                <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-<?php echo $_SESSION['role'] === 'admin' ? 'danger' : ($_SESSION['role'] === 'teacher' ? 'warning' : 'info'); ?>">
                    <?php echo strtoupper($_SESSION['role']); ?>
                </span>
            </div>
        </div>

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
