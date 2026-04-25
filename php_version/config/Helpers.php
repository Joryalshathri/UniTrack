<?php
/**
 * Helper Functions
 */

/**
 * Hash a password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'email' => $_SESSION['email']
        ];
    }
    return null;
}

/**
 * Check if user has required role
 */
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    $userRole = $_SESSION['role'];
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    return $userRole === $roles;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Convert date format
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Log message
 */
function logMessage($message, $type = 'info') {
    $logFile = LOG_PATH . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Get flash message
 */
function getFlash($key) {
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

/**
 * Set flash message
 */
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Calculate attendance percentage
 */
function calculateAttendancePercentage($present, $total) {
    if ($total == 0) return 0;
    return round(($present / $total) * 100, 2);
}

/**
 * Get badge class based on value
 */
function getBadgeClass($value, $thresholds = ['success' => 80, 'warning' => 50]) {
    if ($value >= $thresholds['success']) return 'success';
    if ($value >= $thresholds['warning']) return 'warning';
    return 'danger';
}
?>
