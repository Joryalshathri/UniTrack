<?php
/**
 * Delete Student Handler
 */

if (!isLoggedIn()) {
    redirect(BASE_URL . '/pages/login.php');
}

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$student_id) {
    redirect(BASE_URL . '/?action=students&error=' . urlencode('Invalid student ID'));
}

try {
    $studentObj = new Student();
    $result = $studentObj->deleteStudent($student_id);

    if ($result['success']) {
        redirect(BASE_URL . '/?action=students&success=' . urlencode('Student deleted successfully'));
    } else {
        redirect(BASE_URL . '/?action=students&error=' . urlencode($result['error']));
    }
} catch (Exception $e) {
    redirect(BASE_URL . '/?action=students&error=' . urlencode('An error occurred while deleting the student'));
}
?>
