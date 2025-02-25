<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
   $price = number_format($_POST['price'], 2, '.', ''); // Format price Ensure price is stored correctly
    $location = $_POST['location'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $owner_id = $_SESSION['user_id']; // Logged-in agent

    $imagePaths = []; // Store all uploaded image paths

    // Image Upload Handling
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create upload directory if not exists
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = uniqid() . '_' . $_FILES['images']['name'][$key];
            $filePath = $uploadDir . $fileName;

            // Compress and Save Image
            if (compressImage($tmp_name, $filePath, 30)) {
                $imagePaths[] = $fileName; // Save file path
            }
        }
    }

    // Store in database (images as comma-separated values)
    $imageString = implode(',', $imagePaths);

    $stmt = $conn->prepare("INSERT INTO properties (title, price, location, type, description, owner_id, images) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsssis", $title, $price, $location, $type, $description, $owner_id, $imageString);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Property added successfully!";
    } else {
        $_SESSION['error'] = "Error adding property.";
    }

    header("Location: ../dashboard/agent_properties.php");
    exit();
}

// Function to compress image to 5KB
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } else {
        return false; // Unsupported format
    }

    // Reduce file size to 5KB
    $fileSize = filesize($source);
    while ($fileSize > 5120) { // 5KB = 5120 Bytes
        ob_start();
        imagejpeg($image, null, $quality);
        $compressedImage = ob_get_clean();
        file_put_contents($destination, $compressedImage);
        $fileSize = filesize($destination);
        $quality -= 5; // Reduce quality in steps
        if ($quality < 10) break; // Prevent too much quality loss
    }

    imagedestroy($image);
    return true;
}
?>