<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Define allowed roles
$allowed_roles = ['agent', 'owner', 'hotel_owner'];

// Check if the user's role is allowed
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../auth/unauthorized.php"); // Redirect to an unauthorized page
    exit();
}

// Load the agent content for all allowed roles
$page_content = __DIR__ . "/agent_content.php";

// Include the dashboard layout
include 'dashboard_layout.php';
?>