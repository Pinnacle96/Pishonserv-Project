<?php
session_start();
include '../includes/db_connect.php';

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: admin_properties.php");
    exit();
}

// Get Property ID
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($property_id > 0) {
    // Fetch property details to delete images
    $stmt = $conn->prepare("SELECT images FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();

    if ($property) {
        // Delete images from storage
        $images = explode(',', $property['images']);
        foreach ($images as $image) {
            $image_path = "../public/uploads/" . $image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete property from database
        $delete_stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
        $delete_stmt->bind_param("i", $property_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Property deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete property.";
        }
    } else {
        $_SESSION['error'] = "Property not found!";
    }
} else {
    $_SESSION['error'] = "Invalid property ID!";
}

header("Location: admin_properties.php");
exit();
?>