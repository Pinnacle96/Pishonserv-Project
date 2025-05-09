<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['ref'])) {
    die("Invalid payment reference.");
}

$reference = $_GET['ref'];

// Fetch the order and payment by reference
$stmt = $conn->prepare("SELECT pp.id AS payment_id, pp.order_id, po.user_id, po.status AS order_status, u.email, u.name FROM product_payments pp JOIN product_orders po ON pp.order_id = po.id JOIN users u ON po.user_id = u.id WHERE pp.payment_status = 'pending' AND pp.reference = ? LIMIT 1");
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Payment not found or already processed.");
}

$row = $result->fetch_assoc();
$payment_id = $row['payment_id'];
$order_id = $row['order_id'];
$user_id = $row['user_id'];
$email = $row['email'];
$name = $row['name'];

// Update payment status
$update_payment = $conn->prepare("UPDATE product_payments SET payment_status = 'successful', paid_at = NOW() WHERE id = ?");
$update_payment->bind_param("i", $payment_id);
$update_payment->execute();

// Update order status
$update_order = $conn->prepare("UPDATE product_orders SET status = 'paid' WHERE id = ?");
$update_order->bind_param("i", $order_id);
$update_order->execute();

// Delete cart items
$conn->query("DELETE FROM cart_items WHERE user_id = $user_id");

// Clear session cart
unset($_SESSION['cart']);

// Setup log file
$log_file = '../logs/email_errors.log';
$log_handle = fopen($log_file, 'a');

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtppro.zoho.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pishonserv@pishonserv.com';
    $mail->Password = 'Serv@4321@Ikeja';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->SMTPDebug = 0;

    ob_start();

    $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'PISHONSERV - Order Confirmation';

    $mail->Body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f4f4f4;'>
    <div style='text-align: center;'>
        <img src='https://pishonserv.com/public/images/logo.png' alt='PISHONSERV Logo' style='width: 150px; margin-bottom: 20px;'>
    </div>
    <div style='background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
        <h2 style='color: #092468; margin-bottom: 10px;'>Dear {$name},</h2>
        <p style='font-size: 16px; color: #333;'>We are pleased to confirm that we have received your payment and your order <strong>#{$order_id}</strong> has been successfully processed.</p>
        <p style='font-size: 16px; color: #333;'>Our team is now preparing your items for delivery. You will receive an update once your order has been dispatched.</p>

        <div style='text-align: center; margin: 30px 0;'>
            <a href='https://pishonserv.com/track_order.php?order_id={$order_id}' style='background-color: #092468; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-size: 16px;'>Track My Order</a>
        </div>

        <p style='font-size: 16px; color: #333;'>If you have any questions or need assistance, feel free to contact our support team at <a href='mailto:support@pishonserv.com' style='color: #092468;'>support@pishonserv.com</a>.</p>
        <p style='font-size: 16px; color: #333; margin-top: 20px;'>Thank you for choosing <strong>PISHONSERV</strong>. We value your trust and look forward to serving you again.</p>
        <p style='font-size: 16px; color: #333; margin-top: 20px;'>Best regards,<br><strong>The PISHONSERV Team</strong></p>
    </div>
    <div style='text-align: center; margin-top: 20px; font-size: 12px; color: #777;'>
        &copy; " . date('Y') . " PISHONSERV. All rights reserved.
    </div>
</div>";

    $mail->AltBody = "Dear {$name},\n\nWe are pleased to confirm that we have received your payment and your order #{$order_id} has been successfully processed.\n\nYou can track your order here: https://pishonserv.com/track_order.php?order_id={$order_id}\n\nIf you have any questions, please contact us at support@pishonserv.com.\n\nThank you for choosing PISHONSERV.\n\nBest regards,\nThe PISHONSERV Team";

    $mail->send();

    $log_message = "[" . date('Y-m-d H:i:s') . "] ✅ Email sent to {$email}\n";
    fwrite($log_handle, $log_message);

    ob_end_clean();
    fclose($log_handle);
    header("Location: ../thank_you.php?order_id=$order_id");
    exit();
} catch (Exception $e) {
    $error_message = "[" . date('Y-m-d H:i:s') . "] ❌ Email error to {$email}: {$mail->ErrorInfo}\n";
    fwrite($log_handle, $error_message);

    ob_end_clean();
    fclose($log_handle);
    header("Location: ../thank_you.php?order_id=$order_id");
    exit();
}