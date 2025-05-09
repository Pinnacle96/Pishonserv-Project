<?php
session_start();
include '../includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Define allowed roles
$allowed_roles = ['agent', 'owner', 'hotel_owner', 'developer'];

// Check if the user's role is allowed
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../auth/unauthorized.php"); // Redirect to an unauthorized page
    exit();
}

// Fetch messages where the user is the receiver
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$stmt = $conn->prepare("SELECT m.id, m.message, m.created_at, u.name AS sender_name, m.sender_role, m.status 
                        FROM messages m 
                        JOIN users u ON m.sender_id = u.id 
                        WHERE m.receiver_id = ? AND m.receiver_role = ? 
                        ORDER BY m.created_at DESC");
$stmt->bind_param("is", $user_id, $user_role);
$stmt->execute();
$result = $stmt->get_result();

// Load the agent messages content for all allowed roles
$page_content = __DIR__ . "/agent_messages_content.php";
include 'dashboard_layout.php';
?>