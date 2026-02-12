<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// You can add additional checks here if needed
// For example, checking user roles or permissions
?>
