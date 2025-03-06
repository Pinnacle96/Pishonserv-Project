<?php
session_start();
include '../includes/config.php';
include '../includes/db_connect.php';

// If user is not logged in, store the current page and redirect to login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI']; // Save current page URL
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$property_id = $_GET['property_id'] ?? null;

if (!$property_id) {
    die("Invalid property.");
}

// Fetch property & owner details
$stmt = $conn->prepare("SELECT p.*, u.id AS owner_id, u.email AS owner_email FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

$amount = $property['price'];
$owner_id = $property['owner_id'];
$reference = "TXN_" . uniqid(); // Unique transaction ID

// Fetch user email if not in session
if (!isset($_SESSION['email'])) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $_SESSION['email'] = $user['email'] ?? die("User email not found.");
}

$email = $_SESSION['email'];

// Insert pending transaction into database
$stmt = $conn->prepare("INSERT INTO payments (user_id, property_id, amount, payment_gateway, transaction_id, status) VALUES (?, ?, ?, 'paystack', ?, 'pending')");
$stmt->bind_param("iids", $user_id, $property_id, $amount, $reference);
$stmt->execute();

// Paystack API Configuration
$callback_url = "https://024d-2c0f-f5c0-600-4868-8581-5b86-70e0-ec1e.ngrok-free.app/pishonserv.com/dashboard/paystack_callback.php"; // Ensure this is correct
$paystack_url = "https://api.paystack.co/transaction/initialize";

$fields = [
    'email' => $email,
    'amount' => $amount * 100, // Convert to kobo
    'callback_url' => $callback_url,
    'reference' => $reference
];

$headers = [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paystack_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Disable SSL verification (for local testing only)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Debug: Enable verbose output for cURL
curl_setopt($ch, CURLOPT_VERBOSE, true);
$response = curl_exec($ch);

// Check for cURL errors
if (curl_error($ch)) {
    die("cURL Error: " . curl_error($ch));
}

// Debug: Log or display the raw response
error_log("Paystack Response: " . $response); // Log to error log
var_dump($response); // Display in browser for testing

curl_close($ch);

$paystack_response = json_decode($response, true);

// Check if json_decode failed
if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON Decode Error: " . json_last_error_msg() . ". Response: " . $response);
}

// Handle Paystack Response
if (isset($paystack_response['status']) && $paystack_response['status']) {
    header("Location: " . $paystack_response['data']['authorization_url']);
    exit();
} else {
    $error_message = isset($paystack_response['message']) ? $paystack_response['message'] : 'Unknown error';
    die("Payment failed: " . $error_message . ". Response: " . json_encode($paystack_response));
}