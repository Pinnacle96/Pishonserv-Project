<?php
session_start();
include '../includes/db_connect.php';
require '../vendor/autoload.php'; // Ensure PHPMailer is loaded

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate OTP and expiration time
        $otp = rand(100000, 999999);
        $otp_expire_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Update OTP, expire time, and set email verification to false (0)
        $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ?, email_verified = 0 WHERE email = ?");
        $update_stmt->bind_param("sss", $otp, $otp_expire_time, $email);
        $update_stmt->execute();

        // Store in session for verification
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Send OTP via Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io'; // Change to your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'dd769cbbedffe8'; // Your SMTP username
            $mail->Password = '3f933c457af86c'; // Your SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 2525;

            $mail->setFrom('no-reply@realestate.com', 'Real Estate App');
            $mail->addAddress($email); // Send to user email

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - OTP Code';
            $mail->Body = "<h3>Your OTP Code: <strong>$otp</strong></h3><p>This code expires in 10 minutes.</p>";

            $mail->send();

            $_SESSION['success'] = "OTP sent successfully! Check your email.";
            header("Location: ../auth/verify_otp.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Email sending failed: " . $mail->ErrorInfo;
            header("Location: ../auth/forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Email not found.";
        header("Location: ../auth/forgot_password.php");
        exit();
    }
}
?>