<?php
session_start();
include '../includes/db_connect.php';
include '../includes/config.php'; // ✅ LOCATIONIQ_API_KEY

// ✅ Ensure only Admins & Superadmins can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $listing_type = trim($_POST['listing_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $garage = intval($_POST['garage'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $latitude = null;
    $longitude = null;

    if (!$title || !$price || !$location || !$type || !$status || !$listing_type || !$description) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: admin_add_property.php");
        exit();
    }

    // 🌍 Get Coordinates from LocationIQ
    $encodedLocation = urlencode($location);
    $locationIQUrl = "https://us1.locationiq.com/v1/search.php?key=" . LOCATIONIQ_API_KEY . "&q=$encodedLocation&format=json";

    $response = file_get_contents($locationIQUrl);
    $data = json_decode($response, true);

    if (!empty($data[0])) {
        $latitude = $data[0]['lat'];
        $longitude = $data[0]['lon'];
    }

    $images_array = [];
    $target_dir = "../public/uploads/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                    $_SESSION['error'] = "Upload error: " . $_FILES['images']['error'][$key];
                    continue;
                }

                $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
                $image_path = $target_dir . $image_name;

                if (compressImage($tmp_name, $image_path, 50)) {
                    $images_array[] = $image_name;
                } else {
                    $_SESSION['error'] = "Error processing image: " . $_FILES['images']['name'][$key];
                }
            }
        }
    }

    $image_string = implode(',', $images_array);

    $stmt = $conn->prepare("INSERT INTO properties (
        title, price, location, type, status, listing_type, description,
        bedrooms, bathrooms, garage, size,
        images, latitude, longitude, owner_id, admin_approved
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

    $stmt->bind_param(
        "sdssssssiiiisssi",
        $title,
        $price,
        $location,
        $type,
        $status,
        $listing_type,
        $description,
        $bedrooms,
        $bathrooms,
        $garage,
        $size,
        $image_string,
        $latitude,
        $longitude,
        $admin_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Property added successfully!";
        header("Location: admin_properties.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding property!";
        header("Location: admin_add_property.php");
        exit();
    }
}

// ✅ Image compression function
function compressImage($source, $destination, $targetKB = 50)
{
    if (!file_exists($source)) return false;

    $info = getimagesize($source);
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    list($width, $height) = getimagesize($source);
    $maxW = 1200;
    $maxH = 1200;

    if ($width > $maxW || $height > $maxH) {
        $ratio = min($maxW / $width, $maxH / $height);
        $newW = (int)($width * $ratio);
        $newH = (int)($height * $ratio);
        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
        $image = $resized;
    }

    $quality = 90;
    while ($quality > 10) {
        imagejpeg($image, $destination, $quality);
        clearstatcache();
        if (filesize($destination) / 1024 <= $targetKB) break;
        $quality -= 5;
    }

    imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return true;
}

// ✅ View layout
$page_content = __DIR__ . "/admin_add_property_content.php";
include 'dashboard_layout.php';
