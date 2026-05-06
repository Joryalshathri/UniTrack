<?php
/**
 * Students List Page
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$currentUser = getCurrentUser();
$student = new Student();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$per_page = 10;

$students = $student->getAllStudents($page, $per_page, $search);
$total = $student->getTotalStudents($search);
$total_pages = ceil($total / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Management System</title>
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
        .table-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .table th {
            border: none;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .table td {
            border-color: #e9ecef;
            vertical-align: middle;
        }
        .action-buttons-row {
            display: flex;
            gap: 5px;
        }
        .action-buttons-row .btn {
            padding: 5px 10px;
            font-size: 12px;
        }
        .pagination {
            margin-top: 20px;
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
            <div class="col-md-8">
                <h1 class="mb-0"><i class="fas fa-users"></i> Students Management</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?php echo BASE_URL . '/?action=student_form'; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
        </div>

        <div class="table-card">
            <!-- Search Box -->
            <div class="search-box">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="action" value="students">
                    <input type="text" class="form-control" name="search" placeholder="Search students..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($search): ?>
                        <a href="<?php echo BASE_URL . '/?action=students'; ?>" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Enrollment #</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No students found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $stud): ?>
                                <tr>
                                    <td><strong><?php echo ucfirst($stud['first_name']) . ' ' . ucfirst($stud['last_name']); ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo $stud['enrollment_number']; ?></span></td>
                                    <td><small><?php echo $stud['email']; ?></small></td>
                                    <td><?php echo $stud['phone_number'] ?? '-'; ?></td>
                                    <td>
                                        <div class="action-buttons-row">
                                            <a href="<?php echo BASE_URL . '/?action=student_detail&id=' . $stud['student_id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="<?php echo BASE_URL . '/?action=student_form&id=' . $stud['student_id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo BASE_URL . '/?action=delete_student&id=' . $stud['student_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL . '/?action=students&page=1' . ($search ? '&search=' . urlencode($search) : ''); ?>">First</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL . '/?action=students&page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : ''); ?>">Prev</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL . '/?action=students&page=' . $i . ($search ? '&search=' . urlencode($search) : ''); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL . '/?action=students&page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : ''); ?>">Next</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL . '/?action=students&page=' . $total_pages . ($search ? '&search=' . urlencode($search) : ''); ?>">Last</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
