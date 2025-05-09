<?php
session_start();
include '../includes/db_connect.php';

// Restrict access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch categories
$cat_result = $conn->query("SELECT id, name FROM categories");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);

    // --- Image Upload Handling ---
    $upload_dir = '../public/uploads/';
    $image_path = '';

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image_file']['tmp_name'];
        $original_name = basename($_FILES['image_file']['name']);
        $unique_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $original_name);
        $target_path = $upload_dir . $unique_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $image_path = 'public/uploads/' . $unique_name;
        } else {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: admin_add_product.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Image upload failed. Please select a valid image.";
        header("Location: admin_add_product.php");
        exit();
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdss", $name, $description, $price, $category_id, $image_path);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
    }

    header("Location: admin_add_product.php");
    exit();
}
?>

<?php $page_content = __DIR__ . "/admin_add_product_content.php";
include 'dashboard_layout.php'; ?>