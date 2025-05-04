<?php
require_once 'config.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Update user status to offline
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE tbl_users SET status = 'offline' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: login.php");
exit();
?>
