<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Get user data
$stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if email exists for another user
    $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $email_result = $stmt->get_result();
    
    if ($email_result->num_rows > 0) {
        $error = "Email is already in use by another account";
    } else {
        // Update user info
        $query = "UPDATE tbl_users SET fullname = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $fullname, $email, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['fullname'] = $fullname;
            $success = "Profile updated successfully";
            
            // Update password if provided
            if (!empty($current_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error = "New passwords do not match";
                } else {
                    // In a real application, use password_verify and password_hash
                    // For this demo, we'll just update the password
                    $hashed_password = $new_password; // Simplified for demo
                    
                    $stmt = $conn->prepare("UPDATE tbl_users SET password = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success .= " and password changed";
                    } else {
                        $error = "Failed to update password";
                    }
                }
            }
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Failed to update profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body data-user-id="<?php echo $user_id; ?>">
    <div class="container">
        <div class="login-container" style="max-width: 500px;">
            <div class="login-header">
                <h2><i class="fas fa-user-cog"></i> User Profile</h2>
                <p>Update your account information</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" value="<?php echo $user['username']; ?>" disabled>
                    <small>Username cannot be changed</small>
                </div>
                <div class="form-group">
                    <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo $user['fullname']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <hr style="margin: 20px 0;">
                <p><strong>Change Password</strong> (leave blank to keep current password)</p>
                <div class="form-group">
                    <label for="current_password"><i class="fas fa-lock"></i> Current Password</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                <div class="form-group">
                    <label for="new_password"><i class="fas fa-key"></i> New Password</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
                <div class="text-center">
                    <a href="dashboard.php" class="btn btn-sm">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
