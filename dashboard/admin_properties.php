<?php
session_start();
include '../includes/db_connect.php';

// Ensure only admin or superadmin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch properties for approval & management
$stmt = $conn->prepare("SELECT p.*, u.name AS owner_name FROM properties p 
                        JOIN users u ON p.owner_id = u.id 
                        ORDER BY p.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<?php $page_content = __DIR__ . "/admin_properties_content.php"; include 'dashboard_layout.php'; ?>