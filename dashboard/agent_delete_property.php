<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Delete property
$stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $property_id, $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Property deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete property!";
}
header("Location: agent_properties.php");
exit();
?>