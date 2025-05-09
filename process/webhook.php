<?php
session_start();
include '../includes/db_connect.php';
include '../includes/config.php';  // Your config file containing PAYSTACK_SECRET_KEY

$logDir = '../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Get Webhook Raw Input
$input = file_get_contents("php://input");

// Log the raw webhook data
file_put_contents("$logDir/paystack_webhook.log", date("Y-m-d H:i:s") . " - " . $input . PHP_EOL, FILE_APPEND);

// Verify Signature from Paystack
$signature = (isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '';
if (!$signature) {
    http_response_code(400);
    exit('Signature not provided');
}

if (hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY) !== $signature) {
    http_response_code(400);
    exit('Invalid signature');
}

// Decode Payload
$event = json_decode($input, true);
if (!$event || !isset($event['event'])) {
    http_response_code(400);
    exit('Invalid event structure');
}

$reference = $event['data']['reference'] ?? null;

if (!$reference) {
    http_response_code(400);
    exit('Missing transaction reference');
}

// Check payment record
$stmt = $conn->prepare("SELECT * FROM payments WHERE transaction_id = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$payment_result = $stmt->get_result();

if ($payment_result->num_rows === 0) {
    // Log unmatched transaction
    file_put_contents("$logDir/unmatched_webhook.log", date("Y-m-d H:i:s") . " - Payment not found for: " . $reference . PHP_EOL, FILE_APPEND);
    http_response_code(200);
    exit('Payment not found, logged');
}

$payment = $payment_result->fetch_assoc();

// Check event type
if ($event['event'] == 'charge.success') {

    // Update payments table
    $stmt = $conn->prepare("UPDATE payments SET status='completed' WHERE transaction_id = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();

    // Update bookings table
    $stmt = $conn->prepare("UPDATE bookings SET payment_status='paid' WHERE user_id = ? AND property_id = ?");
    $stmt->bind_param("ii", $payment['user_id'], $payment['property_id']);
    $stmt->execute();

    http_response_code(200);
    exit('Payment verified and updated');
}

http_response_code(200);
echo 'Webhook received but no action taken';
<<<<<<< HEAD

?>
=======
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
