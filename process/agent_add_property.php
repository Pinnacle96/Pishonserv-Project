<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../includes/db_connect.php';
include '../includes/config.php';
include '../includes/zoho_functions.php';
include '../includes/vision_helper.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Invalid request method.");
}

$timestamp = date("Y-m-d H:i:s");

// CSRF debug logs
file_put_contents(__DIR__ . '/debug_csrf_post.log', "[$timestamp] POST: " . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents(__DIR__ . '/debug_csrf_session.log', "[$timestamp] SESSION: " . json_encode($_SESSION) . "\n", FILE_APPEND);
file_put_contents(
    __DIR__ . '/debug_csrf.log',
    "[$timestamp] Session: " . ($_SESSION['csrf_token'] ?? 'MISSING') . " | Posted: " . ($_POST['csrf_token'] ?? 'MISSING') . "\n",
    FILE_APPEND
);


if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['error'] = "Invalid CSRF token!";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// Sanitize inputs
// ✅ Sanitize Inputs
$title = trim($_POST['title'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$location = trim($_POST['location'] ?? '');
$type = trim($_POST['type'] ?? '');
$listing_type = trim($_POST['listing_type'] ?? '');
$description = trim($_POST['description'] ?? '');
$bedrooms = intval($_POST['bedrooms'] ?? 0);
$bathrooms = intval($_POST['bathrooms'] ?? 0);
$garage = isset($_POST['garage']) && is_numeric($_POST['garage']) ? intval($_POST['garage']) : null;
$size = isset($_POST['size']) && is_numeric($_POST['size']) ? floatval($_POST['size']) : null;
$furnishing_status = trim($_POST['furnishing_status'] ?? '');
$property_condition = trim($_POST['property_condition'] ?? '');
$amenities = isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '';
$maintenance_fee = isset($_POST['maintenance_fee']) ? floatval($_POST['maintenance_fee']) : null;
$agent_fee = isset($_POST['agent_fee']) ? floatval($_POST['agent_fee']) : null;
$caution_fee = isset($_POST['caution_fee']) ? floatval($_POST['caution_fee']) : null;
$price_frequency = trim($_POST['price_frequency'] ?? '');
$minimum_stay = isset($_POST['minimum_stay']) ? intval($_POST['minimum_stay']) : null;
$checkin_time = trim($_POST['checkin_time'] ?? '');
$checkout_time = trim($_POST['checkout_time'] ?? '');
$room_type = trim($_POST['room_type'] ?? '');
$star_rating = isset($_POST['star_rating']) ? intval($_POST['star_rating']) : null;
$policies = trim($_POST['policies'] ?? '');

$owner_id = $_SESSION['user_id'] ?? null;
$latitude = $longitude = null;
$expiry_date = null;

if (in_array($listing_type, ['for_sale', 'for_rent'])) {
    $expiry_date = date('Y-m-d', strtotime('+30 days'));
}

if (!$title || !$price || !$location || !$type || !$listing_type || !$description) {
    $_SESSION['error'] = "Please fill all required fields.";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// Get coordinates from LocationIQ
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

// Image upload
$imagePaths = [];
$uploadDir = '../public/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['images']['name'][0])) {
    if (count($_FILES['images']['name']) > 7) {
        $_SESSION['error'] = "You can upload up to 7 images only.";
        header("Location: ../dashboard/agent_properties.php");
        exit();
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $uniqueName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $filePath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmp_name, $filePath)) {
                if (!isImageSafe($filePath)) {
                    unlink($filePath);
                    $_SESSION['error'] = "One or more images contain inappropriate content.";
                    header("Location: ../dashboard/agent_properties.php");
                    exit();
                }
                $imagePaths[] = $uniqueName;
            }
        }
    }
}

$imageString = count($imagePaths) > 0 ? implode(',', $imagePaths) : "default.jpg";

//$status = 'available';
$admin_approved = 1;

$stmt = $conn->prepare("INSERT INTO properties (
    title, price, location, type, status, listing_type, description, bedrooms, bathrooms, garage, size,
    furnishing_status, property_condition, amenities, maintenance_fee, agent_fee, caution_fee,
    price_frequency, minimum_stay, checkin_time, checkout_time, room_type, star_rating, policies,
    images, owner_id, admin_approved, latitude, longitude, expiry_date
) VALUES (?, ?, ?, ?, 'available', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    $_SESSION['error'] = "Database error. Please try again.";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// Types: s = string, d = double, i = integer
$stmt->bind_param(
    "sdssssiidssssdddisssssississs",  // ✅ 30 types
    $title,
    $price,
    $location,
    $type,
    $listing_type,
    $description,
    $bedrooms,
    $bathrooms,
    $garage,
    $size,
    $furnishing_status,
    $property_condition,
    $amenities,
    $maintenance_fee,
    $agent_fee,
    $caution_fee,
    $price_frequency,
    $minimum_stay,
    $checkin_time,
    $checkout_time,
    $room_type,
    $star_rating,
    $policies,
    $imageString,
    $owner_id,
    $admin_approved,
    $latitude,
    $longitude,
    $expiry_date
);
if (!$stmt) {
    error_log("❌ MySQL Prepare Failed: " . $conn->error);
    $_SESSION['error'] = "Database error. Please try again.";
    header("Location: ../dashboard/agent_properties.php");
    exit();
}
if ($stmt->execute()) {
    $property_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("SELECT zoho_lead_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $stmt->bind_result($zoho_lead_id);
    $stmt->fetch();
    $stmt->close();

    if (!empty($zoho_lead_id)) {
        try {
            createZohoProperty($title, $price, $location, $listing_type, 'available', $type, $bedrooms, $bathrooms, $size, $description, $garage, $zoho_lead_id, $owner_id, $property_id);
            $_SESSION['success'] = "Property added successfully and synced to Zoho.";
        } catch (Exception $e) {
            error_log("❌ Zoho sync failed: " . $e->getMessage());
            $_SESSION['error'] = "Property added but Zoho sync failed: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Property added, but Zoho lead ID is missing.";
    }

    header("Location: ../dashboard/agent_properties.php");
    ob_end_flush();
    exit();
} else {
    $_SESSION['error'] = "Failed to save property. Please try again.";
    header("Location: ../dashboard/agent_properties.php");
    ob_end_flush();
    exit();
}