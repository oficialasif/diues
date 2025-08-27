<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$database = new Database();
$auth = new Auth($database);

// Logout the user
$result = $auth->logout();

// Redirect to login page
header('Location: login.php?message=' . urlencode($result['message']));
exit();
?>
