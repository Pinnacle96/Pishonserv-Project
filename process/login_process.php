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
            // Redirect Based on Role
            if ($role === "buyer") {
                header("Location: ../dashboard/buyer.php");
            } elseif ($role === "agent") {
                header("Location: ../dashboard/agent.php");
            } elseif ($role === "owner") {
                header("Location: ../dashboard/owner.php");
            } elseif ($role === "hotel_owner") {
                header("Location: ../dashboard/hotel_owner.php");
            } elseif ($role === "admin") {
                header("Location: ../dashboard/admin.php");
            } elseif ($role === "superadmin") {
                header("Location: ../dashboard/superadmin.php");
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
?>