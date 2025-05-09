<?php
require_once 'includes/db_connect.php';

if (!isset($_GET['order_id'])) {
    die('Invalid order ID.');
}

$order_id = (int)$_GET['order_id'];

$stmt = $conn->prepare("SELECT status, tracking_number, updated_at FROM product_orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Order not found.');
}

$order = $result->fetch_assoc();

echo "<h2>Order #{$order_id} Status</h2>";
echo "<p>Status: <strong>{$order['status']}</strong></p>";
if (!empty($order['tracking_number'])) {
    echo "<p>Tracking Number: {$order['tracking_number']}</p>";
}
echo "<p>Last Updated: {$order['updated_at']}</p>";