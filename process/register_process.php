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
            $mail->Subject = 'Verify Your Email - OTP Code';

            // Comprehensive Email Body
            $mail->Body = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify Your Email - OTP Code</title>
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
            <h3>Verify Your Email Address</h3>
            <p>Thank you for registering with us! To complete your registration, please use the One-Time Password (OTP) below to verify your email address.</p>

            <div class="otp-code">
                Your OTP Code: <strong>' . $otp . '</strong>
            </div>

            <p>This code is valid for <strong>10 minutes</strong>. If you did not request this OTP, please ignore this email or contact our support team immediately.</p>

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
            $mail->AltBody = "Verify Your Email Address\n\n"
                . "Thank you for registering with us! To complete your registration, please use the One-Time Password (OTP) below to verify your email address.\n\n"
                . "Your OTP Code: $otp\n\n"
                . "This code is valid for 10 minutes. If you did not request this OTP, please ignore this email or contact our support team immediately.\n\n"
                . "If you have any questions or need assistance, feel free to reach out to us at support@pishonserv.com.\n\n"
                . "Best regards,\n"
                . "The PISHONSERV Team";

            // Send Email
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
