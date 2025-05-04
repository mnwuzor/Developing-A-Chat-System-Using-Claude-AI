<?php
require_once 'config.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];

// Get all users except the current user
$stmt = $conn->prepare("SELECT id, username, full_name, status, profile_image FROM tbl_users WHERE id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get selected user's information if exists
$selected_user = null;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $chat_user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT id, username, full_name, status, profile_image FROM tbl_users WHERE id = ?");
    $stmt->bind_param("i", $chat_user_id);
    $stmt->execute();
    $selected_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Chat Application</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body data-user-id="<?php echo $user_id; ?>">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="user-info">
                <img src="images/<?php echo htmlspecialchars($_SESSION['profile_image'] ?? 'default.jpg'); ?>" alt="Profile" class="profile-image">
                <h2><?php echo htmlspecialchars($full_name); ?></h2>
                <p>@<?php echo htmlspecialchars($username); ?></p>
            </div>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </header>
        
        <div class="chat-container">
            <div class="users-list">
                <div class="search-container">
                    <input type="text" id="user-search" placeholder="Search users...">
                    <i class="fas fa-search"></i>
                </div>
                
                <ul id="users">
                    <?php foreach ($users as $user): ?>
                        <li class="user-item <?php echo isset($selected_user) && $selected_user['id'] == $user['id'] ? 'active' : ''; ?>" data-user-id="<?php echo $user['id']; ?>">
                            <a href="dashboard.php?user_id=<?php echo $user['id']; ?>">
                                <div class="user-avatar">
                                    <img src="images/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                                    <span class="status-dot <?php echo $user['status'] === 'online' ? 'online' : 'offline'; ?>"></span>
                                </div>
                                <div class="user-details">
                                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                    <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="chat-box">
                <?php if (isset($selected_user)): ?>
                    <div class="chat-header">
                        <div class="selected-user-info">
                            <img src="images/<?php echo htmlspecialchars($selected_user['profile_image']); ?>" alt="<?php echo htmlspecialchars($selected_user['username']); ?>">
                            <div>
                                <h3><?php echo htmlspecialchars($selected_user['full_name']); ?></h3>
                                <p class="status <?php echo $selected_user['status'] === 'online' ? 'online' : 'offline'; ?>">
                                    <?php echo $selected_user['status'] === 'online' ? 'Online' : 'Offline'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <!-- Chat messages will load here via AJAX -->
                    </div>
                    
                    <div class="chat-input">
                        <form id="chat-form">
                            <input type="hidden" id="receiver-id" value="<?php echo $selected_user['id']; ?>">
                            <textarea id="message" placeholder="Type your message..." required></textarea>
                            <button type="submit" id="send-btn"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="no-chat-selected">
                        <i class="fas fa-comments"></i>
                        <h2>Select a user to start chatting</h2>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
