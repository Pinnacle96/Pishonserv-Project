<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');
<<<<<<< HEAD
=======

include '../includes/db_connect.php';
include '../includes/zoho_functions.php';

$log_prefix = date('Y-m-d H:i:s') . " [Zoho Sync Script] ";
error_log($log_prefix . "Script started for session: " . session_id());
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5

include '../includes/db_connect.php';
include '../includes/zoho_functions.php';

$log_prefix = date('Y-m-d H:i:s') . " [Zoho Sync Script] ";
error_log($log_prefix . "Script started for session: " . session_id());

// Ensure only superadmin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
<<<<<<< HEAD
    error_log($log_prefix . "Unauthorized access: user_id=" . ($_SESSION['user_id'] ?? 'unset'));
    die("Unauthorized access.");
=======
    error_log($log_prefix . "Unauthorized access: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));
    die("Error: Unauthorized access.");
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
}
error_log($log_prefix . "Superadmin {$_SESSION['user_id']} authorized");

$sync_success = true;
$error_messages = [];

<<<<<<< HEAD
// 1️⃣ Sync Users to Zoho CRM (Leads)
error_log($log_prefix . "Starting user sync...");

$userResult = $conn->query("SELECT id, name, lname, email, phone, role FROM users WHERE zoho_lead_id IS NULL");

while ($user = $userResult->fetch_assoc()) {
    error_log($log_prefix . "Syncing user: {$user['email']}");

=======
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
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
    try {
        $zoho_lead_id = createZohoLead($user['name'], $user['lname'], $user['email'], $user['phone'], $user['role']);
        if ($zoho_lead_id) {
            $stmt = $conn->prepare("UPDATE users SET zoho_lead_id = ? WHERE id = ?");
            $stmt->bind_param("si", $zoho_lead_id, $user['id']);
<<<<<<< HEAD
            $stmt->execute();
            $stmt->close();
            error_log($log_prefix . "✅ User synced with Zoho Lead ID: $zoho_lead_id");
        } else {
            throw new Exception("Zoho API did not return Lead ID.");
        }
    } catch (Exception $e) {
        $sync_success = false;
        $error_message = "❌ Could not sync user {$user['email']}: {$e->getMessage()}";
=======
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
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
    }
}

<<<<<<< HEAD
// 2️⃣ Convert Leads to Contacts if not yet converted
error_log($log_prefix . "Starting lead to contact conversion...");

$usersToConvert = $conn->query("SELECT id, email, zoho_lead_id FROM users WHERE zoho_lead_id IS NOT NULL AND (zoho_contact_id IS NULL OR zoho_contact_id = '')");

while ($user = $usersToConvert->fetch_assoc()) {
    $user_id = $user['id'];
    $email = $user['email'];
    $lead_id = $user['zoho_lead_id'];

    $contact_id = convertZohoLeadToContact($lead_id, $email);

    if ($contact_id) {
        $stmt = $conn->prepare("UPDATE users SET zoho_contact_id = ? WHERE id = ?");
        $stmt->bind_param("si", $contact_id, $user_id);
        $stmt->execute();
        $stmt->close();
        error_log($log_prefix . "✅ User ID $user_id lead converted to contact successfully.");
    } else {
        $sync_success = false;
        $error_message = "⚠️ Failed to convert lead to contact for user ID: $user_id.";
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
    }
}

// 3️⃣ Sync Properties to Zoho CRM (Products)
error_log($log_prefix . "Starting property sync...");

// Check columns
$columns_result = $conn->query("SHOW COLUMNS FROM properties");
$has_zoho_property_id = false;
$has_zoho_deal_id = false;
while ($column = $columns_result->fetch_assoc()) {
    if ($column['Field'] === 'zoho_property_id') $has_zoho_property_id = true;
    if ($column['Field'] === 'zoho_deal_id') $has_zoho_deal_id = true;
}
error_log($log_prefix . "Columns found: zoho_property_id=" . ($has_zoho_property_id ? 'yes' : 'no') . ", zoho_deal_id=" . ($has_zoho_deal_id ? 'yes' : 'no'));

// Properties needing sync
$propertyQuery = "
    SELECT p.id, p.title, p.price, p.location, p.listing_type, p.status, p.type,
           p.bedrooms, p.bathrooms, p.size, p.description, p.garage,
           p.owner_id, u.zoho_lead_id
    FROM properties p
    JOIN users u ON p.owner_id = u.id
    WHERE (p.zoho_product_id IS NULL OR p.zoho_product_id = '')
";
=======
// Sync Properties to Zoho CRM
error_log($log_prefix . "Starting property sync");
$propertyQuery = "SELECT p.id, p.title, p.price, p.location, p.listing_type, p.status, p.type, p.bedrooms, p.bathrooms, p.size, p.description, p.garage, p.owner_id, u.zoho_lead_id
                  FROM properties p 
                  JOIN users u ON p.owner_id = u.id
                  WHERE (p.zoho_product_id IS NULL" . ($has_zoho_deal_id ? " OR p.zoho_deal_id IS NULL" : ($has_zoho_property_id ? " OR p.zoho_property_id IS NULL" : "")) . ")";
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
$propertyResult = $conn->query($propertyQuery);
$property_count = $propertyResult->num_rows;
error_log($log_prefix . "Found $property_count properties to sync");

while ($property = $propertyResult->fetch_assoc()) {
<<<<<<< HEAD
    error_log($log_prefix . "Processing property: {$property['title']}");

    if (!$property['zoho_lead_id']) {
        $sync_success = false;
        $error_message = "⚠️ Property skipped: {$property['title']} (Owner ID {$property['owner_id']}) has no Zoho Lead.";
=======
    error_log($log_prefix . "Processing property: {$property['title']} (ID: {$property['id']})");
    if (!$property['zoho_lead_id']) {
        $sync_success = false;
        $error_message = "⚠️ Skipped property: {$property['title']} (ID: {$property['id']}) - Owner (ID: {$property['owner_id']}) not synced to Zoho";
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
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
<<<<<<< HEAD
            $property['owner_id'],
            $property['id']
        );

        if ($success) {
            error_log($log_prefix . "✅ Property synced: {$property['title']}");
        } else {
            throw new Exception("Could not sync property {$property['title']}.");
        }
    } catch (Exception $e) {
        $sync_success = false;
        $error_message = "❌ Error syncing property {$property['title']}: {$e->getMessage()}";
=======
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
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
        $error_messages[] = $error_message;
        error_log($log_prefix . $error_message);
    }
}

$conn->close();
<<<<<<< HEAD
error_log($log_prefix . "Script completed. Success=" . ($sync_success ? 'true' : 'false'));
=======
error_log($log_prefix . "Script completed: sync_success=" . ($sync_success ? 'true' : 'false'));
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zoho Sync Status</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<<<<<<< HEAD
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
            showConfirmButton: true
        }).then(() => {
            window.location.href = "superadmin_dashboard.php";
        });
    <?php endif; ?>
</script>
=======
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
>>>>>>> 925fad23b7575f6fea4244a291821886eff718c5
</body>
</html>
