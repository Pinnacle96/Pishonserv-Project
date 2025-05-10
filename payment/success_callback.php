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

// Fetch payment and determine the gateway
$stmt = $conn->prepare("SELECT pp.id AS payment_id, pp.order_id, pp.payment_method, po.user_id, po.status AS order_status, u.email, u.name 
    FROM product_payments pp 
    JOIN product_orders po ON pp.order_id = po.id 
    JOIN users u ON po.user_id = u.id 
    WHERE pp.reference = ? LIMIT 1");
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
$payment_method = $row['payment_method'];

// 🔐 VERIFY BASED ON GATEWAY
$verified = false;

if ($payment_method === 'flutterwave') {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref={$reference}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . FLW_SECRET_KEY
        ],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $api_response = curl_exec($curl);
    curl_close($curl);

    $verify = json_decode($api_response, true);
    $verified = isset($verify['status'], $verify['data']) && $verify['status'] === 'success' && $verify['data']['status'] === 'successful';
} elseif ($payment_method === 'paystack') {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . urlencode($reference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY
        ],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $api_response = curl_exec($curl);
    curl_close($curl);

    $verify = json_decode($api_response, true);
    $verified = isset($verify['status'], $verify['data']) && $verify['status'] === true && $verify['data']['status'] === 'success';
} else {
    die("Unsupported payment gateway.");
}

if (!$verified) {
    die("Payment verification failed or incomplete.");
}

// ✅ Update payment and order
$update_payment = $conn->prepare("UPDATE product_payments SET payment_status = 'successful', paid_at = NOW() WHERE id = ?");
$update_payment->bind_param("i", $payment_id);
$update_payment->execute();

$update_order = $conn->prepare("UPDATE product_orders SET status = 'paid' WHERE id = ?");
$update_order->bind_param("i", $order_id);
$update_order->execute();

// Delete cart items
$conn->query("DELETE FROM cart_items WHERE user_id = $user_id");
unset($_SESSION['cart']);

// 📧 Send confirmation email
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
            <h2 style='color: #092468;'>Dear {$name},</h2>
            <p>Thank you for your payment! Your order <strong>#{$order_id}</strong> has been successfully confirmed.</p>
            <p>You can track your order here:</p>
            <p style='text-align: center; margin: 20px 0;'>
                <a href='https://pishonserv.com/track_order.php?order_id={$order_id}' style='background-color: #092468; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Track My Order</a>
            </p>
            <p>If you have any questions, feel free to contact us at <a href='mailto:support@pishonserv.com'>support@pishonserv.com</a>.</p>
            <p>Best regards,<br><strong>PISHONSERV Team</strong></p>
        </div>
        <div style='text-align: center; font-size: 12px; color: #777; margin-top: 20px;'>&copy; " . date('Y') . " PISHONSERV. All rights reserved.</div>
    </div>";

    $mail->AltBody = "Thank you for your payment! Your order #{$order_id} has been confirmed.\nTrack it here: https://pishonserv.com/track_order.php?order_id={$order_id}";

    $mail->send();
    fwrite($log_handle, "[" . date('Y-m-d H:i:s') . "] ✅ Email sent to {$email}\n");
    ob_end_clean();
    fclose($log_handle);

    header("Location: ../thank_you.php?order_id=$order_id");
    exit();
} catch (Exception $e) {
    fwrite($log_handle, "[" . date('Y-m-d H:i:s') . "] ❌ Email error to {$email}: {$mail->ErrorInfo}\n");
    ob_end_clean();
    fclose($log_handle);
    header("Location: ../thank_you.php?order_id=$order_id");
    exit();
}
