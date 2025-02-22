<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['email'])) {
        $_SESSION['error'] = "Session expired. Try resetting again.";
        header("Location: ../auth/forgot_password.php");
        exit();
    }

    $email = $_SESSION['email'];
    $new_password = $_POST['new_password'];

    // Validate password length
    if (strlen($new_password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: ../auth/reset_password.php");
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires_at = NULL WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        // Clear session data after reset
        unset($_SESSION['email']);
        $_SESSION['success'] = "Password reset successfully! You can now login.";
        header("Location: ../auth/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Something went wrong. Try again.";
        header("Location: ../auth/reset_password.php");
        exit();
    }
}
?>