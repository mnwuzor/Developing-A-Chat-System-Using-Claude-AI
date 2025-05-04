<?php
require_once 'config.php';

// Update user status to offline before logging out
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE tbl_users SET status = 'offline' WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
