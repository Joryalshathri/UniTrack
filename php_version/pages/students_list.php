<?php
/**
 * Students List Page
 */

require_once 'templates/header.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '?action=login');
}

try {
    $db = Database::getInstance();
    
    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    // Search
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $search_param = "%$search%";
    
    // Build query
    $where = '';
    $params = [];
    if (!empty($search)) {
        $where = "WHERE (u.first_name ILIKE $1 OR u.last_name ILIKE $1 OR s.enrollment_number ILIKE $1 OR u.email ILIKE $1)";
        $params = [$search_param];
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as count FROM students s JOIN users u ON s.user_id = u.user_id $where";
    $total = $db->fetchOne($count_query, $params);
    $total_pages = ceil($total['count'] / $per_page);
    
    // Get students
    $query = "SELECT s.student_id, s.enrollment_number, u.first_name, u.last_name, u.email, u.is_active
              FROM students s 
              JOIN users u ON s.user_id = u.user_id 
              $where 
              ORDER BY u.first_name, u.last_name
              LIMIT $2 OFFSET $3";
    
    $params[] = $per_page;
    $params[] = $offset;
    
    $students = $db->fetchAll($query, $params);
    
} catch (Exception $e) {
    $error = "Error loading students: " . $e->getMessage();
    $students = [];
}
?>

<div class="main-content">
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users"></i> Students
                </h1>
                <p class="text-muted">Manage all students in the system</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?php echo BASE_URL; ?>?action=student_form" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="action" value="students">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by name, enrollment #, or email..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>?action=students" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Student List
                    <span class="float-end">Total: <strong><?php echo $total['count']; ?></strong></span>
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Enrollment #</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $idx => $student): ?>
                                <tr>
                                    <td><?php echo ($offset + $idx + 1); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($student['enrollment_number']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <?php echo $student['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>?action=student_detail&id=<?php echo $student['student_id']; ?>" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>?action=student_form&id=<?php echo $student['student_id']; ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $student['student_id']; ?>)" 
                                                class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i><br>
                                    No students found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="card-footer">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>?action=students&page=1">First</a>
                        </li>
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>?action=students&page=<?php echo max(1, $page-1); ?>">Previous</a>
                        </li>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                            <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>?action=students&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>?action=students&page=<?php echo min($total_pages, $page+1); ?>">Next</a>
                        </li>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>?action=students&page=<?php echo $total_pages; ?>">Last</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(studentId) {
    if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
        window.location.href = '<?php echo BASE_URL; ?>?action=delete_student&id=' + studentId;
    }
}
</script>

<?php require_once 'templates/footer.php'; ?>
