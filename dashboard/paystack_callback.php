<?php
session_start();

// ✅ Enable Full Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// ✅ Ensure logs folder exists
$log_dir = "../logs";
$log_file = $log_dir . "/paystack_errors.log";

if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true); // Create logs directory if it doesn't exist
}

// ✅ Set PHP to log errors
ini_set('error_log', $log_file);

// ✅ Debugging: Log Paystack Callback Trigger
error_log("🔍 Paystack Callback Triggered with reference: " . ($_GET['reference'] ?? 'NO REFERENCE'));

// ✅ Check if Reference Exists
$reference = $_GET['reference'] ?? '';
if (!$reference) {
    $_SESSION['error'] = "Invalid transaction reference.";
    error_log("❌ Invalid transaction reference received.");
    header("Location: ../dashboard/payment_failed.php");
    exit();
}

// ✅ Include Database & Config
include '../includes/config.php';
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Import Zoho API functions

// ✅ Fetch Transaction (Ensure it's still pending)
$stmt = $conn->prepare("SELECT * FROM payments WHERE transaction_id = ? AND status = 'pending'");
$stmt->bind_param("s", $reference);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$transaction) {
    $_SESSION['error'] = "Transaction not found or already processed.";
    error_log("❌ Transaction not found or already processed.");
    header("Location: ../dashboard/payment_failed.php");
    exit();
}

$user_id = $transaction['user_id'];
$property_id = $transaction['property_id'];
$total_amount = $transaction['amount'];

// ✅ Fetch Property, Owner, and Zoho Details
$stmt = $conn->prepare("SELECT p.owner_id, p.listing_type, p.zoho_property_id, u.zoho_contact_id 
                        FROM properties p 
                        JOIN users u ON p.owner_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    error_log("❌ Property not found for ID: $property_id");
    die("Error: Property not found.");
}

$owner_id = $property['owner_id'];
$listing_type = $property['listing_type'];
$zoho_property_id = $property['zoho_property_id'];
$zoho_contact_id = $property['zoho_contact_id'];

// ✅ Verify Payment with Paystack API
$paystack_secret_key = PAYSTACK_SECRET_KEY;
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
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$paystack_response = json_decode($response, true);

// ✅ Debugging: Log Paystack Response
error_log("🔍 Paystack API HTTP Code: " . $http_code);
error_log("🔍 Paystack Response: " . json_encode($paystack_response, JSON_PRETTY_PRINT));

// 🚨 Ensure Payment is Successful
if (!$paystack_response['status'] || $paystack_response['data']['status'] !== 'success') {
    $_SESSION['error'] = "Payment verification failed.";
    error_log("❌ Paystack verification failed.");
    header("Location: ../dashboard/payment_failed.php");
    exit();
}

// ✅ Log Successful Payment
error_log("✅ Paystack Payment Verified Successfully");

// ✅ Calculate Earnings (80% to Owner, 20% to Platform)
$platform_fee = 0.20 * $total_amount;
$owner_earnings = 0.80 * $total_amount;
$superadmin_id = 9; // Replace with actual superadmin ID

// ✅ Get the `booking_id`
$stmt = $conn->prepare("SELECT id FROM bookings WHERE property_id = ? AND user_id = ?");
$stmt->bind_param("ii", $property_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    error_log("❌ Booking ID not found for property: $property_id and user: $user_id");
    die("Error: Booking ID not found.");
}

$booking_id = $booking['id'];

// 🚨 Begin Transaction to Ensure Atomicity
$conn->begin_transaction();

try {
    // ✅ Check if Wallet Exists for Owner
    $stmt = $conn->prepare("SELECT id FROM wallets WHERE user_id = ?");
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $wallet_exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($wallet_exists) {
        // Update existing wallet balance
        $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
        $stmt->bind_param("di", $owner_earnings, $owner_id);
    } else {
        // Insert new wallet record
        $stmt = $conn->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, ?)");
        $stmt->bind_param("id", $owner_id, $owner_earnings);
    }
    $stmt->execute();
    $stmt->close();

    // ✅ Update Platform Wallet (Superadmin Earnings)
    $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
    $stmt->bind_param("di", $platform_fee, $superadmin_id);
    $stmt->execute();
    $stmt->close();

    // ✅ Insert Transactions (Owner's Earnings & Platform Fee)
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type, type, status, description) 
                            VALUES (?, ?, 'booking', 'credit', 'completed', ?)");
    $description = "Earnings from property booking";
    $stmt->bind_param("ids", $owner_id, $owner_earnings, $description);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type, type, status, description) 
                            VALUES (?, ?, 'booking', 'credit', 'completed', ?)");
    $description = "Platform commission from booking";
    $stmt->bind_param("ids", $superadmin_id, $platform_fee, $description);
    $stmt->execute();
    $stmt->close();

    // ✅ Update Payment Status
    $stmt = $conn->prepare("UPDATE payments SET status = 'completed' WHERE transaction_id = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $stmt->close();

    // ✅ Update Booking Status to Confirmed
    $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();

    // ✅ Sync Payment & Booking with Zoho CRM
    if ($zoho_contact_id && $zoho_property_id) {
        updateZohoBookingStatus($zoho_contact_id, $zoho_property_id, 'confirmed');
    } else {
        error_log("⚠️ Warning: Zoho CRM IDs missing, unable to sync.");
    }

    // 🚀 Commit Transaction if Everything is Successful
    $conn->commit();

    // ✅ Debugging: Log Success Redirection
    error_log("✅ Redirecting to payment_success.php");

    // ✅ Redirect on Success
    $_SESSION['success'] = "Payment successful! Booking confirmed.";
    header("Location: ../dashboard/payment_success.php?reference=$reference");
    exit();
} catch (Exception $e) {
    // ❌ Rollback Changes on Failure
    $conn->rollback();

    error_log("❌ Transaction failed: " . $e->getMessage());

    $_SESSION['error'] = "Transaction failed. Please contact support.";
    header("Location: ../dashboard/payment_failed.php");
    exit();
}
