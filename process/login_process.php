<?php
session_start();
include '../includes/db_connect.php'; // Includes DB + CSRF token + site status

// ✅ Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ✅ CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token!";
        header("Location: ../auth/login.php");
        exit();
    }

    // ✅ Fetch login credentials
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ Check if site is in restricted mode
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
        } else {
            $_SESSION['error'] = "No user found with this email.";
            header("Location: ../auth/login.php");
            exit();
        }
        $stmt->close();
    }

    // ✅ Proceed with login check
    $stmt = $conn->prepare("SELECT id, name, email, password, role, profile_image FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $email, $hashed_password, $role, $profile_image);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // ✅ Set session
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            $_SESSION['profile_image'] = $profile_image;

            session_write_close(); // 🚀 Speed up response

            // ✅ Redirect priority: session → cookie → role
            if (!empty($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: ../" . $redirect);
                exit();
            }

            if (!empty($_COOKIE['redirect_after_login'])) {
                $redirect = $_COOKIE['redirect_after_login'];
                setcookie("redirect_after_login", "", time() - 3600, "/");
                header("Location: ../" . $redirect);
                exit();
            }

            // ✅ Role-based redirect
            switch ($role) {
                case 'buyer':
                    header("Location: ../dashboard/buyer_dashboard.php");
                    break;
                case 'agent':
                case 'owner':
                case 'hotel_owner':
                case 'developer':
                    header("Location: ../dashboard/agent_dashboard.php");
                    break;
                case 'admin':
                    header("Location: ../dashboard/admin_dashboard.php");
                    break;
                case 'superadmin':
                    header("Location: ../dashboard/superadmin_dashboard.php");
                    break;
                default:
                    $_SESSION['error'] = "Unknown user role.";
                    header("Location: ../auth/login.php");
                    exit();
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
