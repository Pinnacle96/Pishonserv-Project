<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch Analytics Data Based on Date Filter
$stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM users WHERE created_at BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_transactions FROM transactions WHERE created_at BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$total_transactions = $stmt->get_result()->fetch_assoc()['total_transactions'];

$page_content = __DIR__ . "/superadmin_reports_content.php";
include 'dashboard_layout.php';
?>