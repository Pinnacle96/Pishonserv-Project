<?php
session_start();
include '../includes/db_connect.php';

// Ensure only admin or superadmin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Validate message ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid message ID.";
    header("Location: admin_messages.php");
    exit();
}

$message_id = intval($_GET['id']);

// Fetch message details
$stmt = $conn->prepare("
    SELECT m.*, 
           sender.name AS sender_name, sender.lname AS sender_lname, sender.email AS sender_email,
           receiver.name AS receiver_name, receiver.lname AS receiver_lname, receiver.email AS receiver_email,
           p.title AS property_title
    FROM messages m
    LEFT JOIN users sender ON m.sender_id = sender.id
    LEFT JOIN users receiver ON m.receiver_id = receiver.id
    LEFT JOIN properties p ON m.property_id = p.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();

if (!$message) {
    $_SESSION['error'] = "Message not found.";
    header("Location: admin_messages.php");
    exit();
}

// Mark message as read
$conn->query("UPDATE messages SET status = 'read' WHERE id = $message_id");

$page_content = __DIR__ . "/admin_view_message_content.php";
include 'dashboard_layout.php';
