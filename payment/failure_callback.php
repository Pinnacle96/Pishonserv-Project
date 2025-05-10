<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$reference = $_GET['ref'] ?? null;

if (!$reference) {
    $_SESSION['error'] = "Missing payment reference.";
    header("Location: ../cart.php");
    exit();
}

// Look up the payment
$stmt = $conn->prepare("SELECT pp.id AS payment_id, pp.order_id, po.user_id, u.email, u.name 
                        FROM product_payments pp 
                        JOIN product_orders po ON pp.order_id = po.id 
                        JOIN users u ON po.user_id = u.id 
                        WHERE pp.reference = ? LIMIT 1");
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid or expired payment reference.";
    header("Location: ../cart.php");
    exit();
}

$row = $result->fetch_assoc();
$payment_id = $row['payment_id'];
$order_id = $row['order_id'];
$user_id = $row['user_id'];
$email = $row['email'];
$name = $row['name'];

// Update payment status to failed
$update = $conn->prepare("UPDATE product_payments SET payment_status = 'failed' WHERE id = ?");
$update->bind_param("i", $payment_id);
$update->execute();

// Optional: cancel order if needed
$conn->query("UPDATE product_orders SET status = 'cancelled' WHERE id = $order_id");

// Clear cart session
unset($_SESSION['cart']);

// Send failure email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtppro.zoho.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pishonserv@pishonserv.com';
    $mail->Password = 'Serv@4321@Ikeja';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'PISHONSERV - Payment Failed for Order #' . $order_id;
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f9f9f9;'>
            <h2 style='color: #c0392b;'>Payment Failed</h2>
            <p>Hi {$name},</p>
            <p>Your payment for order <strong>#{$order_id}</strong> did not go through or was cancelled.</p>
            <p>If this was a mistake or you'd like to try again, please visit your cart and proceed to checkout.</p>
            <p>Need help? Contact us at <a href='mailto:support@pishonserv.com'>support@pishonserv.com</a></p>
            <p style='margin-top: 20px;'>PISHONSERV Team</p>
        </div>
    ";
    $mail->AltBody = "Hi {$name}, your payment for order #{$order_id} failed. Please try again or contact support.";

    $mail->send();
    error_log("[FAILURE EMAIL] Sent to $email for order #$order_id");
} catch (Exception $e) {
    error_log("[FAILURE EMAIL ERROR] " . $mail->ErrorInfo);
}

// Show styled failure page
header("Location: payment_failed.php?order_id=$order_id");
exit();
