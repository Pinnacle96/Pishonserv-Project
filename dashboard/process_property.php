<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Zoho API Functions

// ✅ Ensure only admin or superadmin can approve/reject
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ Get Property ID & Action
$property_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$property_id || !$action) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: admin_properties.php");
    exit();
}

// ✅ Handle Approval
if ($action === 'approve') {
    // 🔹 Fetch Property Details
    $stmt = $conn->prepare("SELECT p.*, u.zoho_contact_id FROM properties p 
                            JOIN users u ON p.owner_id = u.id 
                            WHERE p.id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$property) {
        $_SESSION['error'] = "Property not found.";
        header("Location: admin_properties.php");
        exit();
    }

    // 🔹 Ensure the owner has a Zoho Contact ID
    if (empty($property['zoho_contact_id'])) {
        $_SESSION['error'] = "Error: Property owner is not synced to Zoho CRM.";
        header("Location: admin_properties.php");
        exit();
    }

    // 🔹 Approve Property & Update Status
    $stmt = $conn->prepare("UPDATE properties SET admin_approved = 1, status = 'available' WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    if (!$stmt->execute()) {
        $_SESSION['error'] = "Error: Could not update property.";
        header("Location: admin_properties.php");
        exit();
    }
    $stmt->close();

    // 🔹 Sync Property to Zoho CRM
    $zoho_property_id = createZohoProperty(
        $property['title'],
        $property['price'],
        $property['location'],
        $property['type'],
        'available', // ✅ Ensure Zoho receives correct status
        $property['zoho_contact_id']
    );

    if ($zoho_property_id) {
        // ✅ Store Zoho ID in Database
        $stmt = $conn->prepare("UPDATE properties SET zoho_property_id = ? WHERE id = ?");
        $stmt->bind_param("si", $zoho_property_id, $property_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "✅ Property Approved & Synced to Zoho CRM!";
    } else {
        $_SESSION['error'] = "⚠️ Property approved, but syncing to Zoho CRM failed.";
    }
}

// ✅ Handle Rejection
elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE properties SET admin_approved = 0 WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['error'] = "❌ Property Rejected!";
}

// ✅ Redirect to Admin Properties Page
header("Location: admin_properties.php");
exit();
