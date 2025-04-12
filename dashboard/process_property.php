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

// Ensure superadmin is logged in
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
$propertyQuery = "SELECT p.id, p.title, p.price, p.location, p.listing_type, p.status, p.type, p.bedrooms, p.bathrooms, p.size, p.description, p.garage, p.owner_id, u.zoho_lead_id, p.zoho_product_id" . ($has_zoho_deal_id ? ", p.zoho_deal_id" : ($has_zoho_property_id ? ", p.zoho_property_id" : "")) . "
                  FROM properties p 
                  JOIN users u ON p.owner_id = u.id";
$propertyResult = $conn->query($propertyQuery);
$property_count = $propertyResult->num_rows;
error_log($log_prefix . "Found $property_count properties to check");

while ($property = $propertyResult->fetch_assoc()) {
    $property_id = $property['id'];
    $title = $property['title'];
    error_log($log_prefix . "Processing property: $title (ID: $property_id)");

    // Check if already synced
    $deal_id = $has_zoho_deal_id ? $property['zoho_deal_id'] : ($has_zoho_property_id ? $property['zoho_property_id'] : null);
    if ($property['zoho_product_id'] && $deal_id) {
        error_log($log_prefix . "Skipping property: $title (ID: $property_id) - already synced (zoho_product_id={$property['zoho_product_id']}, deal_id=$deal_id)");
        continue;
    }

    // Validate zoho_lead_id
    if (!$property['zoho_lead_id']) {
        $sync_success = false;
        $error_message = "⚠️ Skipped property: $title (ID: $property_id) - Owner (ID: {$property['owner_id']}) not synced to Zoho";
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
        continue;
    }

    // Validate numeric fields
    $errors = [];
    $size = $property['size'];
    if (!is_null($size) && (!is_numeric($size) || $size < 0)) {
        $errors[] = "Invalid size: '$size'";
        $size = null;
    }
    $bedrooms = $property['bedrooms'];
    if (!is_null($bedrooms) && (!is_numeric($bedrooms) || $bedrooms < 0)) {
        $errors[] = "Invalid bedrooms: '$bedrooms'";
        $bedrooms = null;
    }
    $bathrooms = $property['bathrooms'];
    if (!is_null($bathrooms) && (!is_numeric($bathrooms) || $bathrooms < 0)) {
        $errors[] = "Invalid bathrooms: '$bathrooms'";
        $bathrooms = null;
    }
    $garage_spaces = $property['garage_spaces'];
    if (!is_null($garage_spaces) && (!is_numeric($garage_spaces) || $garage_spaces < 0)) {
        $errors[] = "Invalid garage_spaces: '$garage_spaces'";
        $garage_spaces = null;
    }

    if (!empty($errors)) {
        $sync_success = false;
        $error_message = "⚠️ Skipped property: $title (ID: $property_id) - " . implode("; ", $errors);
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
            $bedrooms,
            $bathrooms,
            $size,
            $property['description'],
            $garage,
            $property['zoho_lead_id'],
            $property['owner_id'],
            $property_id
        );
        if ($success) {
            error_log($log_prefix . "Property $title (ID: $property_id) synced successfully");
        } else {
            throw new Exception("createZohoProperty returned false");
        }
    } catch (Exception $e) {
        $sync_success = false;
        $error_message = "❌ ERROR: Could not sync property $title (ID: $property_id): {$e->getMessage()}";
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