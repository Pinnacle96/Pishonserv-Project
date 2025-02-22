<?php
include '../includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $email = $_SESSION['email'];

    // Fetch OTP, expiry, and role
    $stmt = $conn->prepare("SELECT otp, otp_expires_at, role FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['otp_expires_at']) > time()) {
            // Update user verification status
            $updateStmt = $conn->prepare("UPDATE users SET email_verified = 1, otp = NULL WHERE email = ?");
            $updateStmt->bind_param("s", $email);
            $updateStmt->execute();

            // Fetch user role
            $role = $row['role'];

            // Set success message
            $_SESSION['success'] = "Your email has been verified successfully!";

            // Redirect based on role
            switch ($role) {
                case "buyer":
                    header("Location: ../dashboard/buyer.php");
                    break;
                case "agent":
                    header("Location: ../dashboard/agent.php");
                    break;
                case "owner":
                    header("Location: ../dashboard/owner.php");
                    break;
                case "hotel_owner":
                    header("Location: ../dashboard/hotel_owner.php");
                    break;
                case "admin":
                    header("Location: ../dashboard/admin.php");
                    break;
                case "superadmin":
                    header("Location: ../dashboard/superadmin.php");
                    break;
                default:
                    header("Location: ../dashboard/default.php");
                    break;
            }
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