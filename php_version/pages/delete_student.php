<?php
/**
 * Delete Student
 * Handles student deletion from database
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
$student_id = $_GET['id'] ?? null;
$action = $_POST['confirm'] ?? null;

if (!$student_id) {
    redirect(BASE_URL . '?action=students_list');
}

// Fetch student info
$result = pg_query_params(
    $db->getConnection(),
    'SELECT s.student_id, s.user_id, u.first_name, u.last_name FROM students s 
     JOIN users u ON s.user_id = u.user_id WHERE s.student_id = $1',
    [$student_id]
);

if (pg_num_rows($result) === 0) {
    redirect(BASE_URL . '?action=students_list');
}

$student = pg_fetch_assoc($result);

// Handle deletion
if ($action === 'yes') {
    try {
        // Delete attendance records first (due to FK)
        pg_query_params(
            $db->getConnection(),
            'DELETE FROM attendance WHERE student_id = $1',
            [$student_id]
        );

        // Delete student record
        pg_query_params(
            $db->getConnection(),
            'DELETE FROM students WHERE student_id = $1',
            [$student_id]
        );

        // Delete user record (CASCADE will handle it, but explicit for clarity)
        pg_query_params(
            $db->getConnection(),
            'DELETE FROM users WHERE user_id = $1',
            [$student['user_id']]
        );

        // Redirect with success message
        $_SESSION['message'] = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . ' has been deleted successfully!';
        $_SESSION['message_type'] = 'success';
        redirect(BASE_URL . '?action=students_list');
        
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error deleting student: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        redirect(BASE_URL . '?action=students_list');
    }
} elseif ($action === 'no') {
    redirect(BASE_URL . '?action=student_detail&id=' . $student_id);
}

?>
<?php require_once 'templates/header.php'; ?>

<div class="main-content">
    <div class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-trash"></i> Delete Student</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning!</strong> This action cannot be undone.
                        </div>

                        <p class="mb-4">
                            Are you sure you want to delete <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>?
                        </p>

                        <p class="text-muted mb-4">
                            <small>
                                This will permanently remove:
                                <ul>
                                    <li>Student record</li>
                                    <li>User account</li>
                                    <li>All attendance records</li>
                                </ul>
                            </small>
                        </p>

                        <form method="POST" class="d-flex gap-2 justify-content-center">
                            <button type="submit" name="confirm" value="no" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete Permanently
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
