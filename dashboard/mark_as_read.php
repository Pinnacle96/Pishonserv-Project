<?php
session_start();
include '../includes/db_connect.php';

// Ensure agent is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['agent', 'owner', 'hotel_owner'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: agent_messages.php");
    exit();
}

$agent_id = $_SESSION['user_id'];
$message_id = $_GET['id'] ?? null;

if (!$message_id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: agent_messages.php");
    exit();
}

// Update message status to read
$stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE id = ? AND receiver_id = ?");
$stmt->bind_param("ii", $message_id, $agent_id);
$stmt->execute();

$_SESSION['success'] = "Inquiry marked as read.";
header("Location: agent_messages.php");
exit();
