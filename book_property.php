<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/book_property_error.log'); // Save into a separate error log
include 'includes/db_connect.php';
include 'includes/zoho_functions.php';

// ✅ Restore Booking Data After Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['booking_data'] = $_POST;  // Store all booking data
    $_SESSION['redirect_after_login'] = "book_property.php?property_id=" . $_POST['property_id'];
    setcookie("redirect_after_login", "book_property.php?property_id=" . $_POST['property_id'], time() + 3600, "/");

    $_SESSION['error'] = "You must log in to book a property.";
    header("Location: auth/login.php");
    exit();
}

// 🚀 Restore Data After Login
if (isset($_SESSION['booking_data'])) {
    $_POST = array_merge($_POST, $_SESSION['booking_data']); // Merge stored data into POST request
    unset($_SESSION['booking_data']); // Clear session data
}

// ✅ Check if request is GET or POST
$property_id = $_POST['property_id'] ?? $_GET['property_id'] ?? null;
$check_in = $_POST['check_in_date'] ?? $_GET['check_in'] ?? null;
$check_out = $_POST['check_out_date'] ?? $_GET['check_out'] ?? null;

// 🚨 Validate Property ID
if (!$property_id || !filter_var($property_id, FILTER_VALIDATE_INT)) {
    $_SESSION['error'] = "Invalid property selection.";
    header("Location: properties.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 🚀 Fetch Property Details (Only Approved Properties)
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND admin_approved = 1");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 🚨 Ensure Property Exists & Is Approved
if (!$property) {
    $_SESSION['error'] = "Property not found or not approved.";
    header("Location: properties.php");
    exit();
}

// 🚨 Prevent Owners From Booking Their Own Property
if ($property['owner_id'] == $user_id) {
    $_SESSION['error'] = "You cannot book your own property.";
    header("Location: property.php?id=$property_id");
    exit();
}

$type = $property['listing_type'];
$status = 'pending';
$total_price = $property['price']; // Default price

// ✅ Handle Shortlet & Hotel Bookings
if ($type === 'short_let' || $type === 'hotel') {
    if (!$check_in || !$check_out) {
        $_SESSION['error'] = "Please select check-in and check-out dates.";
        header("Location: property.php?id=$property_id");
        exit();
    }

    // 🚨 Validate Date Format
    $check_in_date = DateTime::createFromFormat('Y-m-d', $check_in);
    $check_out_date = DateTime::createFromFormat('Y-m-d', $check_out);

    if (!$check_in_date || !$check_out_date) {
        $_SESSION['error'] = "Invalid date format.";
        header("Location: property.php?id=$property_id");
        exit();
    }

    // 🚨 Ensure Check-out is After Check-in
    if ($check_in_date >= $check_out_date) {
        $_SESSION['error'] = "Check-out date must be after check-in date.";
        header("Location: property.php?id=$property_id");
        exit();
    }

    // ✅ Calculate Booking Duration
    $days = $check_in_date->diff($check_out_date)->days;
    $total_price = $property['price'] * $days;

    // 🚨 Check for Existing Bookings in Selected Dates
    $stmt = $conn->prepare("SELECT id FROM bookings 
                            WHERE property_id = ? 
                            AND status IN ('booked', 'reserved') 
                            AND NOT (check_out_date <= ? OR check_in_date >= ?)");
    $stmt->bind_param("iss", $property_id, $check_in, $check_out);
    $stmt->execute();
    $already_booked = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($already_booked) {
        $_SESSION['error'] = "This property is already booked for the selected dates.";
        header("Location: property.php?id=$property_id");
        exit();
    }
}

// 🚀 Insert Booking Into Database
$stmt = $conn->prepare("INSERT INTO bookings (user_id, property_id, check_in_date, check_out_date, duration, amount, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissids", $user_id, $property_id, $check_in, $check_out, $days, $total_price, $status);

if ($stmt->execute()) {
    $stmt->close();

    // ✅ Fetch Last Booking for this User and Property
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND property_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $stmt->bind_result($booking_id);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Error: Booking creation failed. " . $stmt->error);
}

// 🚨 Debugging Step: Check if Booking ID is Captured
if (!isset($booking_id) || empty($booking_id)) {
    die("Error: Booking ID is missing. The booking was not created properly.");
}
// 🚀 Verify that booking_id exists in database
$stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$stmt->bind_result($found_booking_id);
$stmt->fetch();
$stmt->close();

// 🚨 Debugging: Log if booking was found
if (!$found_booking_id) {
    error_log("❌ Booking ID $booking_id not found in database for user $user_id.");
    die("Error: Booking not found. Please try again.");
}


// ✅ Sync Booking to Zoho CRM
$zoho_booking_id = createZohoBooking($user_id, $property_id, $booking_id, $status, $check_in, $check_out, $days, $total_price);
if (!$zoho_booking_id) {
    $_SESSION['error'] = "Failed to sync booking with CRM.";
}

// // ✅ Update Property Status
// $property_status = ($type === 'short_let') ? 'booked' : (($type === 'hotel') ? 'reserved' : 'available');
// $stmt = $conn->prepare("UPDATE properties SET status = ? WHERE id = ?");
// $stmt->bind_param("si", $property_status, $property_id);
// $stmt->execute();
// $stmt->close();

// ✅ Redirect to Checkout for Shortlet/Hotel Payments
if ($type === 'short_let' || $type === 'hotel') {
    // 🚀 Debugging Step: Log Booking ID before redirect
    if (!isset($booking_id) || empty($booking_id)) {
        error_log("❌ Booking ID is empty before redirect.");
        die("Error: Booking ID is missing. The booking was not created properly.");
    }

    // ✅ Redirect to Checkout
    $redirect_url = "dashboard/checkout.php?property_id=$property_id&check_in=" . urlencode($check_in) . "&check_out=" . urlencode($check_out) . "&booking_id=" . $booking_id;
    error_log("🔄 Redirecting to: " . $redirect_url);
    header("Location: " . $redirect_url);
    exit();
}
// ✅ Success Message for Sale & Rent
$_SESSION['success'] = "Booking request submitted. Our team will contact you.";
header("Location: dashboard/buyer_dashboard.php");
exit();
