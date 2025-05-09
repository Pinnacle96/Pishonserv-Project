<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
$cat_result = $conn->query("SELECT id, name FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);

    // Default to existing image path
    $image_path = $product['image'];

    // Check if a new image is uploaded
    if (!empty($_FILES['image_file']['name'])) {
        $target_dir = '../public/uploads/';
        $file_name = basename($_FILES['image_file']['name']);
        $new_file = $target_dir . time() . '_' . $file_name;

        // Try to move the uploaded image
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $new_file)) {
            $image_path = $new_file;
        }
    }

    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssdiss", $name, $description, $price, $category_id, $image_path, $id);
    $stmt->execute();

    header("Location: admin_products.php?updated=1");
    exit();
}
?>

<?php $page_content = __DIR__ . "/admin_edit_product_content.php";
include 'dashboard_layout.php'; ?>