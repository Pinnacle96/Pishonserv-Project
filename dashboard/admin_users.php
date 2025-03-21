<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$page_content = __DIR__ . "/admin_users_content.php";
include 'dashboard_layout.php';
