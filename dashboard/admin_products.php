<?php
session_start();
include '../includes/db_connect.php';

// Restrict access to admin/superadmin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all products with category info
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p
                        JOIN categories c ON p.category_id = c.id
                        ORDER BY p.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<?php $page_content = __DIR__ . "/admin_products_content.php";
include 'dashboard_layout.php'; ?>