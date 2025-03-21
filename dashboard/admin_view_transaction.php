<?php
session_start();
include '../includes/db_connect.php';

// Ensure only admin or superadmin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if a valid transaction ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid transaction ID.";
    header("Location: admin_transactions.php");
    exit();
}

$transaction_id = intval($_GET['id']);

// Fetch transaction details
$stmt = $conn->prepare("
    SELECT t.*, u.name AS user_name, u.lname AS user_lname, u.email AS user_email, u.phone AS user_phone,
           p.title AS property_title, p.location AS property_location, p.price AS property_price
    FROM payments t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN properties p ON t.property_id = p.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    $_SESSION['error'] = "Transaction not found!";
    header("Location: admin_transactions.php");
    exit();
}

// Load the content file
$page_content = __DIR__ . "/admin_view_transaction_content.php";
include 'dashboard_layout.php';
