<?php
// Load common authentication
require_once 'auth_common.php';

// Logout the user
$result = $auth->logout();

// Redirect to login page
header('Location: login.php?message=' . urlencode($result['message']));
exit();
?>
