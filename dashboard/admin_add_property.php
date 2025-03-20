<?php
session_start();
include '../includes/db_connect.php';

// ✅ Ensure only Admins & Superadmins can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Check if fields exist before accessing them
    $title = isset($_POST['title']) ? trim($_POST['title']) : null;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : null;
    $location = isset($_POST['location']) ? trim($_POST['location']) : null;
    $type = isset($_POST['type']) ? trim($_POST['type']) : null;
    $status = isset($_POST['status']) ? trim($_POST['status']) : null;
    $listing_type = isset($_POST['listing_type']) ? trim($_POST['listing_type']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // ✅ Check if any required field is missing
    if (!$title || !$price || !$location || !$type || !$status || !$listing_type || !$description) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: admin_add_property.php");
        exit();
    }

    $images_array = [];

    // ✅ Process Image Upload
    $target_dir = "../public/uploads/";

    // ✅ Ensure upload directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // ✅ Validate and process uploaded images
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                // ✅ Check for upload errors
                if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                    $_SESSION['error'] = "Upload error: " . $_FILES['images']['error'][$key];
                    continue;
                }

                // ✅ Generate a unique filename
                $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
                $image_path = $target_dir . $image_name;

                // ✅ Compress Image Before Storing
                if (compressImage($tmp_name, $image_path, 50)) {
                    $images_array[] = $image_name;
                } else {
                    $_SESSION['error'] = "Error processing image: " . $_FILES['images']['name'][$key];
                }
            }
        }
    }

    // ✅ Convert images array to string
    $image_string = implode(',', $images_array);

    // ✅ Insert Property into Database (Admin-approved by default)
    $stmt = $conn->prepare("INSERT INTO properties (title, price, location, type, status, listing_type, description, images, owner_id, admin_approved) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sdssssssi", $title, $price, $location, $type, $status, $listing_type, $description, $image_string, $admin_id);

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

// ✅ Image Compression Function
function compressImage($source, $destination, $targetKB = 50)
{
    if (!file_exists($source) || empty($source)) {
        return false;
    }

    $info = getimagesize($source);
    if ($info === false) {
        return false;
    }

    // ✅ Create image resource from file
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
            $image = imagepalettetotruecolor($image);
            break;
        default:
            return false;
    }

    // ✅ Resize large images before compressing
    list($width, $height) = getimagesize($source);
    $maxWidth = 1200;
    $maxHeight = 1200;

    if ($width > $maxWidth || $height > $maxHeight) {
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxWidth) {
            $newHeight = (int)(($maxWidth / $width) * $height);
            $newWidth = $maxWidth;
        }
        if ($newHeight > $maxHeight) {
            $newWidth = (int)(($maxHeight / $newHeight) * $newWidth);
            $newHeight = $maxHeight;
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resizedImage;
    }

    // ✅ Start compression at 90% quality
    $quality = 90;
    while ($quality > 10) {
        imagejpeg($image, $destination, $quality);
        clearstatcache();
        $filesize = filesize($destination) / 1024;

        if ($filesize <= $targetKB) {
            imagedestroy($image);
            return true;
        }

        $quality -= 5;
    }

    imagejpeg($image, $destination, 10);
    imagedestroy($image);
    clearstatcache();
    return (filesize($destination) <= $targetKB * 1024);
}

// ✅ Load Admin Dashboard Layout
$page_content = __DIR__ . "/admin_add_property_content.php";
include 'dashboard_layout.php';