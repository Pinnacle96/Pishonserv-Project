<?php
session_start();
include '../includes/db_connect.php';

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    $status = 'unread'; // New messages are unread by default

    if (empty($message)) {
        $_SESSION['error'] = "Message cannot be empty.";
        header("Location: ../dashboard/agent_messages.php");
        exit();
    }

    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Message sent successfully.";

        // Fetch receiver email for notification
        $user_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $receiver_id);
        $user_stmt->execute();
        // Send Email Notification

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_mailtrap_username';
            $mail->Password = 'your_mailtrap_password';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 2525;

            $mail->setFrom('no-reply@realestate.com', 'Real Estate Platform');
            $mail->addAddress($receiver_email);

            $mail->isHTML(true);
            $mail->Subject = 'New Message Notification';
            $mail->Body = "<p>You have received a new message:</p><p><strong>Message:</strong> $message</p>";

            $mail->send();
        } catch (Exception $e) {
            $_SESSION['error'] = "Message sent, but email notification failed.";
        }
    } else {
        $_SESSION['error'] = "Failed to send message. Try again.";
    }

    header("Location: ../dashboard/agent_messages.php");
    exit();
}
