<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/config.php';

$log_file = '../logs/flutterwave_payload.log';
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Incoming POST:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

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

// Validate required fields
$required_fields = ['delivery_name', 'delivery_phone', 'delivery_address', 'delivery_city', 'delivery_state', 'delivery_country', 'payment_method'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Collect inputs
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

// Calculate total (clean and accurate)
$total_amount = 0;
foreach ($cart as $item) {
    $total_amount += floatval(str_replace(',', '', $item['price'])) * intval($item['quantity']);
}
$total_amount += $delivery_fee;
$total_amount = round($total_amount, 2); // always round currency

// Create order
$stmt = $conn->prepare("INSERT INTO product_orders (user_id, total_amount, status, delivery_name, delivery_phone, delivery_email, delivery_address, delivery_city, delivery_state, delivery_country) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("idsssssss", $user_id, $total_amount, $delivery_name, $delivery_phone, $delivery_email, $delivery_address, $delivery_city, $delivery_state, $delivery_country);
$stmt->execute();
if ($stmt->error) {
    echo json_encode(['success' => false, 'message' => 'Failed to create order.']);
    exit();
}
$order_id = $stmt->insert_id;

// Insert order items
$item_stmt = $conn->prepare("INSERT INTO product_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($cart as $item) {
    $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
    $item_stmt->execute();
    if ($item_stmt->error) {
        echo json_encode(['success' => false, 'message' => 'Failed to save order items.']);
        exit();
    }
}

// Create payment
$payment_ref = uniqid("ps_", true);
$stmt = $conn->prepare("INSERT INTO product_payments (order_id, amount, payment_method, payment_status, reference) VALUES (?, ?, ?, 'pending', ?)");
$stmt->bind_param("idss", $order_id, $total_amount, $payment_method, $payment_ref);
$stmt->execute();
if ($stmt->error) {
    echo json_encode(['success' => false, 'message' => 'Failed to create payment record.']);
    exit();
}

// Build final response
$response = [
    'success' => true,
    'payment_method' => $payment_method,
    'reference' => $payment_ref,
    'amount' => ($payment_method === 'paystack') ? intval($total_amount * 100) : $total_amount,
    'email' => $delivery_email,
    'name' => $delivery_name,
    'phone' => $delivery_phone,
    'description' => $order_description,
    'order_id' => $order_id
];

// Add gateway keys
switch ($payment_method) {
    case 'paystack':
        $response['public_key'] = PAYSTACK_PUBLIC_KEY;
        break;
    case 'flutterwave':
        $response['public_key'] = FLW_PUBLIC_KEY;
        $response['redirect_url'] = FLW_REDIRECT_URL;
        break;
    default:
        $response['success'] = false;
        $response['message'] = 'Unsupported payment method.';
        break;
}

// Log outgoing response
file_put_contents($log_file, "Response:\n" . print_r($response, true) . "\n\n", FILE_APPEND);

echo json_encode($response);
