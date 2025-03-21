<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure only admin or superadmin can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if the request is POST and user_id is provided
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../dashboard/admin_users.php");
    exit();
}

$user_id = intval($_POST['user_id']);

// Fetch user details to verify role and get profile image
$stmt = $conn->prepare("SELECT role, profile_image FROM users WHERE id = ? AND role IN ('buyer', 'agent', 'owner', 'hotel_owner')");
if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: ../dashboard/admin_users.php");
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found or cannot be deleted.";
    header("Location: ../dashboard/admin_users.php");
    exit();
}

// Delete the user's profile image if it exists and isn’t default
$target_dir = "../public/uploads/";
if ($user['profile_image'] && $user['profile_image'] !== 'default.png' && file_exists($target_dir . $user['profile_image'])) {
    if (!unlink($target_dir . $user['profile_image'])) {
        $_SESSION['warning'] = "User deleted but profile image could not be removed.";
    }
}

// Delete the user from the database
$delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
if (!$delete_stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: ../dashboard/admin_users.php");
    exit();
}
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = "User deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting user: " . $delete_stmt->error;
}

$delete_stmt->close();
$stmt->close();
$conn->close();

header("Location: ../dashboard/admin_users.php");
exit();
