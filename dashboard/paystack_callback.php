<?php
session_start();
include '../includes/config.php';
include '../includes/db_connect.php';

// Paystack Secret Key
$paystack_secret_key = PAYSTACK_SECRET_KEY;

// Get Paystack transaction reference
$reference = $_GET['reference'] ?? '';

if (!$reference) {
    $_SESSION['error'] = "Invalid transaction reference.";
    error_log("Invalid reference: " . print_r($_GET, true));
    header("Location: payment_failed.php");
    exit();
}

// Verify Paystack payment
$paystack_url = "https://api.paystack.co/transaction/verify/{$reference}";
$headers = [
    "Authorization: Bearer $paystack_secret_key",
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paystack_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
if (curl_error($ch)) {
    $error = "cURL Error: " . curl_error($ch);
    error_log($error);
    $_SESSION['error'] = $error;
    header("Location: payment_failed.php");
    exit();
}
curl_close($ch);

$paystack_response = json_decode($response, true);
error_log("Paystack Response: " . $response);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['error'] = "JSON Decode Error: " . json_last_error_msg() . ". Response: " . $response;
    error_log($_SESSION['error']);
    header("Location: payment_failed.php");
    exit();
}

if (!$paystack_response['status'] || $paystack_response['data']['status'] !== 'success') {
    $error_message = $paystack_response['message'] ?? 'Unknown error';
    $_SESSION['error'] = "Payment verification failed: $error_message";
    error_log($_SESSION['error']);
    header("Location: payment_failed.php");
    exit();
}

// Get transaction details
$amount_paid = $paystack_response['data']['amount'] / 100;
$email = $paystack_response['data']['customer']['email'];
$transaction_id = $paystack_response['data']['reference'];

// Fetch transaction from the database
$stmt = $conn->prepare("SELECT * FROM payments WHERE transaction_id = ? AND status = 'pending'");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    $_SESSION['error'] = "Transaction not found or already processed.";
    error_log($_SESSION['error']);
    header("Location: payment_failed.php");
    exit();
}

$user_id = $transaction['user_id'];
$property_id = $transaction['property_id'];
$total_amount = $transaction['amount'];

$stmt = $conn->prepare("SELECT p.owner_id, u.role FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    $_SESSION['error'] = "Property not found.";
    error_log($_SESSION['error']);
    header("Location: payment_failed.php");
    exit();
}

$owner_id = $property['owner_id'];
$platform_fee = 0.20 * $total_amount;
$owner_amount = 0.80 * $total_amount;

// Update payment status
$stmt = $conn->prepare("UPDATE payments SET status = 'completed' WHERE transaction_id = ?");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();

// Update wallet for the property owner
$stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("di", $owner_amount, $owner_id);
    if (!$stmt->execute()) {
        error_log("Failed to update owner wallet: " . $stmt->error);
    } else {
        error_log("Owner wallet updated: +₦" . $owner_amount . " for user_id=" . $owner_id);
    }
} else {
    error_log("Prepare failed for owner wallet update: " . $conn->error);
}

// Update wallet for superadmin
$stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("di", $platform_fee, $superadmin_id);
    if (!$stmt->execute()) {
        error_log("Failed to update superadmin wallet: " . $stmt->error);
    } else {
        error_log("Superadmin wallet updated: +₦" . $platform_fee . " for user_id=" . $superadmin_id);
    }
} else {
    error_log("Prepare failed for superadmin wallet update: " . $conn->error);
}


// Fetch the current type of the property
$stmt = $conn->prepare("SELECT listing_type FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    error_log("Property not found for id: " . $property_id);
    $_SESSION['error'] = "Property not found.";
    header("Location: payment_failed.php");
    exit();
}

$current_type = $property['listing_type'];
$new_status = '';

// Determine the new status based on the property type
switch ($current_type) {
    case 'for_sale':
        $new_status = 'sold';
        break;
    case 'for_rent':
        $new_status = 'rented';
        break;
    case 'short_let':
    case 'hotel':
        $new_status = 'booked';
        break;
    default:
        error_log("Unknown property type: " . $current_type);
        $_SESSION['error'] = "Invalid property type.";
        header("Location: payment_failed.php");
        exit();
}

// Update the property status
$stmt = $conn->prepare("UPDATE properties SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $property_id);
if ($stmt->execute()) {
    error_log("Property status updated to '$new_status' for property_id=$property_id");
} else {
    error_log("Failed to update property status: " . $stmt->error);
}


// Store success message
$_SESSION['success'] = "Payment successful! Property has been purchased.";

// Log session details for debugging
error_log("Session data before redirect: " . print_r($_SESSION, true));

// Recover user_id if not set
if (!isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        error_log("Recovered user_id: " . $_SESSION['user_id']);
    } else {
        error_log("User not found for email: $email");
        $_SESSION['error'] = "User not found. Please log in.";
        header("Location: payment_failed.php");
        exit();
    }
}

// Log redirect URL
$redirect_url = "payment_success.php?reference=$transaction_id&user_id=" . $_SESSION['user_id'];
error_log("Redirect URL: " . $redirect_url);

// Redirect to payment_success.php with user_id
header("Location: " . $redirect_url);
exit();
