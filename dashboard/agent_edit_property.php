<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../includes/db_connect.php';

// Allowed roles
$allowed_roles = ['agent', 'owner', 'hotel_owner'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../auth/login.php");
    exit();
}

// Get Property ID & User ID
$property_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

// Fetch Property Details
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $property_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    error_log("Property not found or unauthorized access!"); // Debugging
    $_SESSION['error'] = "Property not found!";
    header("Location: agent_properties.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Form submitted!"); // Debugging

    // Fetch & sanitize input fields
    $title = trim($_POST['title']);
    $price = floatval($_POST['price']);
    $location = trim($_POST['location']);
    $listing_type = trim($_POST['listing_type']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $size = trim($_POST['size']);
    $garage = intval($_POST['garage']);
    $description = trim($_POST['description']);
    $new_images = [];

    // Image Upload Handling
    if (!empty($_FILES['images']['name'][0])) {
        error_log("Processing file uploads..."); // Debugging
        $target_dir = "../public/uploads/";

        // Delete old images
        $old_images = explode(',', $property['images']);
        foreach ($old_images as $old_image) {
            $old_path = $target_dir . $old_image;
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        // Process new images
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
                $image_path = $target_dir . $image_name;

                // Compress & Move Uploaded File
                if (compressImage($tmp_name, $image_path, 50)) { // Compress to 50% quality
                    $new_images[] = $image_name;
                }
            }
        }
    }

    // Convert images array to string for storage
    $image_string = empty($new_images) ? $property['images'] : implode(',', $new_images);

    // Update property
    $updateStmt = $conn->prepare("UPDATE properties 
        SET title=?, price=?, location=?, listing_type=?, type=?, status=?, bedrooms=?, bathrooms=?, size=?, garage=?, description=?, images=? 
        WHERE id=? AND owner_id=?");
    $updateStmt->bind_param(
        "sdsssiiissssii",
        $title,
        $price,
        $location,
        $listing_type,
        $type,
        $status,
        $bedrooms,
        $bathrooms,
        $size,
        $garage,
        $description,
        $image_string,
        $property_id,
        $user_id
    );

    if ($updateStmt->execute()) {
        error_log("Property updated successfully!"); // Debugging
        $_SESSION['success'] = "Property updated successfully!";
    } else {
        error_log("Database update failed: " . $updateStmt->error); // Debugging
        $_SESSION['error'] = "Failed to update property!";
    }

    // Close connections and redirect
    $updateStmt->close();
    header("Location: agent_properties.php");
    exit();
}

// Image Compression Function
function compressImage($source, $destination, $quality)
{
    $info = getimagesize($source);
    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    // Reduce file size progressively
    $target_quality = $quality;
    do {
        ob_start();
        if ($info['mime'] == 'image/png') {
            imagepng($image, null, $target_quality);
        } else {
            imagejpeg($image, null, $target_quality);
        }
        $compressed_image = ob_get_clean();
        $size = strlen($compressed_image);
        $target_quality -= 5;
    } while ($size > 5120 && $target_quality > 10); // 5KB = 5120 bytes

    file_put_contents($destination, $compressed_image);
    imagedestroy($image);

    return true;
}

// Include dashboard layout
$page_content = __DIR__ . "/agent_edit_property_content.php";
include 'dashboard_layout.php';
