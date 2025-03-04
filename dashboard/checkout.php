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
$callback_url = "  https://20e9-102-88-33-213.ngrok-free.app/pishonserv.com/dashboard/paystack_callback.php"; // Change to your actual domain
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
$response = curl_exec($ch);
curl_close($ch);

$paystack_response = json_decode($response, true);

// Handle Paystack Response
if ($paystack_response['status']) {
    header("Location: " . $paystack_response['data']['authorization_url']);
    exit();
} else {
    die("Payment failed: " . $paystack_response['message']);
}
