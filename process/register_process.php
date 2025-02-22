<?php
include '../includes/db_connect.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $otp = rand(100000, 999999);
    $otp_expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $image_name = "default.png"; // Default image

    // Debugging: Check if the file exists
    if (!isset($_FILES['profile_image'])) {
        die("Error: profile_image field is missing in the form.");
    }

    if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        die("Upload Error: " . $_FILES['profile_image']['error']);
    }

    // Allowed file types
    $image_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    if (!in_array($image_ext, $allowed_ext)) {
        die("Invalid file type. Only JPG, JPEG, PNG allowed.");
    }

    // Generate unique name and set target directory
    $image_name = uniqid() . "." . $image_ext;
    $target_dir = "../public/uploads/";
    $target_file = $target_dir . $image_name;

    // Move file to target directory
    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
        die("File upload failed. Check folder permissions.");
    }

    // Check if file exists after moving
    if (!file_exists($target_file)) {
        die("File move unsuccessful. Not found in directory.");
    }

    // Compress Image
    function compressImage($source, $destination, $quality = 30) {
        $info = getimagesize($source);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        } else {
            return false;
        }
        if (!imagejpeg($image, $destination, $quality)) {
            die("Image compression failed.");
        }
        return true;
    }

    // Compress the image
    if (!compressImage($target_file, $target_file, 30)) {
        die("Image compression error.");
    }

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, otp, otp_expires_at, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssss", $name, $email, $password, $role, $otp, $otp_expires_at, $image_name);

    if (!$stmt->execute()) {
        die("Database insert error: " . $stmt->error);
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
}
?>