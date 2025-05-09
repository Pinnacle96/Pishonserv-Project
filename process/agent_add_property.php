<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../includes/db_connect.php';
include '../includes/config.php';
include '../includes/zoho_functions.php';
include '../includes/vision_helper.php'; // ✅ Vision API Helper

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Invalid request method.");
}

// ✅ Timestamp
$timestamp = date("Y-m-d H:i:s");

// ✅ CSRF Token Logging
$log_post = __DIR__ . '/debug_csrf_post.log';
$log_session = __DIR__ . '/debug_csrf_session.log';
$log_combined = __DIR__ . '/debug_csrf.log';

file_put_contents($log_post, "[$timestamp] POST: " . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents($log_session, "[$timestamp] SESSION: " . json_encode($_SESSION) . "\n", FILE_APPEND);

$posted_token = $_POST['csrf_token'] ?? 'MISSING';
$session_token = $_SESSION['csrf_token'] ?? 'MISSING';
file_put_contents($log_combined, "[$timestamp] Session: $session_token | Posted: $posted_token\n", FILE_APPEND);

if (empty($_POST) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['error'] = "File size too large. Try reducing image sizes.";
    error_log("❌ POST is empty. Likely exceeded PHP post_max_size.");
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// ✅ Validate CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token!";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// ✅ Sanitize Inputs
$title = trim($_POST['title'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$location = trim($_POST['location'] ?? '');
$listing_type = trim($_POST['listing_type'] ?? '');
$type = trim($_POST['type'] ?? '');
$bedrooms = (int) ($_POST['bedrooms'] ?? 0);
$bathrooms = (int) ($_POST['bathrooms'] ?? 0);
$size = trim($_POST['size'] ?? '');
$garage = (int) ($_POST['garage'] ?? 0);
$description = trim($_POST['description'] ?? '');
$owner_id = $_SESSION['user_id'] ?? null;
$latitude = null;
$longitude = null;

// ✅ Validate Required Fields
if (!$title || !$price || !$location || !$listing_type || !$type || $bedrooms < 0 || $bathrooms < 0 || !$size || $garage < 0 || !$description || !$owner_id) {
    error_log("❌ Missing required fields.");
    $_SESSION['error'] = "Please fill all required fields.";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// ✅ Get Coordinates from LocationIQ
try {
    $encodedLocation = urlencode($location);
    $url = "https://us1.locationiq.com/v1/search.php?key=" . LOCATIONIQ_API_KEY . "&q=$encodedLocation&format=json";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data[0])) {
        $latitude = $data[0]['lat'];
        $longitude = $data[0]['lon'];
    }
} catch (Exception $e) {
    error_log("⚠️ LocationIQ API error: " . $e->getMessage());
}

// ✅ Handle Image Upload
$imagePaths = [];

if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (count($_FILES['images']['name']) > 7) {
        error_log("❌ Too many images uploaded.");
        $_SESSION['error'] = "You can upload up to 7 images only.";
        header("Location: ../dashboard/agent_properties.php");
        exit();
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileExtension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $filePath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmp_name, $filePath)) {
                if (!isImageSafe($filePath)) {
                    unlink($filePath);
                    error_log("❌ Blocked illicit image: $uniqueName");
                    $_SESSION['error'] = "One or more images contain inappropriate content.";
                    header("Location: ../dashboard/agent_properties.php");
                    exit();
                }
                $imagePaths[] = $uniqueName;
            } else {
                error_log("❌ Failed to upload image: " . $_FILES['images']['name'][$key]);
            }
        } else {
            error_log("❌ Upload Error: " . $_FILES['images']['error'][$key]);
        }
    }
}

$imageString = count($imagePaths) > 0 ? implode(',', $imagePaths) : "default.jpg";

// ✅ Property Defaults
$admin_approved = 1;
$status = 'available';
$expiry_date = in_array($listing_type, ['for_sale', 'for_rent']) ? date('Y-m-d', strtotime('+30 days')) : null;

// ✅ Insert Property
$stmt = $conn->prepare("INSERT INTO properties (
    title, price, location, listing_type, type,
    bedrooms, bathrooms, size, garage, description, images, owner_id,
    status, admin_approved, latitude, longitude, expiry_date
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    error_log("❌ SQL Prepare Error: " . $conn->error);
    $_SESSION['error'] = "Database error. Please try again.";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

$stmt->bind_param(
    "sdsssiiisssisssss",
    $title,
    $price,
    $location,
    $listing_type,
    $type,
    $bedrooms,
    $bathrooms,
    $size,
    $garage,
    $description,
    $imageString,
    $owner_id,
    $status,
    $admin_approved,
    $latitude,
    $longitude,
    $expiry_date
);

if ($stmt->execute()) {
    $property_id = $stmt->insert_id;
    $stmt->close();

    // ✅ Get Zoho lead ID
    $stmt = $conn->prepare("SELECT zoho_lead_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $stmt->bind_result($zoho_lead_id);
    $stmt->fetch();
    $stmt->close();

    if (!empty($zoho_lead_id)) {
        try {
            createZohoProperty($title, $price, $location, $listing_type, $status, $type, $bedrooms, $bathrooms, $size, $description, $garage, $zoho_lead_id, $owner_id, $property_id);
        } catch (Exception $e) {
            error_log("❌ Zoho sync failed: " . $e->getMessage());
        }
    } else {
        error_log("⚠️ Missing zoho_lead_id for user $owner_id, skipping sync.");
    }

    $_SESSION['success'] = "Property added successfully and synced to Zoho.";
    header("Location: ../dashboard/agent_properties.php");
    ob_end_flush();
    exit();
} else {
    error_log("❌ SQL Execution Error: " . $stmt->error);
    $_SESSION['error'] = "Failed to save property. Please try again.";
    header("Location: ../dashboard/agent_properties.php");
    ob_end_flush();
    exit();
}
