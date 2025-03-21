<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Zoho API Integration

// Ensure only superadmin can add users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ensure request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Invalid request method.");
}

// Capture & sanitize input
$name = trim($_POST['name']);
$lname = trim($_POST['lname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$role = trim($_POST['role']);

$default_profile = "default.png";

// Ensure all fields are provided
if (!$name || !$lname || !$email || !$phone || !$role) {
    $_SESSION['error'] = "All fields are required!";
    header("Location: ../admin/manage_admins.php");
    exit();
}

// ✅ Check if email already exists
$check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
$check_email->store_result();

if ($check_email->num_rows > 0) {
    $_SESSION['error'] = "Email already exists!";
    header("Location: ../admin/manage_admins.php");
    exit();
}
$check_email->close();

// ✅ Insert new admin/user
$stmt = $conn->prepare("INSERT INTO users (name, lname, email, phone, password, role, profile_image, email_verified) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
$stmt->bind_param("sssssss", $name, $lname, $email, $phone, $password, $role, $default_profile);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    $_SESSION['success'] = "User added successfully!";

    // ✅ Sync to Zoho CRM
    $zoho_contact_id = createZohoContact($name, $lname, $email, $phone, $role);

    // ✅ Store Zoho ID in database
    if ($zoho_contact_id) {
        $stmt = $conn->prepare("UPDATE users SET zoho_contact_id = ? WHERE id = ?");
        $stmt->bind_param("si", $zoho_contact_id, $user_id);
        $stmt->execute();
        $_SESSION['success'] .= " Synced to Zoho CRM!";
    } else {
        $_SESSION['error'] = "User added, but Zoho sync failed.";
    }
} else {
    $_SESSION['error'] = "Failed to add user!";
}

// ✅ Redirect to manage admins page
header("Location: ../dashboard/superadmin_manage.php");
exit();
