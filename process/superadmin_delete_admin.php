<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $admin_id = $_GET['id'];

    // Prevent deleting superadmin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    if ($role === 'superadmin') {
        $_SESSION['error'] = "Superadmin cannot be deleted!";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $admin_id);
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Admin deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete admin.";
        }
    }
}

header("Location: ../dashboard/superadmin_manage.php");
exit();
