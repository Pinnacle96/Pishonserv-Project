<?php
session_start();
include '../includes/db_connect.php';

// ✅ Allow multiple roles
$allowed_roles = ['agent', 'owner', 'hotel_owner', 'developer'];

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$property_id || !is_numeric($property_id)) {
    $_SESSION['error'] = "Invalid property ID!";
    header("Location: agent_properties.php");
    exit();
}

// ✅ Delete property only if owner_id matches
$stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $property_id, $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Property deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete property!";
}

$stmt->close();
header("Location: agent_properties.php");
exit();