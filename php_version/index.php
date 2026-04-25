<?php
/**
 * Index/Router for PHP Application
 */

require_once __DIR__ . '/config/config.php';

// Check if user is logged in, redirect to login
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    $action = $_GET['action'] ?? 'login';
    if ($action !== 'login' && $action !== 'do_login') {
        redirect(BASE_URL . '/pages/login.php');
    }
}

// Route requests
$action = $_GET['action'] ?? 'dashboard';

// Load appropriate page
switch ($action) {
    case 'login':
        require 'pages/login.php';
        break;
    case 'do_login':
        require 'pages/login_handler.php';
        break;
    case 'logout':
        require 'pages/logout.php';
        break;
    case 'dashboard':
        require 'pages/dashboard.php';
        break;
    case 'students':
        require 'pages/students_list.php';
        break;
    case 'student_detail':
        require 'pages/student_detail.php';
        break;
    case 'student_form':
        require 'pages/student_form.php';
        break;
    case 'mark_attendance':
        require 'pages/mark_attendance.php';
        break;
    case 'attendance_report':
        require 'pages/attendance_report.php';
        break;
    case 'reports':
        require 'pages/reports_dashboard.php';
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
?>
