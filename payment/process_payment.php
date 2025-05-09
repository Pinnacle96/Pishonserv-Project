<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// Validate delivery and payment info
$required_fields = ['delivery_name', 'delivery_phone', 'delivery_address', 'delivery_city', 'delivery_state', 'delivery_country', 'payment_method'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

$delivery_name = $_POST['delivery_name'];
$delivery_phone = $_POST['delivery_phone'];
$delivery_email = $_POST['delivery_email'] ?? ($_SESSION['email'] ?? 'guest@example.com');
$delivery_address = $_POST['delivery_address'];
$delivery_city = $_POST['delivery_city'];
$delivery_state = $_POST['delivery_state'];
$delivery_country = $_POST['delivery_country'];
$payment_method = $_POST['payment_method'];
$order_description = $_POST['order_description'] ?? 'PishonServ Product Payment';
$delivery_fee = floatval($_POST['delivery_fee'] ?? 0);

// 1. Create the Order
$total_amount = 0;
foreach ($cart as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
$total_amount += $delivery_fee;

$stmt = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, delivery_name, delivery_phone, delivery_email, delivery_address, delivery_city, delivery_state, delivery_country) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("idsssssss", $user_id, $total_amount, $delivery_name, $delivery_phone, $delivery_email, $delivery_address, $delivery_city, $delivery_state, $delivery_country);
$stmt->execute();
$order_id = $conn->insert_id;

// 2. Add Items to product_order_items
$item_stmt = $conn->prepare("INSERT INTO product_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($cart as $key => $item) {
    $product_id = $item['id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    $item_stmt->execute();
}

// 3. Create Payment row (status pending)
$payment_ref = uniqid("ps_", true);
$stmt = $conn->prepare("INSERT INTO product_payments (order_id, amount, payment_method, payment_status, reference) VALUES (?, ?, ?, 'pending', ?)");
$stmt->bind_param("idss", $order_id, $total_amount, $payment_method, $payment_ref);
$stmt->execute();

// 4. Return response for JS inline popup
$response = [
    'success' => true,
    'payment_method' => $payment_method,
    'amount' => intval($total_amount * 100), // Paystack uses kobo
    'email' => $delivery_email,
    'reference' => $payment_ref,
    'order_id' => $order_id,
    'description' => $order_description,
    'public_key' => PAYSTACK_PUBLIC_KEY
];

echo json_encode($response);