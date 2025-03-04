<?php
include '../includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

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

            // Set success message
            $_SESSION['success'] = "Login successful! Welcome, $name.";

            // Check if user was redirected from a property or checkout page
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login']; // Store the redirect URL
                unset($_SESSION['redirect_after_login']); // Remove the session variable
                header("Location: $redirect_url"); // Redirect user back to where they were
                exit();
            }

            // Redirect Based on Role
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
