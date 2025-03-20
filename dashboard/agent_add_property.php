<?php
session_start();
include '../includes/db_connect.php';

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


$page_content = __DIR__ . "/agent_add_property_content.php";
include 'dashboard_layout.php';