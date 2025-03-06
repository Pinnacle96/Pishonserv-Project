<?php
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Include Zoho API functions
//session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $phone = trim($_POST['phone']); // ✅ Capture phone number
    $otp = rand(100000, 999999);
    $otp_expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $image_name = "default.png"; // Default image

    // Handle Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $image_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($image_ext, $allowed_ext)) {
            $image_name = uniqid() . "." . $image_ext;
            $target_file = "../public/uploads/" . $image_name;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);
        }
    }

    // Insert user into the database (Including Phone Number)
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, otp, otp_expires_at, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("ssssssss", $name, $email, $password, $role, $phone, $otp, $otp_expires_at, $image_name);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; // Get newly created user ID

        // 🔹 Call Zoho API to sync user
        $zoho_contact_id = createZohoContact($name, $email, $phone, $role);

        // 🔹 Store Zoho Contact ID in the database
        if ($zoho_contact_id) {
            $stmt = $conn->prepare("UPDATE users SET zoho_contact_id = ? WHERE email = ?");
            $stmt->bind_param("ss", $zoho_contact_id, $email);
            $stmt->execute();
        }

        $_SESSION['email'] = $email;

        // Send OTP Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'dd769cbbedffe8';
            $mail->Password = '3f933c457af86c';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 2525;

            $mail->setFrom('no-reply@realestate.com', 'Real Estate App');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - OTP Code';
            $mail->Body = "<h3>Your OTP Code: <strong>$otp</strong></h3><p>This code expires in 10 minutes.</p>";

            $mail->send();
            $_SESSION['success'] = "Registration successful! Check your email for OTP.";
            header("Location: ../auth/verify-otp.php");
            exit();
        } catch (Exception $e) {
            die("Mail sending failed: " . $mail->ErrorInfo);
        }
    } else {
        $_SESSION['error'] = "Error registering user.";
    }
}
