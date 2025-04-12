<?php
session_start();
include '../includes/db_connect.php';
include '../includes/config.php';
include '../includes/zoho_functions.php';

$log_prefix = date('Y-m-d H:i:s') . " [Admin Add Property] ";

// Ensure only Admins & Superadmins can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    error_log($log_prefix . "Unauthorized access: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
error_log($log_prefix . "Admin ID: $admin_id, Role: {$_SESSION['role']}");

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
    $garage_spaces = intval($_POST['garage'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $latitude = null;
    $longitude = null;

    // Validate inputs
    if (empty($title) || $price <= 0 || empty($location) || empty($type) || empty($status) || empty($listing_type) || empty($description)) {
        error_log($log_prefix . "Validation failed: title=$title, price=$price, location=$location, type=$type, status=$status, listing_type=$listing_type, description=$description");
        $_SESSION['error'] = "All required fields must be filled correctly!";
        header("Location: admin_add_property.php");
        exit();
    }

    // Validate size
    if ($size !== '' && (!is_numeric($size) || $size < 0)) {
        error_log($log_prefix . "Invalid size: $size");
        $_SESSION['error'] = "Size must be a non-negative number!";
        header("Location: admin_add_property.php");
        exit();
    }
    $size = $size !== '' ? floatval($size) : null;

    // Get Coordinates from LocationIQ
    $encodedLocation = urlencode($location);
    $locationIQUrl = "https://us1.locationiq.com/v1/search.php?key=" . LOCATIONIQ_API_KEY . "&q=$encodedLocation&format=json";
    error_log($log_prefix . "Fetching coordinates for location: $location");

    $response = @file_get_contents($locationIQUrl);
    if ($response === false) {
        error_log($log_prefix . "LocationIQ request failed for location: $location");
    } else {
        $data = json_decode($response, true);
        if (!empty($data[0])) {
            $latitude = $data[0]['lat'];
            $longitude = $data[0]['lon'];
            error_log($log_prefix . "Coordinates: lat=$latitude, lon=$longitude");
        } else {
            error_log($log_prefix . "No coordinates found for location: $location");
        }
    }

    // Handle images
    $images_array = [];
    $target_dir = "../public/uploads/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
        error_log($log_prefix . "Created upload directory: $target_dir");
    }

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                    error_log($log_prefix . "Upload error for image {$key}: " . $_FILES['images']['error'][$key]);
                    $_SESSION['error'] = "Upload error for image: " . $_FILES['images']['name'][$key];
                    continue;
                }

                $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
                $image_path = $target_dir . $image_name;

                if (compressImage($tmp_name, $image_path, 50)) {
                    $images_array[] = $image_name;
                    error_log($log_prefix . "Image uploaded: $image_name");
                } else {
                    error_log($log_prefix . "Failed to compress image: " . $_FILES['images']['name'][$key]);
                    $_SESSION['error'] = "Error processing image: " . $_FILES['images']['name'][$key];
                }
            }
        }
    }

    $image_string = implode(',', $images_array);
    error_log($log_prefix . "Images: $image_string");

    // Insert property
    $insert_stmt = $conn->prepare("INSERT INTO properties (
        title, price, location, type, status, listing_type, description,
        bedrooms, bathrooms, garage, size,
        images, latitude, longitude, owner_id, admin_approved
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

    if (!$insert_stmt) {
        error_log($log_prefix . "Prepare failed: " . $conn->error);
        $_SESSION['error'] = "Database error!";
        header("Location: admin_add_property.php");
        exit();
    }

    $insert_stmt->bind_param(
        "sdssssssiiissss",
        $title,
        $price,
        $location,
        $type,
        $status,
        $listing_type,
        $description,
        $bedrooms,
        $bathrooms,
        $garage_spaces,
        $size,
        $image_string,
        $latitude,
        $longitude,
        $admin_id
    );

    if ($insert_stmt->execute()) {
        $property_id = $conn->insert_id;
        error_log($log_prefix . "Property inserted: ID=$property_id, title=$title");
        $insert_stmt->close();

        // Sync to Zoho CRM
        try {
            // Get zoho_lead_id
            $lead_stmt = $conn->prepare("SELECT zoho_lead_id FROM users WHERE id = ?");
            $lead_stmt->bind_param("i", $admin_id);
            $lead_stmt->execute();
            $result = $lead_stmt->get_result();
            $user = $result->fetch_assoc();
            $lead_stmt->close();

            if (!$user || empty($user['zoho_lead_id'])) {
                error_log($log_prefix . "No zoho_lead_id for admin_id=$admin_id");
                $_SESSION['error'] = "Admin not synced to Zoho CRM!";
                header("Location: admin_properties.php");
                exit();
            }
            $zoho_lead_id = $user['zoho_lead_id'];
            error_log($log_prefix . "Found zoho_lead_id=$zoho_lead_id for admin_id=$admin_id");

            // Call createZohoProperty
            $success = createZohoProperty(
                $title,
                $price,
                $location,
                $listing_type,
                $status,
                $type,
                $bedrooms,
                $bathrooms,
                $size,
                $description,
                $garage_spaces,
                $zoho_lead_id,
                $admin_id,
                $property_id
            );

            if ($success) {
                error_log($log_prefix . "Property ID=$property_id synced to Zoho");
                $_SESSION['success'] = "Property added and synced to Zoho CRM!";
            } else {
                error_log($log_prefix . "Zoho sync returned false for property ID=$property_id");
                $_SESSION['error'] = "Property added but failed to sync to Zoho CRM!";
            }
        } catch (Exception $e) {
            error_log($log_prefix . "Zoho sync error for property ID=$property_id: " . $e->getMessage());
            $_SESSION['error'] = "Property added but Zoho sync failed: " . htmlspecialchars($e->getMessage());
        }

        header("Location: admin_properties.php");
        exit();
    } else {
        error_log($log_prefix . "Insert failed: " . $insert_stmt->error);
        $_SESSION['error'] = "Error adding property: " . $insert_stmt->error;
        $insert_stmt->close();
        header("Location: admin_add_property.php");
        exit();
    }
}

// Image compression function
function compressImage($source, $destination, $targetKB = 50)
{
    if (!file_exists($source)) {
        error_log("CompressImage: Source file does not exist: $source");
        return false;
    }

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
            error_log("CompressImage: Unsupported mime type: {$info['mime']}");
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
    error_log("CompressImage: Compressed to $destination, size=" . (filesize($destination) / 1024) . "KB");
    return true;
}

// View layout
$page_content = __DIR__ . "/admin_add_property_content.php";
include 'dashboard_layout.php';
