<?php
session_start();
include '../includes/db_connect.php';

// Ensure only admin or superadmin can approve/reject
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = $_GET['id'];
$action = $_GET['action'];

if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE properties SET admin_approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $_SESSION['success'] = "Property Approved!";
} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE properties SET admin_approved = 0 WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $_SESSION['error'] = "Property Rejected!";
}

header("Location: admin_properties.php");
exit();