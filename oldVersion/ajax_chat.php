<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get_messages':
        $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        
        if ($receiver_id <= 0) {
            echo json_encode(['error' => 'Invalid receiver']);
            exit();
        }
        
        // Get receiver info
        $stmt = $conn->prepare("SELECT username, fullname, status FROM tbl_users WHERE user_id = ?");
        $stmt->bind_param("i", $receiver_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $receiver_info = $result->fetch_assoc();
        
        // Get messages
        $stmt = $conn->prepare("
            SELECT c.chat_id, c.sender, c.receiver, c.message, c.is_read, c.sent_at,
                   s.username as sender_username, s.fullname as sender_fullname,
                   r.username as receiver_username, r.fullname as receiver_fullname
            FROM tbl_chats c
            JOIN tbl_users s ON c.sender = s.user_id
            JOIN tbl_users r ON c.receiver = r.user_id
            WHERE (c.sender = ? AND c.receiver = ?) OR (c.sender = ? AND c.receiver = ?)
            ORDER BY c.sent_at ASC
        ");
        $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
        $stmt->execute();
        $messages_result = $stmt->get_result();
        
        $messages = [];
        while ($row = $messages_result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        // Mark messages as read
        $stmt = $conn->prepare("
            UPDATE tbl_chats
            SET is_read = TRUE
            WHERE sender = ? AND receiver = ? AND is_read = FALSE
        ");
        $stmt->bind_param("ii", $receiver_id, $user_id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'receiver' => $receiver_info,
            'messages' => $messages
        ]);
        break;
        
    case 'send_message':
        $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
        
        if ($receiver_id <= 0 || empty($message)) {
            echo json_encode(['error' => 'Invalid receiver or empty message']);
            exit();
        }
        
        // Insert message
        $stmt = $conn->prepare("
            INSERT INTO tbl_chats (sender, receiver, message)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            $new_message_id = $stmt->insert_id;
            
            // Get the inserted message with user details
            $stmt = $conn->prepare("
                SELECT c.chat_id, c.sender, c.receiver, c.message, c.is_read, c.sent_at,
                       s.username as sender_username, s.fullname as sender_fullname,
                       r.username as receiver_username, r.fullname as receiver_fullname
                FROM tbl_chats c
                JOIN tbl_users s ON c.sender = s.user_id
                JOIN tbl_users r ON c.receiver = r.user_id
                WHERE c.chat_id = ?
            ");
            $stmt->bind_param("i", $new_message_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $message_data = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => $message_data
            ]);
        } else {
            echo json_encode(['error' => 'Failed to send message']);
        }
        break;
        
    case 'get_users':
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
        
        $users = [];
        while ($row = $users_result->fetch_assoc()) {
            $users[] = $row;
        }
        
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
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'unread_counts' => $unread_counts
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
