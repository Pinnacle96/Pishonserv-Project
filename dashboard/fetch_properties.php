<?php
include 'includes/db_connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

$query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image, 
          (SELECT COUNT(*) FROM wishlist w WHERE w.user_id = ? AND w.property_id = p.id) AS is_wishlist
          FROM properties p 
          JOIN users u ON p.owner_id = u.id
          WHERE p.admin_approved = 1
          ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

echo json_encode($properties);
?>