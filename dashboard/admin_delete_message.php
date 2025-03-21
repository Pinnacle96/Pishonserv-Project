<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

// Ensure only admin or superadmin can delete messages
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    echo json_encode(["success" => false, "error" => "Unauthorized access."]);
    exit();
}

// Validate input
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message_id']) && is_numeric($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);

    // Delete the message
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to delete message."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request."]);
}

exit();
