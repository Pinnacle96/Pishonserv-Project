<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = intval($_GET['id']); // Ensure property ID is valid

// Fetch property details
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    $_SESSION['error'] = "Property not found!";
    header("Location: admin_properties.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data safely
    $title = trim($_POST['title']);
    $price = floatval($_POST['price']);
    $location = trim($_POST['location']);
    $type = $_POST['type'];
    $status = $_POST['status'];
    $listing_type = $_POST['listing_type'];
    $description = trim($_POST['description']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $size = trim($_POST['size']);
    $garage = intval($_POST['garage']);
    $admin_approved = intval($_POST['admin_approved']);

    $new_images = [];

    // ✅ Handle image upload
    if (!empty($_FILES['images']['name'][0])) {
        $target_dir = "../public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Remove old images if new images are uploaded
        if (!empty($property['images'])) {
            $old_images = explode(',', $property['images']);
            foreach ($old_images as $old_image) {
                $old_path = $target_dir . $old_image;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
        }

        // Process new image uploads
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
            $image_path = $target_dir . $image_name;

            if (compressImage($tmp_name, $image_path, 75)) { // Compress image at 75% quality
                $new_images[] = $image_name;
            }
        }
    }

    // Keep old images if no new images are uploaded
    $image_string = empty($new_images) ? $property['images'] : implode(',', $new_images);

    // ✅ Update Property Query
    $updateStmt = $conn->prepare("UPDATE properties 
        SET title=?, price=?, location=?, type=?, status=?, listing_type=?, description=?, bedrooms=?, bathrooms=?, size=?, garage=?, images=?, admin_approved=? 
        WHERE id=?");

    $updateStmt->bind_param(
        "sdssssssiiissi",
        $title,
        $price,
        $location,
        $type,
        $status,
        $listing_type,
        $description,
        $bedrooms,
        $bathrooms,
        $size,
        $garage,
        $image_string,
        $admin_approved,
        $property_id
    );

    if ($updateStmt->execute()) {
        $_SESSION['success'] = "✅ Property updated successfully!";
    } else {
        $_SESSION['error'] = "❌ Failed to update property!";
    }
    header("Location: admin_properties.php");
    exit();
}

// ✅ Image Compression Function
function compressImage($source, $destination, $quality)
{
    $info = getimagesize($source);

    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepng($image, $destination, 9);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            imagegif($image, $destination);
            break;
        default:
            return false;
    }

    imagedestroy($image);
    return true;
}

$page_content = __DIR__ . "/admin_edit_property_content.php";
include 'dashboard_layout.php';
