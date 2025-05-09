<?php
session_start();
include '../includes/db_connect.php';
include '../includes/config.php';
include '../includes/zoho_functions.php';

$log_prefix = date('Y-m-d H:i:s') . " [Admin Add Property] ";

// ✅ Only Admins & Superadmins
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    error_log($log_prefix . "Unauthorized access: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));
    header("Location: ../auth/login.php");
    exit();
}
$admin_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ CSRF Token Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<pre>Invalid CSRF token.</pre>";
        exit();
    }

    // ✅ Sanitize Inputs
    $title = trim($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $listing_type = trim($_POST['listing_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $garage = intval($_POST['garage'] ?? 0);
    $size = trim($_POST['size'] ?? '');
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

    $expiry_date = null;
    if (in_array($listing_type, ['for_sale', 'for_rent'])) {
        $expiry_date = date('Y-m-d', strtotime('+30 days'));
    }
    if (empty($title) || $price <= 0 || empty($location) || empty($type) || empty($listing_type) || empty($description)) {
        echo "<pre>Validation failed: Missing required fields.</pre>";
        exit();
    }

    $latitude = $longitude = null;
    $encodedLocation = urlencode($location);
    $geo_url = "https://us1.locationiq.com/v1/search.php?key=" . LOCATIONIQ_API_KEY . "&q=$encodedLocation&format=json";
    $geo_response = @file_get_contents($geo_url);
    if ($geo_response) {
        $geo_data = json_decode($geo_response, true);
        if (!empty($geo_data[0])) {
            $latitude = $geo_data[0]['lat'];
            $longitude = $geo_data[0]['lon'];
        }
    }

    $images_array = [];
    $upload_dir = "../public/uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
            if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$k]);
                $image_path = $upload_dir . $image_name;
                if (compressImage($tmp_name, $image_path, 50)) {
                    $images_array[] = $image_name;
                }
            }
        }
    }
    $image_string = implode(',', $images_array);

    $stmt = $conn->prepare("INSERT INTO properties (
        title, price, location, type, status, listing_type, description,
        bedrooms, bathrooms, garage, size, furnishing_status, property_condition,
        amenities, maintenance_fee, agent_fee, caution_fee, price_frequency,
        minimum_stay, checkin_time, checkout_time, room_type, star_rating, policies,
        images, latitude, longitude, owner_id, admin_approved, expiry_date
    ) VALUES (?, ?, ?, ?, 'available', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");

    if (!$stmt) {
        echo "<pre>MySQL Prepare Failed: " . $conn->error . "</pre>";
        exit();
    }

    $stmt->bind_param(
        "sdsssssiisssssdddsisssssssid",
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
        $image_string,
        $latitude,
        $longitude,
        $admin_id,
        $expiry_date
    );

    if ($stmt->execute()) {
        $property_id = $stmt->insert_id;
        $stmt->close();

        try {
            $lead_q = $conn->prepare("SELECT zoho_lead_id FROM users WHERE id = ?");
            $lead_q->bind_param("i", $admin_id);
            $lead_q->execute();
            $lead_res = $lead_q->get_result();
            $lead = $lead_res->fetch_assoc();
            $lead_q->close();

            if ($lead && !empty($lead['zoho_lead_id'])) {
                createZohoProperty(
                    $title,
                    $price,
                    $location,
                    $listing_type,
                    'available',
                    $type,
                    $bedrooms,
                    $bathrooms,
                    $size,
                    $description,
                    $garage,
                    $lead['zoho_lead_id'],
                    $admin_id,
                    $property_id
                );
                $_SESSION['success'] = "<pre>✅ Property added and synced with Zoho.</pre>";
            } else {
                $_SESSION['error'] = "<pre>⚠️ Missing Zoho Lead ID for admin.</pre>";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "<pre>❌ Property added but Zoho sync failed: " . $e->getMessage() . "</pre>";
        }
    } else {
        $_SESSION['error'] = "<pre>❌ Insert failed: " . $stmt->error . "</pre>";
        $stmt->close();
    }
}

function compressImage($src, $dest, $targetKB = 50)
{
    $info = getimagesize($src);
    switch ($info['mime']) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($src);
            break;
        case 'image/png':
            $img = imagecreatefrompng($src);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($src);
            break;
        default:
            return false;
    }
    list($w, $h) = $info;
    $max = 1200;
    $ratio = min($max / $w, $max / $h, 1);
    $nw = (int)($w * $ratio);
    $nh = (int)($h * $ratio);
    $resized = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $q = 90;
    while ($q > 10) {
        imagejpeg($resized, $dest, $q);
        clearstatcache();
        if (filesize($dest) / 1024 <= $targetKB) break;
        $q -= 5;
    }
    imagejpeg($resized, $dest, $q);
    imagedestroy($img);
    return true;
}


// View layout
$page_content = __DIR__ . "/admin_add_property_content.php";
include 'dashboard_layout.php';