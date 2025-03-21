<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch Admin Data
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid Admin ID.";
    header("Location: superadmin_manage.php");
    exit();
}

$admin_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT id, name, lname, email, phone, role, profile_image FROM users WHERE id = ? AND role IN ('admin', 'superadmin')");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header("Location: superadmin_manage.php");
    exit();
}

// Process Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role']; // Only 'admin' or 'superadmin' allowed
    $profile_image = $admin['profile_image']; // Keep the existing image by default

    // Ensure role is valid
    if (!in_array($role, ['admin', 'superadmin'])) {
        $_SESSION['error'] = "Invalid role selected.";
        header("Location: superadmin_edit_admin.php?id=$admin_id");
        exit();
    }

    // Handle Profile Image Upload
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique file name
        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_image_name = uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_image_name;

        // Validate image type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG & GIF are allowed.";
            header("Location: superadmin_edit_admin.php?id=$admin_id");
            exit();
        }

        // Upload new image
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Delete old image if it's not the default
            if ($admin['profile_image'] !== 'default.png' && file_exists($target_dir . $admin['profile_image'])) {
                unlink($target_dir . $admin['profile_image']);
            }

            $profile_image = $new_image_name; // Update image name in database
        } else {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: superadmin_edit_admin.php?id=$admin_id");
            exit();
        }
    }

    // Update Admin Details
    $update_stmt = $conn->prepare("UPDATE users SET name=?, lname=?, email=?, phone=?, role=?, profile_image=? WHERE id=?");
    $update_stmt->bind_param("ssssssi", $name, $lname, $email, $phone, $role, $profile_image, $admin_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Admin details updated successfully!";
        header("Location: superadmin_manage.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating admin.";
    }
}
$page_content = __DIR__ . "/superadmin_edit_admin_content.php";
include 'dashboard_layout.php';
