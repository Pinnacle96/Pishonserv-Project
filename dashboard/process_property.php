<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Zoho API Functions

// Ensure only admin or superadmin can approve/reject
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = $_GET['id'];
$action = $_GET['action'];

if ($action === 'approve') {
    // Fetch property details
    $stmt = $conn->prepare("SELECT p.*, u.zoho_contact_id FROM properties p 
                            JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();

    if (!$property) {
        $_SESSION['error'] = "Property not found.";
        header("Location: admin_properties.php");
        exit();
    }

    // Ensure the owner has a Zoho Contact ID before syncing
    if (empty($property['zoho_contact_id'])) {
        $_SESSION['error'] = "Error: Property owner is not synced to Zoho CRM.";
        header("Location: admin_properties.php");
        exit();
    }

    // Approve Property in Database
    $stmt = $conn->prepare("UPDATE properties SET admin_approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();

    // Sync Property to Zoho CRM
    $zoho_property_id = createZohoProperty(
        $property['title'],
        $property['price'],
        $property['location'],
        $property['type'],
        $property['status'],
        $property['zoho_contact_id']
    );

    if ($zoho_property_id) {
        // Store Zoho ID in Database
        $stmt = $conn->prepare("UPDATE properties SET zoho_property_id = ? WHERE id = ?");
        $stmt->bind_param("si", $zoho_property_id, $property_id);
        $stmt->execute();

        $_SESSION['success'] = "✅ Property Approved & Synced to Zoho CRM!";
    } else {
        $_SESSION['error'] = "⚠️ Property approved, but syncing to Zoho CRM failed.";
    }
} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE properties SET admin_approved = 0 WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();

    $_SESSION['error'] = "❌ Property Rejected!";
}

// Redirect with SweetAlert
header("Location: admin_properties.php");
exit();