<?php
/**
 * Logout Handler
 */

$user = new User();
$user->logout();
redirect(BASE_URL . '/pages/login.php');
?>
