<?php
session_start();
include '../includes/db_connect.php';

// Ensure agent is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['agent', 'owner', 'hotel_owner'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inquiry_id = $_POST['inquiry_id'];
    $buyer_id = $_POST['buyer_id'];
    $property_id = $_POST['property_id'];
    $reply_message = trim($_POST['reply_message']);
    $agent_id = $_SESSION['user_id'];

    if (empty($reply_message)) {
        $_SESSION['error'] = "Reply message cannot be empty.";
        header("Location: view_inquiry.php?id=$inquiry_id");
        exit();
    }

    // Insert the reply into the messages table
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, sender_role, receiver_role, property_id, message, status) 
                            VALUES (?, ?, 'agent', 'buyer', ?, ?, 'unread')");
    $stmt->bind_param("iiis", $agent_id, $buyer_id, $property_id, $reply_message);

    if ($stmt->execute()) {
        // ✅ Store a notification for the buyer
        $notification_msg = "You received a reply from an agent on your inquiry.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $buyer_id, $notification_msg);
        $stmt->execute();

        $_SESSION['success'] = "Reply sent successfully!";
    } else {
        $_SESSION['error'] = "Failed to send reply.";
    }

    header("Location: view_inquiry.php?id=$inquiry_id");
    exit();
}
