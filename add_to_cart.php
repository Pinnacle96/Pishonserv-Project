<?php
session_start();
include 'includes/db_connect.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to add items to your cart.";
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$selected_color = isset($_POST['color']) ? trim($_POST['color']) : 'default';

if ($product_id <= 0) {
    $_SESSION['error'] = "Invalid product selected.";
    header("Location: index.php");
    exit();
}

// ✅ Fetch product details
$stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header("Location: index.php");
    exit();
}

// ✅ Unique cart key: product + color
$cart_key = $product_id . '_' . $selected_color;

// ✅ Update session cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$cart_key])) {
    $_SESSION['cart'][$cart_key]['quantity'] += 1;
} else {
    $_SESSION['cart'][$cart_key] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'color' => $selected_color,
        'quantity' => 1
    ];
}

// ✅ Sync to database `cart_items`
$check = $conn->prepare("SELECT id FROM cart_items WHERE user_id = ? AND product_id = ? AND color = ?");
$check->bind_param("iis", $user_id, $product_id, $selected_color);
$check->execute();
$res = $check->get_result();

if ($existing = $res->fetch_assoc()) {
    $update = $conn->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
    $update->bind_param("i", $existing['id']);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity, color) VALUES (?, ?, ?, ?)");
    $qty = 1;
    $insert->bind_param("iiis", $user_id, $product_id, $qty, $selected_color);
    $insert->execute();
}

$_SESSION['success'] = "Added to cart successfully.";
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();