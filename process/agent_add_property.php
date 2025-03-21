<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Invalid request method.");
}

// ✅ Fetch and Sanitize Inputs
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

// ✅ Validate Required Fields
if (!$title || !$price || !$location || !$listing_type || !$type || $bedrooms < 0 || $bathrooms < 0 || !$size || $garage < 0 || !$description || !$owner_id) {
    error_log("❌ Error: Missing required fields.");
    header("Location: ../dashboard/agent_properties.php?error=missing_fields");
    exit();
}

// ✅ Initialize Image Paths
$imagePaths = [];

if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (count($_FILES['images']['name']) > 7) {
        error_log("❌ Error: Too many images uploaded.");
        header("Location: ../dashboard/agent_properties.php?error=max_files");
        exit();
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            // ✅ Generate a unique filename
            $fileExtension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $filePath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmp_name, $filePath)) {
                $imagePaths[] = $uniqueName; // ✅ Store filename correctly
            } else {
                error_log("❌ Failed to upload image: " . $_FILES['images']['name'][$key]);
            }
        } else {
            error_log("❌ Image Upload Error: " . $_FILES['images']['error'][$key]);
        }
    }
}

// ✅ Ensure `$imageString` is a Proper Comma-Separated String
$imageString = count($imagePaths) > 0 ? implode(',', $imagePaths) : "default.jpg";

// ✅ Debugging: Log the final image string before inserting into the database
error_log("✅ Final Image String: '$imageString'");

// ✅ Prepare and Execute SQL Query
$stmt = $conn->prepare("INSERT INTO properties 
    (title, price, location, listing_type, type, bedrooms, bathrooms, size, garage, description, images, owner_id, status, admin_approved) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0)");

if (!$stmt) {
    error_log("❌ SQL Prepare Error: " . $conn->error);
    header("Location: ../dashboard/agent_properties.php?error=sql_error");
    exit();
}

// ✅ Corrected Binding for `images` Column
$stmt->bind_param(
    "sdsssiiisssi",
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
    $owner_id
);

// ✅ Execute and Debug
if ($stmt->execute()) {
    $_SESSION['success'] = "Property added successfully! Pending admin approval.";
    error_log("✅ Property added with ID: " . $stmt->insert_id);
    header("Location: ../dashboard/agent_properties.php?success=true");
    ob_end_flush();
    exit();
} else {
    error_log("❌ SQL Execution Error: " . $stmt->error);
    header("Location: ../dashboard/agent_properties.php?error=db_insert_failed");
    ob_end_flush();
    exit();
}
