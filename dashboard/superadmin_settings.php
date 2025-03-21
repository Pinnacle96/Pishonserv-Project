<?php
session_start();
include '../includes/db_connect.php';

// Restrict to superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for debugging, 0 for production

// Fetch system settings
$settings_query = $conn->query("SELECT * FROM system_settings LIMIT 1");
$settings = $settings_query->fetch_assoc(); // Null if table is empty

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $commission = floatval($_POST['commission'] ?? 0.00);
    $max_users = intval($_POST['max_users'] ?? 1000);
    $site_status = in_array($_POST['site_status'] ?? 'active', ['active', 'maintenance', 'inactive']) ? $_POST['site_status'] : 'active';

    // Check if settings exist
    if ($settings === null) {
        // Insert new row if table is empty
        $query = $conn->prepare("INSERT INTO system_settings (commission, max_users, site_status) VALUES (?, ?, ?)");
        if ($query === false) {
            $_SESSION['error'] = "Failed to prepare INSERT query: " . $conn->error;
            header("Location: superadmin_settings.php");
            exit();
        }
        $query->bind_param("dis", $commission, $max_users, $site_status);
    } else {
        // Update existing row
        $query = $conn->prepare("UPDATE system_settings SET commission = ?, max_users = ?, site_status = ? WHERE id = ?");
        if ($query === false) {
            $_SESSION['error'] = "Failed to prepare UPDATE query: " . $conn->error;
            header("Location: superadmin_settings.php");
            exit();
        }
        $query->bind_param("disi", $commission, $max_users, $site_status, $settings['id']);
    }

    // Execute query
    if ($query->execute()) {
        if ($query->affected_rows > 0 || $settings === null) {
            $_SESSION['success'] = "Settings updated successfully!";
        } else {
            $_SESSION['warning'] = "No changes were made to the settings.";
        }
    } else {
        $_SESSION['error'] = "Failed to execute query: " . $query->error;
    }

    $query->close();
    $conn->close(); // Close connection after use
    header("Location: superadmin_settings.php");
    exit();
}

// Handle Site Status Redirect for non-superadmins (only if settings exist)
if ($settings && in_array($settings['site_status'], ['maintenance', 'inactive']) && $_SESSION['role'] !== 'superadmin') {
    $redirect_page = $settings['site_status'] === 'maintenance' ? 'maintenance.php' : 'site_closed.php';
    echo "<script>window.location.href = '$redirect_page';</script>";
    exit();
}

$page_content = __DIR__ . "/superadmin_settings_content.php";
include 'dashboard_layout.php';
