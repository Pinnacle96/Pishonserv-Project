<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db_connect.php';
include '../includes/config.php'; // Include LOCATIONIQ_API_KEY

$allowed_roles = ['agent', 'owner', 'hotel_owner', 'developer'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../auth/login.php");
    exit();
}

$property_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

// Fetch property details
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $property_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    $_SESSION['error'] = "Property not found!";
    header("Location: agent_properties.php");
    exit();
}

// ✅ Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token!";
        header("Location: agent_edit_property.php?id=" . $property_id);
        exit();
    }

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

    // ✅ Image Upload Handling
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

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
                $image_path = $target_dir . $image_name;

                if (compressImage($tmp_name, $image_path, 50)) {
                    $new_images[] = $image_name;
                }
            }
        }
    }

    $image_string = empty($new_images) ? $property['images'] : implode(',', $new_images);

    // ✅ Fetch lat/lon from LocationIQ
    $lat = null;
    $lon = null;
    $locationiq_key = LOCATIONIQ_API_KEY;
    $encoded_location = urlencode($location);
    $geo_url = "https://us1.locationiq.com/v1/search.php?key={$locationiq_key}&q={$encoded_location}&format=json";

    $geo_response = @file_get_contents($geo_url);
    if ($geo_response !== false) {
        $geo_data = json_decode($geo_response, true);
        if (isset($geo_data[0]['lat']) && isset($geo_data[0]['lon'])) {
            $lat = $geo_data[0]['lat'];
            $lon = $geo_data[0]['lon'];
        }
    }

    // ✅ Update property
    $updateStmt = $conn->prepare("UPDATE properties 
        SET title=?, price=?, location=?, listing_type=?, type=?, status=?, bedrooms=?, bathrooms=?, size=?, garage=?, description=?, images=?, latitude=?, longitude=? 
        WHERE id=? AND owner_id=?");

    $updateStmt->bind_param(
        "sdsssiiisssddii",
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
        $lat,
        $lon,
        $property_id,
        $user_id
    );

    if ($updateStmt->execute()) {
        $_SESSION['success'] = "✅ Property updated successfully!";
    } else {
        $_SESSION['error'] = "❌ Failed to update property!";
    }

    $updateStmt->close();
    header("Location: agent_properties.php");
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

    ob_start();
    if ($info['mime'] == 'image/png') {
        imagepng($image, null, 9);
    } else {
        imagejpeg($image, null, $quality);
    }
    $compressed = ob_get_clean();
    file_put_contents($destination, $compressed);
    imagedestroy($image);
    return true;
}

// ✅ Load edit UI
$page_content = __DIR__ . "/agent_edit_property_content.php";
include 'dashboard_layout.php';
