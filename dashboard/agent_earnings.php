<?php 

session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../auth/login.php");
    exit();
}
$page_content = __DIR__ . "/agent_earnings_content.php"; 
include 'dashboard_layout.php'; 
?>