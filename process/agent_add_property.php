<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Debug upload limits
echo "<pre>";
var_dump(ini_get('post_max_size'));
var_dump(ini_get('upload_max_filesize'));
var_dump($_SERVER['CONTENT_LENGTH']);
echo "</pre>";

include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Invalid request method.");
}

// Validate & sanitize input
$title = isset($_POST['title']) ? trim($_POST['title']) : null;
$price = isset($_POST['price']) ? (float)$_POST['price'] : null;
$location = isset($_POST['location']) ? trim($_POST['location']) : null;
$listing_type = isset($_POST['listing_type']) ? trim($_POST['listing_type']) : null;
$type = isset($_POST['type']) ? trim($_POST['type']) : null;
$bedrooms = isset($_POST['bedrooms']) ? (int)$_POST['bedrooms'] : null;
$bathrooms = isset($_POST['bathrooms']) ? (int)$_POST['bathrooms'] : null;
$size = isset($_POST['size']) ? trim($_POST['size']) : null;
$garage = isset($_POST['garage']) ? (int)$_POST['garage'] : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : null;
$owner_id = $_SESSION['user_id'] ?? null;

// Prevent submission if required fields are empty or invalid
if (!$title || !$price || !$location || !$listing_type || !$type || $bedrooms === null || $bathrooms === null || !$size || $garage === null || !$description || !$owner_id) {
    die("Error: All fields are required.");
}

// Additional validation
if ($price < 0) {
    die("Error: Price cannot be negative.");
}
if ($bedrooms < 0) {
    die("Error: Bedrooms cannot be negative.");
}
if ($bathrooms < 0) {
    die("Error: Bathrooms cannot be negative.");
}
if ($garage < 0) {
    die("Error: Garage spaces cannot be negative.");
}

// Validate listing_type and type against allowed values
$valid_listing_types = ['for_sale', 'for_rent', 'short_let'];
$valid_types = [
    'apartment',
    'office',
    'event_center',
    'hotel',
    'short_stay',
    'house',
    'villa',
    'condo',
    'townhouse',
    'duplex',
    'penthouse',
    'studio',
    'bungalow',
    'commercial',
    'warehouse',
    'retail',
    'land',
    'farmhouse',
    'mixed_use'
];
if (!in_array($listing_type, $valid_listing_types)) {
    die("Error: Invalid listing type selected.");
}
if (!in_array($type, $valid_types)) {
    die("Error: Invalid property type selected.");
}

// Image Upload Handling
$imagePaths = [];
if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $maxImages = 7;
    if (count($_FILES['images']['name']) > $maxImages) {
        die("Error: You can upload a maximum of $maxImages images.");
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $filePath)) {
                $imagePaths[] = $fileName;
            } else {
                die("Error uploading image: " . $_FILES['images']['name'][$key]);
            }
        } else {
            die("Error with file upload: " . $_FILES['images']['error'][$key]);
        }
    }
}

$imageString = implode(',', $imagePaths);

// Prepare and execute SQL query
$stmt = $conn->prepare("INSERT INTO properties 
    (title, price, location, listing_type, type, bedrooms, bathrooms, size, garage, description, images, owner_id, status, admin_approved) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");
$stmt->bind_param(
    "sdsssiiissii",  // Corrected to 12 characters
    $title,          // s (string)
    $price,          // d (double)
    $location,       // s (string)
    $listing_type,   // s (string)
    $type,           // s (string)
    $bedrooms,       // i (integer)
    $bathrooms,      // i (integer)
    $size,           // s (string)
    $garage,         // i (integer)
    $description,    // s (string)
    $imageString,    // s (string)
    $owner_id        // i (integer)
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Property added successfully! Pending admin approval.";
    $stmt->close();
    $conn->close();
    header("Location: ../dashboard/agent_properties.php");
    exit();
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    die("Error inserting property: " . $error);
}
