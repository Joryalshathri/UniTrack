<?php
/**
 * Students List - Procedural Approach
 * Demonstrates procedural pattern from documentation
 * Display all students using direct Database object
 */

require_once '../config/config.php';
require_once '../config/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get database instance
$db = Database::getInstance();

// Retrieve students from database
$sql = "SELECT s.student_id, s.enrollment_number, s.phone_number,
               u.first_name, u.last_name, u.email
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE u.is_active = true
        ORDER BY u.first_name, u.last_name";

$students = $db->fetchAll($sql, []);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List - Procedural Approach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Students List</h1>
        <p class="text-muted">Procedural approach example from documentation</p>
        
        <?php if (empty($students)): ?>
            <div class="alert alert-info">No students found.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($students as $student): ?>
                    <div class="list-group-item">
                        <h5 class="mb-1">
                            <?php echo htmlspecialchars($student['enrollment_number']); ?> - 
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </h5>
                        <p class="mb-1">
                            <small>Email: <?php echo htmlspecialchars($student['email']); ?></small>
                        </p>
                        <p class="mb-0">
                            <small>Phone: <?php echo htmlspecialchars($student['phone_number'] ?? 'N/A'); ?></small>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <hr>
        
        <!-- Simple text output version (from documentation) -->
        <h2 class="mt-5">Simple Output Version</h2>
        <div class="alert alert-light border">
            <?php foreach ($students as $student): ?>
                <?php echo htmlspecialchars($student['enrollment_number']) . ' - ' .
                     htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?><br>
            <?php endforeach; ?>
        </div>
        
        <a href="students.php" class="btn btn-secondary mt-3">Back to OOP Version</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
