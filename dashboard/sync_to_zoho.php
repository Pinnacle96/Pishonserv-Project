<?php
session_start();
include '../includes/db_connect.php';
include '../includes/zoho_functions.php'; // Updated Zoho functions

// Ensure superadmin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    die("Error: Unauthorized access.");
}

$sync_success = true;
$error_messages = [];

// Sync Users to Zoho CRM (Leads)
$userQuery = "SELECT id, name, lname, email, phone, role FROM users WHERE zoho_lead_id IS NULL";
$userResult = $conn->query($userQuery);

while ($user = $userResult->fetch_assoc()) {
    $zoho_lead_id = createZohoLead($user['name'], $user['lname'], $user['email'], $user['phone'], $user['role']);

    if ($zoho_lead_id) {
        $stmt = $conn->prepare("UPDATE users SET zoho_lead_id = ? WHERE id = ?");
        $stmt->bind_param("si", $zoho_lead_id, $user['id']);
        $stmt->execute();
    } else {
        $sync_success = false;
        $error_messages[] = "❌ ERROR: Could not sync user " . $user['name'];
    }
}

// Sync Properties to Zoho CRM
$propertyQuery = "SELECT p.id, p.title, p.price, p.location, p.type, p.status, p.listing_type, u.zoho_lead_id
                  FROM properties p 
                  JOIN users u ON p.owner_id = u.id
                  WHERE p.zoho_property_id IS NULL OR p.zoho_product_id IS NULL";
$propertyResult = $conn->query($propertyQuery);

while ($property = $propertyResult->fetch_assoc()) {
    if (!$property['zoho_lead_id']) {
        $sync_success = false;
        $error_messages[] = "⚠️ Skipped property: " . $property['title'] . " (Owner not synced to Zoho)";
        continue;
    }

    $success = createZohoProperty(
        $property['title'],
        $property['price'],
        $property['location'],
        $property['listing_type'],
        $property['status'],
        $property['type'],
        $property['zoho_lead_id'],
        null, // $user_id is not needed here
        $property['id']
    );

    if (!$success) {
        $sync_success = false;
        $error_messages[] = "❌ ERROR: Could not sync property " . $property['title'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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
                window.location.href = "superadmin_dashboard.php";
            });
        <?php else: ?>
            Swal.fire({
                title: "Sync Completed with Errors ⚠️",
                html: "<?php echo implode('<br>', $error_messages); ?>",
                icon: "warning",
                timer: 5000,
                showConfirmButton: true
            }).then(() => {
                window.location.href = "superadmin_dashboard.php";
            });
        <?php endif; ?>
    </script>
</body>

</html>