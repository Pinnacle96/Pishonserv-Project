<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Contains API functions

// Ensure superadmin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    die("Error: Unauthorized access.");
}

// Track sync status
$sync_success = true;
$error_messages = [];

// Sync Users to Zoho CRM
$userQuery = "SELECT id, name, lname, email, phone, role FROM users WHERE zoho_contact_id IS NULL";
$userResult = $conn->query($userQuery);

while ($user = $userResult->fetch_assoc()) {
    $zoho_contact_id = createZohoContact($user['name'], $user['lname'], $user['email'], $user['phone'], $user['role']);

    if ($zoho_contact_id) {
        $stmt = $conn->prepare("UPDATE users SET zoho_contact_id = ? WHERE id = ?");
        $stmt->bind_param("si", $zoho_contact_id, $user['id']);
        $stmt->execute();
    } else {
        $sync_success = false;
        $error_messages[] = "❌ ERROR: Could not sync user " . $user['name'];
    }
}

// Sync Properties to Zoho CRM
$propertyQuery = "SELECT p.id, p.title, p.price, p.location, p.type, p.status, u.zoho_contact_id
                  FROM properties p 
                  JOIN users u ON p.owner_id = u.id
                  WHERE p.zoho_property_id IS NULL";
$propertyResult = $conn->query($propertyQuery);

while ($property = $propertyResult->fetch_assoc()) {
    if (!$property['zoho_contact_id']) {
        $sync_success = false;
        $error_messages[] = "⚠️ Skipped property: " . $property['title'] . " (Owner not synced to Zoho CRM)";
        continue;
    }

    $zoho_property_id = createZohoProperty(
        $property['title'],
        $property['price'],
        $property['location'],
        $property['type'],
        $property['status'],
        $property['zoho_contact_id']
    );

    if ($zoho_property_id) {
        $stmt = $conn->prepare("UPDATE properties SET zoho_property_id = ? WHERE id = ?");
        $stmt->bind_param("si", $zoho_property_id, $property['id']);
        $stmt->execute();
    } else {
        $sync_success = false;
        $error_messages[] = "❌ ERROR: Could not sync property " . $property['title'];
    }
}

// Show SweetAlert and Redirect
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoho Sync Status</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
        <?php if ($sync_success): ?>
            Swal.fire({
                title: "Sync Successful ✅",
                text: "Users and Properties have been added to Zoho CRM.",
                icon: "success",
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "superadmin_dashboard.php"; // Redirect
            });
        <?php else: ?>
            Swal.fire({
                title: "Sync Completed with Errors ⚠️",
                html: "<?php echo implode('<br>', $error_messages); ?>",
                icon: "warning",
                timer: 5000,
                showConfirmButton: true
            }).then(() => {
                window.location.href = "superadmin_dashboard.php"; // Redirect
            });
        <?php endif; ?>
    </script>
</body>

</html>