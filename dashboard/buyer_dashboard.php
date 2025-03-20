<?php
session_start();
include '../includes/db_connect.php';

// Ensure buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch unread notifications
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

$page_content = __DIR__ . "/buyer_content.php"; // Use absolute path
include 'dashboard_layout.php';
