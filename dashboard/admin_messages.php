<?php
session_start();
include '../includes/db_connect.php';

// Ensure only admin or superadmin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all messages
$messages = $conn->query("
    SELECT m.*, 
           sender.name AS sender_name, sender.lname AS sender_lname, sender.email AS sender_email,
           receiver.name AS receiver_name, receiver.lname AS receiver_lname, receiver.email AS receiver_email,
           p.title AS property_title
    FROM messages m
    LEFT JOIN users sender ON m.sender_id = sender.id
    LEFT JOIN users receiver ON m.receiver_id = receiver.id
    LEFT JOIN properties p ON m.property_id = p.id
    ORDER BY m.created_at DESC
");

$page_content = __DIR__ . "/admin_messages_content.php";
include 'dashboard_layout.php';
