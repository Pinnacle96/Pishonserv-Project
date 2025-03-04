<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Define allowed roles
$allowed_roles = ['agent', 'owner', 'hotel_owner'];

// Check if the user's role is allowed
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../auth/unauthorized.php"); // Redirect to an unauthorized page
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $owner_id = $_SESSION['user_id'];

    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($image_tmp, "../public/uploads/$image");

    $stmt = $conn->prepare("INSERT INTO properties (title, price, location, type, description, image, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $title, $price, $location, $type, $description, $image, $owner_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Property added successfully!";
        header("Location: agent_properties.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to add property.";
    }
}
?>

<?php $page_content = __DIR__ . "/agent_add_property_content.php"; include 'dashboard_layout.php'; ?>