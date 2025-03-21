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
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtppro.zoho.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pishonserv@pishonserv.com';
            $mail->Password = 'Serv@4321@Ikeja';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Sender and Recipient
            $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');
            $mail->addAddress($email, $name);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password - OTP Code';

            // Comprehensive Email Body
            $mail->Body = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Your Password - OTP Code</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background-color: #f9f9f9;
            }
            h3 {
                color: #333;
            }
            .otp-code {
                font-size: 24px;
                font-weight: bold;
                color: #007BFF;
                margin: 20px 0;
            }
            .footer {
                margin-top: 20px;
                font-size: 14px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h3>Reset Your Password</h3>
            <p>We received a request to reset the password for your PISHONSERV account. Please use the One-Time Password (OTP) below to verify your identity and proceed with resetting your password.</p>

            <div class="otp-code">
                Your OTP Code: <strong>' . $otp . '</strong>
            </div>

            <p>This code is valid for <strong>10 minutes</strong>. Enter it on the password reset page to continue. If you did not request a password reset, please ignore this email or contact our support team immediately.</p>

            <p>If you have any questions or need assistance, feel free to reach out to us at <a href="mailto:pishonserv@pishonserv.com">support@pishonserv.com</a>.</p>

            <div class="footer">
                <p>Best regards,</p>
                <p><strong>The PISHONSERV Team</strong></p>
            </div>
        </div>
    </body>
    </html>
    ';

            // Plain Text Version (Fallback)
            $mail->AltBody = "Reset Your Password\n\n"
                . "We received a request to reset the password for your PISHONSERV account. Please use the One-Time Password (OTP) below to verify your identity and proceed with resetting your password.\n\n"
                . "Your OTP Code: $otp\n\n"
                . "This code is valid for 10 minutes. Enter it on the password reset page to continue. If you did not request a password reset, please ignore this email or contact our support team immediately.\n\n"
                . "If you have any questions or need assistance, feel free to reach out to us at support@pishonserv.com.\n\n"
                . "Best regards,\n"
                . "The PISHONSERV Team";

            // Send Email
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
