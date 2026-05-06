<?php
/**
 * Mark Attendance
 * Interface to mark attendance for multiple students
 */

session_start();
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/Helpers.php';

// Check authentication
if (!isLoggedIn()) {
    redirect(BASE_URL . '?action=login');
}

$db = Database::getInstance();
$date_filter = $_GET['date'] ?? date('Y-m-d');
$error = '';
$success = '';

// Fetch all active students
$students_result = pg_query(
    $db->getConnection(),
    'SELECT s.student_id, s.enrollment_number, u.first_name, u.last_name 
     FROM students s 
     JOIN users u ON s.user_id = u.user_id 
     WHERE u.is_active = true
     ORDER BY u.first_name, u.last_name'
);

$students = [];
while ($row = pg_fetch_assoc($students_result)) {
    $students[] = $row;
}

// Fetch existing attendance for the date
$existing_result = pg_query_params(
    $db->getConnection(),
    'SELECT student_id, status, remarks FROM attendance WHERE DATE(attendance_date) = $1',
    [$date_filter]
);

$existing_attendance = [];
while ($row = pg_fetch_assoc($existing_result)) {
    $existing_attendance[$row['student_id']] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_data = $_POST['attendance'] ?? [];
    
    if (empty($attendance_data)) {
        $error = 'Please mark attendance for at least one student';
    } else {
        try {
            $count = 0;
            foreach ($attendance_data as $student_id => $data) {
                $status = $data['status'] ?? null;
                $remarks = trim($data['remarks'] ?? '');
                
                if (empty($status)) {
                    continue;
                }
                
                // Check if attendance already exists for this date
                $check = pg_query_params(
                    $db->getConnection(),
                    'SELECT attendance_id FROM attendance WHERE student_id = $1 AND DATE(attendance_date) = $2',
                    [$student_id, $date_filter]
                );
                
                if (pg_num_rows($check) > 0) {
                    // Update existing record
                    pg_query_params(
                        $db->getConnection(),
                        'UPDATE attendance SET status = $1, remarks = $2, created_at = NOW() 
                         WHERE student_id = $3 AND DATE(attendance_date) = $4',
                        [$status, $remarks, $student_id, $date_filter]
                    );
                } else {
                    // Insert new record
                    pg_query_params(
                        $db->getConnection(),
                        'INSERT INTO attendance (student_id, attendance_date, status, remarks) 
                         VALUES ($1, $2, $3, $4)',
                        [$student_id, $date_filter, $status, $remarks]
                    );
                }
                
                $count++;
            }
            
            $success = "Attendance marked for {$count} student" . ($count !== 1 ? 's' : '') . '!';
            // Refresh existing attendance
            $existing_result = pg_query_params(
                $db->getConnection(),
                'SELECT student_id, status, remarks FROM attendance WHERE DATE(attendance_date) = $1',
                [$date_filter]
            );
            
            $existing_attendance = [];
            while ($row = pg_fetch_assoc($existing_result)) {
                $existing_attendance[$row['student_id']] = $row;
            }
            
        } catch (Exception $e) {
            $error = 'Error saving attendance: ' . $e->getMessage();
        }
    }
}

?>
<?php require_once 'templates/header.php'; ?>

<div class="main-content">
    <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Mark Attendance</h1>
                <p class="text-muted">Record student attendance for a specific date</p>
            </div>
            <a href="<?php echo BASE_URL; ?>?action=attendance_report" class="btn btn-info">
                <i class="fas fa-list"></i> View Report
            </a>
        </div>

        <!-- Error/Success Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Attendance Form -->
        <form method="POST" class="needs-validation" novalidate>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar"></i> Attendance for <?php echo date('M d, Y', strtotime($date_filter)); ?></h5>
                </div>
                <div class="card-body">
                    <!-- Date Filter -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="date_filter" class="form-label">Select Date:</label>
                            <input type="date" id="date_filter" class="form-control" value="<?php echo $date_filter; ?>" 
                                   onchange="window.location = window.location.href.split('?')[0] + '?action=mark_attendance&date=' + this.value;">
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 15%">Enrollment</th>
                                    <th style="width: 30%">Name</th>
                                    <th style="width: 20%">Status</th>
                                    <th style="width: 30%">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($students as $index => $student):
                                    $existing = $existing_attendance[$student['student_id']] ?? null;
                                    $status = $existing['status'] ?? '';
                                    $remarks = $existing['remarks'] ?? '';
                                ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['enrollment_number']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </td>
                                        <td>
                                            <select name="attendance[<?php echo $student['student_id']; ?>][status]" 
                                                    class="form-select form-select-sm" aria-label="Attendance status">
                                                <option value="">— Select —</option>
                                                <option value="present" <?php echo $status === 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo $status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="late" <?php echo $status === 'late' ? 'selected' : ''; ?>>Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[<?php echo $student['student_id']; ?>][remarks]" 
                                                   class="form-control form-control-sm" placeholder="Reason or notes..."
                                                   value="<?php echo htmlspecialchars($remarks); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex gap-2 justify-content-end">
                <a href="<?php echo BASE_URL; ?>?action=dashboard" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </form>

        <!-- Summary Card -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Summary</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            <small>Total Active Students: <strong><?php echo count($students); ?></strong></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php require_once 'templates/footer.php'; ?>
