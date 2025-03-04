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
$response = curl_exec($ch);
curl_close($ch);

$paystack_response = json_decode($response, true);

// Check if payment was successful
if (!$paystack_response['status'] || $paystack_response['data']['status'] !== 'success') {
    $_SESSION['error'] = "Payment verification failed.";
    header("Location: payment_failed.php");
    exit();
}

// Get transaction details
$amount_paid = $paystack_response['data']['amount'] / 100; // Convert back from kobo
$email = $paystack_response['data']['customer']['email'];
$transaction_id = $paystack_response['data']['reference'];

// Get transaction from database
$stmt = $conn->prepare("SELECT * FROM payments WHERE transaction_id = ? AND status = 'pending'");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    $_SESSION['error'] = "Transaction not found or already processed.";
    header("Location: payment_failed.php");
    exit();
}

$user_id = $transaction['user_id'];
$property_id = $transaction['property_id'];
$total_amount = $transaction['amount'];

// Fetch property & owner details
$stmt = $conn->prepare("SELECT p.owner_id, u.role FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    $_SESSION['error'] = "Property not found.";
    header("Location: payment_failed.php");
    exit();
}

$owner_id = $property['owner_id'];

// Split Payment Logic
$platform_fee = 0.20 * $total_amount; // 20% to Superadmin
$owner_amount = 0.80 * $total_amount; // 80% to Owner/Agent/Hotel Owner

// Update payment status
$stmt = $conn->prepare("UPDATE payments SET status = 'completed' WHERE transaction_id = ?");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();

// Credit Owner's Wallet
$stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
$stmt->bind_param("di", $owner_amount, $owner_id);
$stmt->execute();

// Credit Superadmin Wallet (Assuming Superadmin has `id = 9`)
$superadmin_id = 9;
$stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
$stmt->bind_param("di", $platform_fee, $superadmin_id);
$stmt->execute();

// Log Transaction for Owner
$stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'credit', 'completed', ?)");
$desc_owner = "Property sale earnings for Property ID: $property_id";
$stmt->bind_param("ids", $owner_id, $owner_amount, $desc_owner);
$stmt->execute();

// Log Transaction for Superadmin
$stmt->bind_param("ids", $superadmin_id, $platform_fee, $desc_owner);
$stmt->execute();

// Update Property Status
$stmt = $conn->prepare("UPDATE properties SET status = 'sold' WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();

// Store success message
$_SESSION['success'] = "Payment successful! Property has been purchased.";

// Redirect to Success Page with Reference
header("Location: payment_success.php?reference=$transaction_id&user_id=" . $_SESSION['user_id']);
exit();
