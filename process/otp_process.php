<?php
include '../includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $email = $_SESSION['email'];

    // Fetch OTP, expiry, and full user details
    $stmt = $conn->prepare("SELECT id, name, email, password, role, profile_image, otp_expires_at FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['otp_expires_at']) > time()) {
            // Update user verification status
            $updateStmt = $conn->prepare("UPDATE users SET email_verified = 1, otp = NULL WHERE email = ?");
            $updateStmt->bind_param("s", $email);
            $updateStmt->execute();

            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['profile_image'] = $row['profile_image'];

            // Set success message
            $_SESSION['success'] = "Your email has been verified successfully!";

            // Redirect based on role
            switch ($row['role']) {
                case "buyer":
                    header("Location: ../dashboard/buyer_dashboard.php");
                    break;
                case "agent":
                case "owner":
                case "hotel_owner":
                    header("Location: ../dashboard/agent_dashboard.php");
                    break;
                case "admin":
                    header("Location: ../dashboard/admin_dashboard.php");
                    break;
                case "superadmin":
                    header("Location: ../dashboard/superadmin_dashboard.php");
                    break;
                default:
                    // Fallback for unknown role (redirect to login)
                    $_SESSION['error'] = "Role not recognized, please log in again.";
                    header("Location: ../auth/login.php");
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
