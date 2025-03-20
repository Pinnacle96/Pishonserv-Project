<?php
session_start();
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);
$property_id = isset($data['property_id']) ? (int)$data['property_id'] : 0;
$action = isset($data['action']) ? $data['action'] : '';
$user_id = (int)$_SESSION['user_id'];

if ($property_id <= 0 || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Check current wishlist status
$check_query = "SELECT id FROM wishlist WHERE user_id = $user_id AND property_id = $property_id";
$check_result = $conn->query($check_query);
$isInWishlist = $check_result->num_rows > 0;

if ($action === 'add' && !$isInWishlist) {
    // Add to wishlist
    $insert_query = "INSERT INTO wishlist (user_id, property_id) VALUES ($user_id, $property_id)";
    if ($conn->query($insert_query)) {
        echo json_encode(['success' => true, 'inWishlist' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
} elseif ($action === 'remove' && $isInWishlist) {
    // Remove from wishlist
    $delete_query = "DELETE FROM wishlist WHERE user_id = $user_id AND property_id = $property_id";
    if ($conn->query($delete_query)) {
        echo json_encode(['success' => true, 'inWishlist' => false]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
    }
} else {
    // No action needed (already in desired state)
    echo json_encode(['success' => true, 'inWishlist' => $isInWishlist]);
}

$conn->close();
