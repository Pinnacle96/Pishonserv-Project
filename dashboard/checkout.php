<?php
session_start();

// 🚀 Enable Full Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'errors.log'); // Logs errors to a file

include '../includes/config.php';
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Import Zoho CRM functions

// 🚀 Debugging: Log the URL for troubleshooting
error_log("🔍 Entering checkout.php with URL: " . $_SERVER['REQUEST_URI']);

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    error_log("❌ User not logged in. Redirecting to login.");
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$property_id = $_GET['property_id'] ?? null;
$check_in = $_GET['check_in'] ?? null;
$check_out = $_GET['check_out'] ?? null;
$booking_id = $_GET['booking_id'] ?? null;

// 🚨 Validate Booking Details
if (!$property_id || !$check_in || !$check_out || !$booking_id) {
    error_log("❌ Missing booking details.");
    die("Error: Missing booking details.");
}

// ✅ Convert dates & calculate duration
$check_in_date = new DateTime($check_in);
$check_out_date = new DateTime($check_out);
$interval = $check_in_date->diff($check_out_date);
$days_booked = $interval->days;

if ($days_booked < 1) {
    die("Error: Booking duration must be at least 1 day.");
}

// ✅ Fetch existing booking instead of inserting a new one
$stmt = $conn->prepare("SELECT amount, duration FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    die("Error: Booking not found.");
}

$total_amount = $booking['amount'];
$days_booked = $booking['duration'];

// ✅ Fetch property & owner details
$stmt = $conn->prepare("SELECT p.*, u.id AS owner_id, u.email AS owner_email 
                        FROM properties p 
                        JOIN users u ON p.owner_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    die("Error: Property not found.");
}

$type = $property['listing_type'];
$daily_price = $property['price'];
$owner_id = $property['owner_id'];

// ✅ Prevent Overlapping Bookings (Now only considers confirmed bookings)
error_log("🔍 Checking overlapping bookings for Property ID: $property_id, Check-in: $check_in, Check-out: $check_out");

$stmt = $conn->prepare("SELECT id, check_in_date, check_out_date, status FROM bookings 
                        WHERE property_id = ? 
                        AND status = 'confirmed' 
                        AND NOT (check_out_date <= ? OR check_in_date >= ?)");
$stmt->bind_param("iss", $property_id, $check_in, $check_out);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    error_log("⚠️ Found existing booking: ID: {$row['id']}, Status: {$row['status']}, Check-in: {$row['check_in_date']}, Check-out: {$row['check_out_date']}");
}

if ($result->num_rows > 0) {
    $_SESSION['error'] = "This property is already booked for the selected dates.";
    error_log("❌ Property already booked: $property_id");
    die("Error: This property is already booked for the selected dates.");
}

$stmt->close();

// ✅ Fetch user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$email = $user['email'] ?? die("Error: User email not found.");

// ✅ Secure Unique Transaction Reference
$reference = "TXN_" . bin2hex(random_bytes(10));

// ✅ Insert Transaction in Payments Table
$stmt = $conn->prepare("INSERT INTO payments (user_id, property_id, amount, transaction_id, status) 
                        VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("iids", $user_id, $property_id, $total_amount, $reference);

if (!$stmt->execute()) {
    die("Error: Payment record creation failed. " . $stmt->error);
}
$stmt->close();

// ✅ Sync booking to Zoho CRM
$zoho_booking_id = createZohoBooking($user_id, $property_id, 'pending', $check_in, $check_out, $days_booked, $total_amount);
if (!$zoho_booking_id) {
    error_log("⚠️ Warning: Failed to sync booking with CRM.");
}

// ✅ Process Payment with Paystack
if ($type === 'short_let' || $type === 'hotel') {
    $callback_url = "https://db72-102-88-43-114.ngrok-free.app/pishonserv.com/dashboard/paystack_callback.php";
    $paystack_url = "https://api.paystack.co/transaction/initialize";

    // 🚀 Debugging: Verify Paystack API Key
    error_log("🔑 Paystack Secret Key: " . (defined('PAYSTACK_SECRET_KEY') ? 'SET' : 'NOT SET'));

    $fields = [
        'email' => $email,
        'amount' => $total_amount * 100, // Convert to kobo
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

    // 🚀 Capture HTTP response code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $paystack_response = json_decode($response, true);

    // 🚨 Debugging: Log Paystack API response
    error_log("🔍 Paystack API HTTP Code: " . $http_code);
    error_log("🔍 Paystack Response: " . json_encode($paystack_response));

    if (isset($paystack_response['status']) && $paystack_response['status']) {
        error_log("✅ Paystack Payment Initialized: Redirecting to " . $paystack_response['data']['authorization_url']);
        header("Location: " . $paystack_response['data']['authorization_url']);
        exit();
    } else {
        error_log("❌ Paystack Payment Error: " . json_encode($paystack_response));
        echo "<h3>Error: Unable to initialize payment.</h3>";
        echo "<pre>" . json_encode($paystack_response, JSON_PRETTY_PRINT) . "</pre>";
        exit();
    }
} else {
    // ✅ For Sale & Rent properties (No online payment)
    $_SESSION['success'] = "Booking successful! Our team will contact you.";
    header("Location: ../dashboard/buyer_dashboard.php");
    exit();
}