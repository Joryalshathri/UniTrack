<?php
// UniTrack - Simple Router
session_start();

// Database connection
function getDb() {
    $conn = pg_connect("host=localhost dbname=unitrack_db user=postgres password=postgres");
    return $conn;
}

// Check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Message
function setMessage($msg, $type = 'success') {
    $_SESSION['msg'] = $msg;
    $_SESSION['msg_type'] = $type;
}

function showMessage() {
    if (isset($_SESSION['msg'])) {
        $type = $_SESSION['msg_type'] === 'error' ? 'danger' : 'success';
        echo "<div class='alert alert-$type alert-dismissible'>" . $_SESSION['msg'] . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        unset($_SESSION['msg']);
    }
}

// Check auth
if (!isLoggedIn() && $_GET['action'] !== 'login') {
    redirect('index.php?action=login');
}

$action = $_GET['action'] ?? 'dashboard';
$baseURL = 'index.php?action=';

// Include header HTML
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>UniTrack</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;}.navbar{background:rgba(0,0,0,0.1);}.card{border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,0.2);}</style></head><body>';

if (isLoggedIn()) {
    echo '<nav class="navbar navbar-expand-lg navbar-dark"><div class="container-fluid"><a class="navbar-brand fw-bold" href="'.$baseURL.'dashboard">📚 UniTrack</a><div class="collapse navbar-collapse"><ul class="navbar-nav ms-auto"><li class="nav-item"><a class="nav-link" href="'.$baseURL.'dashboard">Dashboard</a></li><li class="nav-item"><a class="nav-link" href="'.$baseURL.'students">Students</a></li><li class="nav-item"><a class="nav-link" href="'.$baseURL.'mark_attendance">Mark</a></li><li class="nav-item"><a class="nav-link" href="'.$baseURL.'view_attendance">Records</a></li><li class="nav-item"><a class="nav-link" href="'.$baseURL.'logout">Logout</a></li></ul></div></div></nav>';
}

echo '<div class="container mt-4">';
showMessage();

// Route to pages
include match($action) {
    'login' => 'pages/login.php',
    'dashboard' => 'pages/dashboard.php',
    'students' => 'pages/students.php',
    'add_student' => 'pages/add_student.php',
    'edit_student' => 'pages/edit_student.php',
    'delete_student' => 'pages/delete_student.php',
    'mark_attendance' => 'pages/mark_attendance.php',
    'view_attendance' => 'pages/view_attendance.php',
    'logout' => 'pages/logout.php',
    default => 'pages/dashboard.php'
};

echo '</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script></body></html>';
?>
