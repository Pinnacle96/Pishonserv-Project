<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = intval($_GET['id']); // Ensure ID is an integer

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
    $title = $_POST['title'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    $listing_type = $_POST['listing_type'];
    $description = $_POST['description'];
    $admin_approved = $_POST['admin_approved'];

    $new_images = [];

    // Handle new image uploads
    if (!empty($_FILES['images']['name'][0])) {
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
            $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
            $image_path = $target_dir . $image_name;

            if (compressImage($tmp_name, $image_path, 50)) { // Compress image
                $new_images[] = $image_name;
            }
        }
    }

    // Convert images array to string for storage
    $image_string = empty($new_images) ? $property['images'] : implode(',', $new_images);

   // ✅ Correct SQL query with matching placeholders (10 placeholders)
$updateStmt = $conn->prepare("UPDATE properties 
    SET title=?, price=?, location=?, type=?, status=?, listing_type=?, description=?, images=?, admin_approved=? 
    WHERE id=?");

// ✅ Ensure `listing_type` is properly bound
$updateStmt->bind_param("sdssssssii", $title, $price, $location, $type, $status, $listing_type, $description, $image_string, $admin_approved, $property_id);

if ($updateStmt->execute()) {
    $_SESSION['success'] = "Property updated successfully!";
} else {
    $_SESSION['error'] = "Failed to update property!";
}
header("Location: admin_properties.php");
exit();

}

// Image Compression Function
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);

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

    $quality = ($quality > 5) ? 5 : $quality;
    
    if ($info['mime'] == 'image/png') {
        imagepng($image, $destination, 9);
    } else {
        imagejpeg($image, $destination, $quality);
    }
    imagedestroy($image);
    return true;
}

$page_content = __DIR__ . "/admin_edit_property_content.php";
include 'dashboard_layout.php';
?>