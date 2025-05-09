<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

// Restrict to agent, owner, or hotel_owner roles
<<<<<<< HEAD
$allowed_roles = ['agent', 'owner', 'hotel_owner', 'developer'];
=======
$allowed_roles = ['agent', 'owner', 'hotel_owner'];
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error'] = "You do not have permission to access this page.";
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's MOU file
try {
    $stmt = $conn->prepare("SELECT mou_file FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || empty($user['mou_file'])) {
        $_SESSION['error'] = "No MOU file found for your account.";
        header("Location: ../index.php");
        exit();
    }

    $mou_file = $user['mou_file'];
    $file_path = realpath(__DIR__ . '/../documents/mou/' . $mou_file);

    // Check if file exists
    if (!file_exists($file_path)) {
        $_SESSION['error'] = "MOU file is missing or inaccessible.";
        header("Location: ../index.php");
        exit();
    }

    // Serve the file for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($mou_file) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    readfile($file_path);
    exit();
} catch (Exception $e) {
    error_log("MOU download error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while accessing your MOU. Please try again.";
    header("Location: ../index.php");
    exit();
}
