<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../auth/login.php");
    exit();
}

$page_content = __DIR__ . "/agent_content.php"; // Load agent-specific content
include 'dashboard_layout.php';
?>