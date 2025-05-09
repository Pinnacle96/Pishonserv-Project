<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: admin_products.php?deleted=1");
    exit();
}

$product = $conn->query("SELECT name FROM products WHERE id = $id")->fetch_assoc();
?>

<?php $page_content = __DIR__ . "/admin_delete_product_content.php";
include 'dashboard_layout.php'; ?>