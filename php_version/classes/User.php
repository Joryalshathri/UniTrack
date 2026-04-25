<?php
/**
 * User Management Class
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Authenticate user
     */
    public function login($username, $password) {
        try {
            $sql = "SELECT user_id, username, email, role, first_name, last_name, password_hash, is_active 
                    FROM users WHERE username = $1";
            $user = $this->db->fetch($sql, [$username]);

            if (!$user) {
                return ['success' => false, 'error' => 'Invalid username or password'];
            }

            if ($user['is_active'] != 't') {
                return ['success' => false, 'error' => 'User account is inactive'];
            }

            if (!verifyPassword($password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Invalid username or password'];
            }

            // Create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            return ['success' => true, 'message' => 'Login successful'];
        } catch (Exception $e) {
            logMessage('Login error: ' . $e->getMessage(), 'error');
            return ['success' => false, 'error' => 'An error occurred during login'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return true;
    }
}
?>
