<?php
/**
 * Login Handler
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        redirect(BASE_URL . '/pages/login.php?error=' . urlencode('Username and password are required'));
    }

    try {
        $user = new User();
        $result = $user->login($username, $password);

        if ($result['success']) {
            redirect(BASE_URL . '/?action=dashboard');
        } else {
            redirect(BASE_URL . '/pages/login.php?error=' . urlencode($result['error']));
        }
    } catch (Exception $e) {
        redirect(BASE_URL . '/pages/login.php?error=' . urlencode('An error occurred during login'));
    }
} else {
    redirect(BASE_URL . '/pages/login.php');
}
?>
