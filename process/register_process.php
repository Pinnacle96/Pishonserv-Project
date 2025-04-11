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
    $address = trim($_POST['address']);
    $nin = trim($_POST['nin']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $otp = rand(100000, 999999);
    $otp_expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $image_name = "default.png"; // Default image

    $redirect_after_login = $_SESSION['redirect_after_login'] ?? '../auth/verify-otp.php';

    // File Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $image_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($image_ext, $allowed_ext)) {
            $image_name = uniqid() . "." . $image_ext;
            $target_dir = "../public/uploads/";
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $image_name);
        }
    }

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (name, lname, email, phone, address, nin, password, role, otp, otp_expires_at, profile_image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $name, $lname, $email, $phone, $address, $nin, $password, $role, $otp, $otp_expires_at, $image_name);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['profile_image'] = $image_name;

        // Zoho CRM Lead Creation
        $zoho_lead_id = createZohoLead($name, $lname, $email, $phone, $role);
        if ($zoho_lead_id) {
            $stmt = $conn->prepare("UPDATE users SET zoho_lead_id = ? WHERE id = ?");
            $stmt->bind_param("si", $zoho_lead_id, $user_id);
            $stmt->execute();
        }

        if (isset($_SESSION['redirect_after_login'])) {
            $_SESSION['temp_redirect_after_otp'] = $_SESSION['redirect_after_login'];
        }

        // Send OTP Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtppro.zoho.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pishonserv@pishonserv.com';
            $mail->Password = 'Serv@4321@Ikeja';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - Your OTP Code';

            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px;'>
                <h2 style='color: #092468;'>Hello {$name},</h2>
                <p>Thank you for registering with <strong>PISHONSERV</strong>.</p>
                <p>To complete your registration, please use the One-Time Password (OTP) below to verify your email address:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 28px; font-weight: bold; color: #CC9933;'>{$otp}</span>
                </div>
                <p>This code is valid for <strong>10 minutes</strong>. Please do not share this code with anyone.</p>
                <p>If you did not initiate this request, please ignore this message or contact our support team immediately.</p>
                <p>Best Regards,<br><strong>PISHONSERV Team</strong><br>
                <a href='mailto:support@pishonserv.com'>support@pishonserv.com</a></p>
            </div>";

            $mail->AltBody = "Hello {$name}, Your OTP Code is {$otp}. It expires in 10 minutes. If you did not request this, kindly ignore this email or contact support@pishonserv.com.";

            $mail->send();

            $_SESSION['success'] = "Registration successful! Please check your email for the OTP code to verify your account.";
            header("Location: ../auth/verify-otp.php");
            exit();
        } catch (Exception $e) {
            error_log("Mail sending failed: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Unable to send verification email. Please try again.";
            header("Location: ../auth/register.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../auth/register.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid Request.";
    header("Location: ../auth/register.php");
    exit();
}
