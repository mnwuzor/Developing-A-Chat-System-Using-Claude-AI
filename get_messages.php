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

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

// Get the last timestamp if provided
$last_timestamp = isset($_POST['last_timestamp']) ? $_POST['last_timestamp'] : null;

// Fetch messages
$query = "SELECT c.*, u_sender.username as sender_username, u_receiver.username as receiver_username 
          FROM tbl_chats c
          JOIN tbl_users u_sender ON c.sender = u_sender.id
          JOIN tbl_users u_receiver ON c.receiver = u_receiver.id
          WHERE (c.sender = ? AND c.receiver = ?) OR (c.sender = ? AND c.receiver = ?)";

// Add timestamp condition if last_timestamp is provided
if ($last_timestamp) {
    $query .= " AND c.sent_at > ?";
}

$query .= " ORDER BY c.sent_at ASC";

$stmt = $conn->prepare($query);

if ($last_timestamp) {
    $stmt->bind_param("iiiss", $sender_id, $receiver_id, $receiver_id, $sender_id, $last_timestamp);
} else {
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
}

$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark messages as read
$update_stmt = $conn->prepare("UPDATE tbl_chats SET is_read = TRUE WHERE sender = ? AND receiver = ? AND is_read = FALSE");
$update_stmt->bind_param("ii", $receiver_id, $sender_id);
$update_stmt->execute();
$update_stmt->close();

echo json_encode(['messages' => $messages]);
?>
