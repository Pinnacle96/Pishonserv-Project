<?php
session_start();
include 'includes/db_connect.php';

// Response Helper
function sendResponse($success, $message = '', $inWishlist = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'inWishlist' => $inWishlist
    ]);
    exit;
}

// Check User Auth
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'User not logged in');
}

$data = json_decode(file_get_contents('php://input'), true);
$property_id = isset($data['property_id']) ? (int)$data['property_id'] : 0;
$action = isset($data['action']) ? $data['action'] : '';
$user_id = (int)$_SESSION['user_id'];

// Validate Input
if ($property_id <= 0 || !in_array($action, ['add', 'remove'])) {
    sendResponse(false, 'Invalid request');
}

// Check Wishlist Status
$check_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?");
$check_stmt->bind_param('ii', $user_id, $property_id);
$check_stmt->execute();
$check_stmt->store_result();
$isInWishlist = $check_stmt->num_rows > 0;
$check_stmt->close();

if ($action === 'add' && !$isInWishlist) {
    $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, property_id) VALUES (?, ?)");
    $insert_stmt->bind_param('ii', $user_id, $property_id);
    if ($insert_stmt->execute()) {
        sendResponse(true, 'Added to wishlist', true);
    } else {
        sendResponse(false, 'Failed to add to wishlist');
    }
} elseif ($action === 'remove' && $isInWishlist) {
    $delete_stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND property_id = ?");
    $delete_stmt->bind_param('ii', $user_id, $property_id);
    if ($delete_stmt->execute()) {
        sendResponse(true, 'Removed from wishlist', false);
    } else {
        sendResponse(false, 'Failed to remove from wishlist');
    }
} else {
    // No Action Needed
    sendResponse(true, '', $isInWishlist);
}

$conn->close();
