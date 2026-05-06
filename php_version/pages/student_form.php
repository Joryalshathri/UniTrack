<?php
/**
 * Student Form - Add/Edit Student
 * Handles both adding new students and editing existing student information
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
$mode = $student_id ? 'edit' : 'add';
$error = '';
$success = '';

// Fetch student data for edit mode
$student = null;
$user = null;
if ($mode === 'edit' && $student_id) {
    $result = pg_query_params(
        $db->getConnection(),
        'SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.is_active 
         FROM students s 
         JOIN users u ON s.user_id = u.user_id 
         WHERE s.student_id = $1',
        [$student_id]
    );
    
    if (pg_num_rows($result) > 0) {
        $student = pg_fetch_assoc($result);
    } else {
        $error = 'Student not found';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $enrollment_number = trim($_POST['enrollment_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    
    // Validation
    if (empty($first_name)) {
        $error = 'First name is required';
    } elseif (empty($last_name)) {
        $error = 'Last name is required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required';
    } elseif (empty($enrollment_number)) {
        $error = 'Enrollment number is required';
    } elseif (empty($phone) || !preg_match('/^\+?[\d\s\-\(\)]{10,}$/', $phone)) {
        $error = 'Valid phone number is required';
    } elseif ($mode === 'add') {
        // Check if enrollment number already exists
        $check = pg_query_params(
            $db->getConnection(),
            'SELECT enrollment_number FROM students WHERE enrollment_number = $1',
            [$enrollment_number]
        );
        
        if (pg_num_rows($check) > 0) {
            $error = 'Enrollment number already exists';
        }
    }
    
    if (empty($error)) {
        try {
            if ($mode === 'add') {
                // Create new user account
                $password = $enrollment_number; // Auto-generate from enrollment number
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
                
                $user_result = pg_query_params(
                    $db->getConnection(),
                    'INSERT INTO users (username, password_hash, role, first_name, last_name, email, phone, is_active) 
                     VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING user_id',
                    [$enrollment_number, $password_hash, 'student', $first_name, $last_name, $email, $phone, true]
                );
                
                if (pg_num_rows($user_result) > 0) {
                    $user_id = pg_fetch_assoc($user_result)['user_id'];
                    
                    // Insert student record
                    pg_query_params(
                        $db->getConnection(),
                        'INSERT INTO students (user_id, enrollment_number, address, city, state, postal_code) 
                         VALUES ($1, $2, $3, $4, $5, $6)',
                        [$user_id, $enrollment_number, $address, $city, $state, $postal_code]
                    );
                    
                    $success = "Student '{$first_name} {$last_name}' added successfully!";
                    // Redirect after 2 seconds
                    header('Refresh: 2; url=' . BASE_URL . '?action=students_list');
                } else {
                    $error = 'Failed to create user account';
                }
                
            } else {
                // Update existing student
                pg_query_params(
                    $db->getConnection(),
                    'UPDATE users SET first_name = $1, last_name = $2, email = $3, phone = $4, is_active = $5 
                     WHERE user_id = $6',
                    [$first_name, $last_name, $email, $phone, true, $student['user_id']]
                );
                
                pg_query_params(
                    $db->getConnection(),
                    'UPDATE students SET address = $1, city = $2, state = $3, postal_code = $4 
                     WHERE student_id = $5',
                    [$address, $city, $state, $postal_code, $student_id]
                );
                
                $success = "Student information updated successfully!";
                // Redirect after 2 seconds
                header('Refresh: 2; url=' . BASE_URL . '?action=student_detail&id=' . $student_id);
            }
            
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
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
                <h1><?php echo $mode === 'add' ? 'Add New Student' : 'Edit Student'; ?></h1>
                <p class="text-muted">Fill in the student information below</p>
            </div>
            <a href="<?php echo BASE_URL; ?>?action=students_list" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
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

        <!-- Form Card -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $mode === 'add' ? 'plus-circle' : 'edit'; ?>"></i>
                            <?php echo $mode === 'add' ? 'New Student Information' : 'Update Student Information'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Personal Information Section -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-3">Personal Information</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">First name is required</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($student['last_name'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">Last name is required</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="enrollment_number" class="form-label">Enrollment Number *</label>
                                        <input type="text" class="form-control" id="enrollment_number" name="enrollment_number" 
                                               value="<?php echo htmlspecialchars($student['enrollment_number'] ?? ''); ?>" 
                                               <?php echo $mode === 'edit' ? 'disabled' : ''; ?> required>
                                        <small class="text-muted">
                                            <?php echo $mode === 'add' ? '(Used to generate initial password)' : '(Cannot be changed)'; ?>
                                        </small>
                                        <div class="invalid-feedback">Enrollment number is required</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">Valid email is required</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" 
                                           placeholder="+1 (555) 000-0000" required>
                                    <div class="invalid-feedback">Valid phone number is required</div>
                                </div>
                            </div>

                            <!-- Address Information Section -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-3">Address Information</h6>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>" 
                                           placeholder="Street address">
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo htmlspecialchars($student['city'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">State/Province</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo htmlspecialchars($student['state'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                               value="<?php echo htmlspecialchars($student['postal_code'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?php echo BASE_URL; ?>?action=students_list" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $mode === 'add' ? 'Add Student' : 'Update Student'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($mode === 'add'): ?>
                            <p><strong>Adding New Student</strong></p>
                            <ul class="small">
                                <li>Fill in all required fields marked with *</li>
                                <li>Initial password will be the enrollment number</li>
                                <li>Student can change password after first login</li>
                                <li>Email must be unique and valid</li>
                                <li>Phone number should include country/area code</li>
                            </ul>
                        <?php else: ?>
                            <p><strong>Editing Student</strong></p>
                            <ul class="small">
                                <li>Enrollment number cannot be changed</li>
                                <li>Update any information as needed</li>
                                <li>Changes are saved immediately</li>
                                <li>To change password, use admin panel</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Required Fields Legend -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-2"><small><strong>Field Legend:</strong></small></p>
                        <p class="text-muted"><small><span class="text-danger">*</span> = Required field</small></p>
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
