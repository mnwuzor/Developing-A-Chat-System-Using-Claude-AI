<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];

// Get list of users to chat with
$stmt = $conn->prepare("
    SELECT user_id, username, fullname, status, last_login, profile_pic
    FROM tbl_users 
    WHERE user_id != ?
    ORDER BY status = 'online' DESC, last_login DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users_result = $stmt->get_result();

// Get unread message counts
$unread_counts = [];
$stmt = $conn->prepare("
    SELECT sender, COUNT(*) as count
    FROM tbl_chats
    WHERE receiver = ? AND is_read = FALSE
    GROUP BY sender
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_result = $stmt->get_result();

while ($row = $unread_result->fetch_assoc()) {
    $unread_counts[$row['sender']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body data-user-id="<?php echo $user_id; ?>">
    <div class="chat-container">
        <!-- Sidebar with user list -->
        <div class="chat-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-comments"></i> Chat App</h3>
                <div class="user-info">
                    <div class="user-avatar">
                        <img src="images/default.jpg" alt="Profile Picture">
                        <span class="status-dot online"></span>
                    </div>
                    <div class="user-details">
                        <h4><?php echo $fullname; ?></h4>
                        <p>@<?php echo $username; ?></p>
                    </div>
                </div>
                <button class="mobile-menu-btn" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-search">
                <div class="input-group">
                    <input type="text" placeholder="Search users..." id="user-search">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="user-list">
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <div class="user-item" data-user-id="<?php echo $user['user_id']; ?>">
                        <div class="user-avatar">
                            <img src="images/<?php echo $user['profile_pic']; ?>" alt="<?php echo $user['fullname']; ?>">
                            <span class="status-dot <?php echo $user['status']; ?>"></span>
                        </div>
                        <div class="user-info">
                            <h4><?php echo $user['fullname']; ?></h4>
                            <p>@<?php echo $user['username']; ?></p>
                        </div>
                        <?php if (isset($unread_counts[$user['user_id']]) && $unread_counts[$user['user_id']] > 0): ?>
                            <div class="unread-badge"><?php echo $unread_counts[$user['user_id']]; ?></div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="sidebar-footer">
                <a href="profile.php" class="btn btn-sm"><i class="fas fa-user-cog"></i> Profile</a>
                <a href="logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main chat area -->
        <div class="chat-main">
            <div class="chat-header">
                <div id="selected-user-info">
                    <div class="user-avatar">
                        <img src="images/default.jpg" alt="Select a user">
                        <span class="status-dot offline"></span>
                    </div>
                    <div class="user-details">
                        <h4>Select a user to chat</h4>
                        <p class="typing-status"></p>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="btn btn-sm" id="refresh-btn"><i class="fas fa-sync-alt"></i></button>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <div class="no-chat-selected">
                    <i class="fas fa-comments fa-4x"></i>
                    <p>Select a user to start chatting</p>
                </div>
            </div>
            
            <div class="chat-input-container">
                <form id="chat-form" class="hidden">
                    <div class="input-group">
                        <input type="text" id="message-input" placeholder="Type a message..." disabled>
                        <button type="submit" id="send-btn" disabled>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>
