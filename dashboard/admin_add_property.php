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
    $title = $_POST['title'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    $listing_type = $_POST['listing_type'];
    $description = $_POST['description'];
    $images_array = [];

    // ✅ Process Image Upload
    $target_dir = "../public/uploads/";
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $image_name = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
        $image_path = $target_dir . $image_name;

        // ✅ Compress Image Before Storing
        if (compressImage($tmp_name, $image_path, 50)) {
            $images_array[] = $image_name;
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
    }
}

// ✅ Image Compression Function
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
    } else {
        return false;
    }
    imagejpeg($image, $destination, $quality);
    return true;
}

// ✅ Load Admin Dashboard Layout
$page_content = __DIR__ . "/admin_add_property_content.php";
include 'dashboard_layout.php';
?>