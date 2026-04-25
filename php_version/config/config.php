<?php
/**
 * Configuration file for PHP Student Management System
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', 5432);
define('DB_NAME', 'student_management');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'postgres');

// Application Settings
define('APP_NAME', 'Student Management & Attendance System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/php_version');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Security
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('HASH_ALGORITHM', 'bcrypt');

// Environment
define('ENVIRONMENT', 'development'); // development, staging, production

// Error Reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
}

// Logging
define('LOG_PATH', __DIR__ . '/../logs/');
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Required Files
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helpers.php';

// Timezone
date_default_timezone_set('UTC');

// Start Session
session_start();
session_set_cookie_params(['lifetime' => SESSION_TIMEOUT]);
?>
