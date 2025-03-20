<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $otp = rand(100000, 999999);
    $otp_expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $image_name = "default.png"; // Default profile image

    // Check for previous redirect
    $redirect_after_login = $_SESSION['redirect_after_login'] ?? '../auth/verify-otp.php';

    // File Upload Logic
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $image_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($image_ext, $allowed_ext)) {
            $image_name = uniqid() . "." . $image_ext;
            $target_dir = "../public/uploads/";
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $image_name);
        }
    }

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (name, lname, email, phone, password, role, otp, otp_expires_at, profile_image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $lname, $email, $phone, $password, $role, $otp, $otp_expires_at, $image_name);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['profile_image'] = $image_name;

        // ✅ Sync User to Zoho CRM
        $zoho_contact_id = createZohoContact($name, $lname, $email, $phone, $role);
        if ($zoho_contact_id) {
            $stmt = $conn->prepare("UPDATE users SET zoho_contact_id = ? WHERE id = ?");
            $stmt->bind_param("si", $zoho_contact_id, $user_id);
            $stmt->execute();
        } else {
            error_log("Failed to sync user $email to Zoho.");
        }

        // ✅ Store intended page before OTP verification
        if (isset($_SESSION['redirect_after_login'])) {
            $_SESSION['temp_redirect_after_otp'] = $_SESSION['redirect_after_login'];
        }

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
            $_SESSION['success'] = "Registration successful! Please verify your email with OTP.";

            header("Location: ../auth/verify-otp.php");
            exit();
        } catch (Exception $e) {
            error_log("Mail sending failed: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Error sending OTP email. Please try again.";
            header("Location: ../auth/register.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../auth/register.php");
        exit();
    }
}
