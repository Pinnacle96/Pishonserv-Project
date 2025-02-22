<?php
// Enable error reporting for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include '../includes/db_connect.php';
session_start();

// Check if session email exists
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    $_SESSION['error'] = "Session email not set. Please login again.";
    header("Location: ../auth/login.php");
    exit();
}

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST['otp'];
    $email = $_SESSION['email'];

    // Ensure database connection is working
    if (!$conn) {
        $_SESSION['error'] = "Database connection failed: " . mysqli_connect_error();
        header("Location: ../auth/verify_otp.php");
        exit();
    }

    // Fetch OTP and expiry from database
    $stmt = $conn->prepare("SELECT otp, otp_expires_at FROM users WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../auth/verify_otp.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Check if OTP matches
        if ($row['otp'] == $user_otp) {
            // Check if OTP is expired
            if (strtotime($row['otp_expires_at']) > time()) {
                // Update user verification status
                $updateStmt = $conn->prepare("UPDATE users SET email_verified = 1, otp = NULL WHERE email = ?");
                if (!$updateStmt) {
                    $_SESSION['error'] = "Database error: " . $conn->error;
                    header("Location: ../auth/verify_otp.php");
                    exit();
                }
                
                $updateStmt->bind_param("s", $email);
                $updateStmt->execute();

                // Unset OTP session
                unset($_SESSION['otp']);

                // ✅ Success - Store in session and redirect
                $_SESSION['success'] = "OTP Verified! You can now reset your password.";
                header("Location: ../auth/reset_password.php");
                exit();
            } else {
                // ❌ OTP Expired
                $_SESSION['error'] = "Your OTP has expired. Please request a new one.";
                header("Location: ../auth/verify_otp.php");
                exit();
            }
        } else {
            // ❌ Invalid OTP
            $_SESSION['error'] = "Invalid OTP. Please try again.";
            header("Location: ../auth/verify_otp.php");
            exit();
        }
    } else {
        // ❌ No OTP Found
        $_SESSION['error'] = "No OTP is associated with this email. Please request a new one.";
        header("Location: ../auth/verify_otp.php");
        exit();
    }
}
?>