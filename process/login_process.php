<?php
include '../includes/db_connect.php'; // Includes $site_status from system_settings
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check site status and restrict login to superadmin only in maintenance/inactive mode
    if (in_array($site_status, ['maintenance', 'inactive'])) {
        $stmt = $conn->prepare("SELECT role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($role);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if ($role !== 'superadmin') {
                $_SESSION['error'] = "Login is restricted to superadmins only during maintenance or inactive mode.";
                header("Location: ../auth/login.php");
                exit();
            }
        }
        $stmt->close();
    }

    // Existing login logic
    $stmt = $conn->prepare("SELECT id, name, email, password, role, profile_image FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $email, $hashed_password, $role, $profile_image);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            $_SESSION['profile_image'] = $profile_image;

            // Ensure session is written and closed before redirect
            session_write_close();

            // ✅ Check for stored session redirect first
            if (!empty($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: ../" . $redirect_url);
                exit();
            }

            // ✅ If no session redirect, check cookie
            if (isset($_COOKIE['redirect_after_login'])) {
                $redirect_url = $_COOKIE['redirect_after_login'];
                setcookie("redirect_after_login", "", time() - 3600, "/");
                header("Location: ../" . $redirect_url);
                exit();
            }

            // ✅ Default Redirect Based on User Role
            if ($role === "buyer") {
                header("Location: ../dashboard/buyer_dashboard.php");
            } elseif ($role === "agent" || $role === "owner" || $role === "hotel_owner") {
                header("Location: ../dashboard/agent_dashboard.php");
            } elseif ($role === "admin") {
                header("Location: ../dashboard/admin_dashboard.php");
            } elseif ($role === "superadmin") {
                header("Location: ../dashboard/superadmin_dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password.";
            header("Location: ../auth/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No user found with this email.";
        header("Location: ../auth/login.php");
        exit();
    }
}
