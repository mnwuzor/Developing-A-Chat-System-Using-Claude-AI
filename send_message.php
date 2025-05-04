<?php
require_once 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Validate input
if (!isset($_POST['receiver_id']) || !is_numeric($_POST['receiver_id'])) {
    echo json_encode(['error' => 'Invalid receiver']);
    exit();
}

if (!isset($_POST['message']) || empty($_POST['message'])) {
    echo json_encode(['error' => 'Message cannot be empty']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = sanitize_input($_POST['message']);

// Insert message into database
$stmt = $conn->prepare("INSERT INTO tbl_chats (sender, receiver, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    $message_id = $stmt->insert_id;
    
    // Get the message details
    $select_stmt = $conn->prepare("SELECT c.*, u_sender.username as sender_username, u_receiver.username as receiver_username 
                                  FROM tbl_chats c
                                  JOIN tbl_users u_sender ON c.sender = u_sender.id
                                  JOIN tbl_users u_receiver ON c.receiver = u_receiver.id
                                  WHERE c.id = ?");
    $select_stmt->bind_param("i", $message_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $message_data = $result->fetch_assoc();
    $select_stmt->close();
    
    echo json_encode(['success' => true, 'message' => $message_data]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}

$stmt->close();
?>
