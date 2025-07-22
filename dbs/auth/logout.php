<?php
// admin/logout.php
// Process admin logout

require_once 'auth.php';

$auth->logout();

// Redirect to login page
header('Location: ../../admin/login.php');
exit;
?>
