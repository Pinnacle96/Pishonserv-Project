<?php
session_start();
include '../includes/db_connect.php';

// Ensure agent is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['agent', 'owner', 'hotel_owner'])) {
    header("Location: ../auth/login.php");
    exit();
}

$inquiry_id = $_GET['id'] ?? null;
$agent_id = $_SESSION['user_id'];

if (!$inquiry_id) {
    $_SESSION['error'] = "Invalid inquiry selection.";
    header("Location: agent_inquiries.php");
    exit();
}

// Fetch inquiry details
$stmt = $conn->prepare("
    SELECT m.*, u.name AS buyer_name, p.title AS property_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    JOIN properties p ON m.property_id = p.id
    WHERE m.id = ? AND m.receiver_id = ?
");
$stmt->bind_param("ii", $inquiry_id, $agent_id);
$stmt->execute();
$inquiry = $stmt->get_result()->fetch_assoc();

if (!$inquiry) {
    $_SESSION['error'] = "Inquiry not found.";
    header("Location: agent_inquiries.php");
    exit();
}

// Mark as read
if ($inquiry['status'] === 'unread') {
    $update_stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
    $update_stmt->bind_param("i", $inquiry_id);
    $update_stmt->execute();
}

// Load the agent content for all allowed roles
$page_content = __DIR__ . "/view_inquiry_content.php";

// Include the dashboard layout
include 'dashboard_layout.php';
