<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
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
$price = isset($_POST['price']) ? (float) $_POST['price'] : null;
$location = isset($_POST['location']) ? trim($_POST['location']) : null;
$type = isset($_POST['type']) ? trim($_POST['type']) : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : null;
$owner_id = $_SESSION['user_id'] ?? null;

// Prevent submission if fields are empty
if (!$title || !$price || !$location || !$type || !$description || !$owner_id) {
    die("Error: All fields are required.");
}

// Image Upload Handling
$imagePaths = [];
if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $fileName = uniqid() . '_' . $_FILES['images']['name'][$key];
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmp_name, $filePath)) {
            $imagePaths[] = $fileName;
        } else {
            die("Error uploading image: " . $_FILES['images']['name'][$key]);
        }
    }
}

$imageString = implode(',', $imagePaths);

$stmt = $conn->prepare("INSERT INTO properties 
    (title, price, location, type, description, owner_id, images, status, admin_approved) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 0)");
$stmt->bind_param("sdsssis", $title, $price, $location, $type, $description, $owner_id, $imageString);

if ($stmt->execute()) {
    $_SESSION['success'] = "Property added successfully! Pending admin approval.";
    header("Location: ../dashboard/agent_properties.php");
    exit();
} else {
    die("Error inserting property: " . $stmt->error);
}