<?php
session_start();
include '../includes/db_connect.php';

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch system settings
$settings_query = $conn->query("SELECT * FROM system_settings LIMIT 1");
$settings = $settings_query->fetch_assoc() ?? ['commission' => 0.00, 'max_users' => 1000, 'site_status' => 'active'];


// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $commission = $_POST['commission'];
    $max_users = $_POST['max_users'];
    $site_status = $_POST['site_status'];

    // Ensure the settings exist
    if ($settings) {
        $update_query = $conn->prepare("UPDATE system_settings SET commission=?, max_users=?, site_status=?");
    } else {
        $update_query = $conn->prepare("INSERT INTO system_settings (commission, max_users, site_status) VALUES (?, ?, ?)");
    }

    $update_query->bind_param("dis", $commission, $max_users, $site_status);
    
    if ($update_query->execute()) {
        $_SESSION['success'] = "Settings updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update settings.";
    }
    header("Location: superadmin_settings.php");
    exit();
}
?>

<?php $page_content = __DIR__ . "/superadmin_settings_content.php"; include 'dashboard_layout.php'; ?>