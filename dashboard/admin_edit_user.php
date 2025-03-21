<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Import Zoho API Functions

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure only admin or superadmin can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch User Data
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid User ID.";
    header("Location: admin_users.php");
    exit();
}

$user_id = intval($_GET['id']);
echo "<!-- Debug: User ID = $user_id -->";

$stmt = $conn->prepare("SELECT id, name, lname, email, phone, role, profile_image, zoho_contact_id 
                        FROM users 
                        WHERE id = ? 
                        AND role IN ('buyer', 'agent', 'owner', 'hotel_owner')");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo "<!-- Debug: Query Result = " . ($user ? json_encode($user) : 'No user found') . " -->";

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: admin_users.php");
    exit();
}

// Process Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $profile_image = $user['profile_image'];
    $zoho_contact_id = $user['zoho_contact_id'];

    if (!in_array($role, ['buyer', 'agent', 'owner', 'hotel_owner'])) {
        $_SESSION['error'] = "Invalid role selected.";
        header("Location: admin_edit_user.php?id=$user_id");
        exit();
    }

    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_image_name = uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_image_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG & GIF are allowed.";
            header("Location: admin_edit_user.php?id=$user_id");
            exit();
        }

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            if ($user['profile_image'] !== 'default.png' && file_exists($target_dir . $user['profile_image'])) {
                unlink($target_dir . $user['profile_image']);
            }
            $profile_image = $new_image_name;
        } else {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: admin_edit_user.php?id=$user_id");
            exit();
        }
    }

    $update_stmt = $conn->prepare("UPDATE users SET name=?, lname=?, email=?, phone=?, role=?, profile_image=? WHERE id=?");
    $update_stmt->bind_param("ssssssi", $name, $lname, $email, $phone, $role, $profile_image, $user_id);

    if ($update_stmt->execute()) {
        if (!empty($zoho_contact_id)) {
            $updateZoho = updateZohoUser($zoho_contact_id, $name, $lname, $email, $phone, $role);
            if (!$updateZoho) {
                $_SESSION['warning'] = "User details updated but failed to sync with Zoho CRM.";
            }
        }
        $_SESSION['success'] = "User details updated successfully!";
        header("Location: admin_users.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating user: " . $update_stmt->error;
    }
}

// Zoho function (unchanged)
function updateZohoUser($zoho_contact_id, $firstName, $lastName, $email, $phone, $role)
{
    $access_token = getZohoAccessToken();
    if (!$access_token) return false;

    $zoho_url = "https://www.zohoapis.com/crm/v2/Contacts/$zoho_contact_id";
    $update_data = [
        "data" => [[
            "First_Name" => $firstName,
            "Last_Name" => $lastName,
            "Email" => $email,
            "Phone" => $phone,
            "Role" => ucfirst($role)
        ]]
    ];

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zoho_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    return isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] === "SUCCESS";
}

$page_content = __DIR__ . "/admin_edit_user_content.php";
include 'dashboard_layout.php';
