<?php
include '../includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $email = $_SESSION['email'];

    $stmt = $conn->prepare("SELECT otp, otp_expires_at FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['otp_expires_at']) > time()) {
            // Update user verification status
            $updateStmt = $conn->prepare("UPDATE users SET email_verified = 1, otp = NULL WHERE email = ?");
            $updateStmt->bind_param("s", $email);
            $updateStmt->execute();

            // Set success message
            $_SESSION['success'] = "Your email has been verified successfully!";
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            $_SESSION['error'] = "OTP expired. Please request a new OTP.";
            header("Location: ../auth/verify-otp.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid OTP. Please try again.";
        header("Location: ../auth/verify-otp.php");
        exit();
    }
}
?>