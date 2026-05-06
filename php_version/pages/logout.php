<?php
/**
 * Logout Handler
 */

session_destroy();
redirect(BASE_URL . '?action=login');
?>
