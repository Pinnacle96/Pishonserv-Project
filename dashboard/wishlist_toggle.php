<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$propertyId = isset($data['property_id']) ? intval($data['property_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

if (!$propertyId || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, property_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $propertyId);
    } else {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND property_id = ?");
        $stmt->bind_param("ii", $userId, $propertyId);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'inWishlist' => ($action === 'add')]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
