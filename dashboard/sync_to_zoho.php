<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

include '../includes/db_connect.php';
include '../includes/zoho_functions.php';

$log_prefix = date('Y-m-d H:i:s') . " [Zoho Sync Script] ";
error_log($log_prefix . "Script started for session: " . session_id());

include '../includes/db_connect.php';
include '../includes/zoho_functions.php';

$log_prefix = date('Y-m-d H:i:s') . " [Zoho Sync Script] ";
error_log($log_prefix . "Script started for session: " . session_id());

// Ensure only superadmin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    error_log($log_prefix . "Unauthorized access: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));
    die("Error: Unauthorized access.");
}
error_log($log_prefix . "Superadmin {$_SESSION['user_id']} authorized");

$sync_success = true;
$error_messages = [];

// Check if zoho_property_id exists (for transition)
error_log($log_prefix . "Checking properties table columns");
$columns_query = "SHOW COLUMNS FROM properties";
$columns_result = $conn->query($columns_query);
$has_zoho_property_id = false;
$has_zoho_deal_id = false;
while ($column = $columns_result->fetch_assoc()) {
    if ($column['Field'] === 'zoho_property_id') $has_zoho_property_id = true;
    if ($column['Field'] === 'zoho_deal_id') $has_zoho_deal_id = true;
}
error_log($log_prefix . "Columns found: zoho_property_id=" . ($has_zoho_property_id ? 'yes' : 'no') . ", zoho_deal_id=" . ($has_zoho_deal_id ? 'yes' : 'no'));

// Sync Users to Zoho CRM (Leads)
error_log($log_prefix . "Starting user sync");
$userQuery = "SELECT id, name, lname, email, phone, role FROM users WHERE zoho_lead_id IS NULL";
$userResult = $conn->query($userQuery);
$user_count = $userResult->num_rows;
error_log($log_prefix . "Found $user_count users to sync");

while ($user = $userResult->fetch_assoc()) {
    error_log($log_prefix . "Syncing user: {$user['email']} (ID: {$user['id']})");
    try {
        $zoho_lead_id = createZohoLead($user['name'], $user['lname'], $user['email'], $user['phone'], $user['role']);
        if ($zoho_lead_id) {
            $stmt = $conn->prepare("UPDATE users SET zoho_lead_id = ? WHERE id = ?");
            $stmt->bind_param("si", $zoho_lead_id, $user['id']);
            if ($stmt->execute()) {
                error_log($log_prefix . "User {$user['email']} synced, zoho_lead_id=$zoho_lead_id");
            } else {
                throw new Exception("Failed to update zoho_lead_id for user ID {$user['id']}: " . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Zoho API returned no lead ID for user {$user['email']}");
        }
    } catch (Exception $e) {
        $sync_success = false;
        $error_message = "❌ ERROR: Could not sync user {$user['name']} ({$user['email']}): {$e->getMessage()}";
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
    }
}

// Sync Properties to Zoho CRM
error_log($log_prefix . "Starting property sync");
$propertyQuery = "SELECT p.id, p.title, p.price, p.location, p.listing_type, p.status, p.type, p.bedrooms, p.bathrooms, p.size, p.description, p.garage, p.owner_id, u.zoho_lead_id
                  FROM properties p 
                  JOIN users u ON p.owner_id = u.id
                  WHERE (p.zoho_product_id IS NULL" . ($has_zoho_deal_id ? " OR p.zoho_deal_id IS NULL" : ($has_zoho_property_id ? " OR p.zoho_property_id IS NULL" : "")) . ")";
$propertyResult = $conn->query($propertyQuery);
$property_count = $propertyResult->num_rows;
error_log($log_prefix . "Found $property_count properties to sync");

while ($property = $propertyResult->fetch_assoc()) {
    error_log($log_prefix . "Processing property: {$property['title']} (ID: {$property['id']})");
    if (!$property['zoho_lead_id']) {
        $sync_success = false;
        $error_message = "⚠️ Skipped property: {$property['title']} (ID: {$property['id']}) - Owner (ID: {$property['owner_id']}) not synced to Zoho";
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
        continue;
    }

    try {
        $success = createZohoProperty(
            $property['title'],
            $property['price'],
            $property['location'],
            $property['listing_type'],
            $property['status'],
            $property['type'],
            $property['bedrooms'],
            $property['bathrooms'],
            $property['size'],
            $property['description'],
            $property['garage'],
            $property['zoho_lead_id'],
            $property['owner_id'], // Pass owner_id instead of null
            $property['id']
        );
        if ($success) {
            error_log($log_prefix . "Property {$property['title']} (ID: {$property['id']}) synced successfully");
        } else {
            throw new Exception("createZohoProperty returned false");
        }
    } catch (Exception $e) {
        $sync_success = false;
        $error_message = "❌ ERROR: Could not sync property {$property['title']} (ID: {$property['id']}): {$e->getMessage()}";
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
    }
}

$conn->close();
error_log($log_prefix . "Script completed: sync_success=" . ($sync_success ? 'true' : 'false'));
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
                text: "All users and properties have been synced to Zoho CRM.",
                icon: "success",
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "superadmin_dashboard.php";
            });
        <?php else: ?>
            Swal.fire({
                title: "Sync Completed with Errors ⚠️",
                html: "<?php echo implode('<br>', array_map('htmlspecialchars', $error_messages)); ?>",
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