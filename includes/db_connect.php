<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "real_estate_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$settings_query = $conn->query("SELECT site_status FROM system_settings LIMIT 1");
$settings = $settings_query->fetch_assoc() ?? ['site_status' => 'active'];
$site_status = $settings['site_status'];

$current_page = basename($_SERVER['PHP_SELF']);
$is_logging_in = ($current_page === 'login.php' || $current_page === 'login_process.php');

// ✅ Allow login-related pages or superadmin to proceed
if (!$is_logging_in && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin')) {
    if ($site_status === 'maintenance') {
        header("Location: /pishonserv.com/maintenance.php");
        exit();
    }

    if ($site_status === 'inactive') {
        header("Location: /pishonserv.com/site_closed.php");
        exit();
    }
}
