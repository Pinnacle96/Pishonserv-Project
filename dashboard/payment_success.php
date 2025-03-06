<?php
session_start();
include '../includes/db_connect.php';


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name, profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['name'] = $user['name'];
        $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
    }
}


// Debug: Log session and GET data
error_log("Session data: " . print_r($_SESSION, true));
error_log("GET data: " . print_r($_GET, true));

// Ensure user is logged in or restore session from URL
if (!isset($_SESSION['user_id'])) {
    $user_id = trim($_GET['user_id'] ?? '');
    if ($user_id === '') {
        error_log("No user_id in session or URL. GET user_id is empty. URL: " . print_r($_SERVER['REQUEST_URI'], true));
        die("User not logged in or invalid user ID. Please log in and try again.");
    }
    $_SESSION['user_id'] = $user_id;
    error_log("Restored user_id from GET: " . $_SESSION['user_id']);
}

// Redirect if session is still missing (shouldn’t happen after above check)
if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session or URL after restoration. Redirecting to login.");
    header("Location: ../auth/login.php");
    exit();
}

// Get transaction reference
$reference = trim($_GET['reference'] ?? '');
if (!$reference) {
    error_log("Invalid transaction reference. URL: " . print_r($_SERVER['REQUEST_URI'], true));
    die("Invalid transaction reference.");
}

// Debug: Log the received parameters
error_log("Fetching transaction for user_id: " . $_SESSION['user_id'] . ", reference: " . $reference);

// Fetch transaction details
$stmt = $conn->prepare("
    SELECT p.title, p.location, p.price, t.amount, t.status, t.created_at 
    FROM payments t 
    JOIN properties p ON t.property_id = p.id 
    WHERE TRIM(t.transaction_id) = ? 
    AND t.user_id = ? 
    AND LOWER(TRIM(t.status)) = 'completed'
");

// 🛠️ Fix: Corrected bind_param order (string, int) instead of (int, string)
$stmt->bind_param("si", $reference, $_SESSION['user_id']);
$stmt->execute();

// Debug: Log any SQL errors
if ($conn->error) {
    error_log("Database error during prepare/execute: " . $conn->error);
}

$transaction = $stmt->get_result()->fetch_assoc();

// Debug: Log the query result
error_log("Transaction query result: " . print_r($transaction, true));

if (!$transaction) {
    // Additional debugging: Try querying directly in the database
    $result = $conn->query("SELECT * FROM payments WHERE TRIM(transaction_id) = '" . $conn->real_escape_string($reference) . "' AND user_id = " . intval($_SESSION['user_id']) . " AND LOWER(TRIM(status)) = 'completed'");
    error_log("Manual query result: " . print_r($result->fetch_all(MYSQLI_ASSOC), true));
    error_log("Transaction not found for user_id: " . $_SESSION['user_id'] . ", reference: " . $reference . ". Database state: " . print_r($conn->error, true));
    die("Transaction details not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Real Estate Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg text-center">
        <h1 class="text-3xl font-bold text-green-600">Payment Successful ✅</h1>
        <p class="text-gray-700 mt-2">Thank you! Your payment has been confirmed.</p>

        <div class="mt-6 text-left">
            <p><strong>Property:</strong> <?php echo htmlspecialchars($transaction['title']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($transaction['location']); ?></p>
            <p><strong>Amount Paid:</strong> ₦<?php echo number_format(htmlspecialchars($transaction['amount']), 2); ?>
            </p>
            <p><strong>Status:</strong> <span
                    class="text-green-500"><?php echo ucfirst(htmlspecialchars($transaction['status'])); ?></span></p>
            <p><strong>Transaction Reference:</strong> <?php echo htmlspecialchars($reference); ?></p>
            <p><strong>Date:</strong>
                <?php echo date("F j, Y, g:i a", strtotime(htmlspecialchars($transaction['created_at']))); ?></p>
        </div>

        <a href="buyer_dashboard.php" class="block mt-6 bg-blue-600 text-white px-5 py-3 rounded hover:bg-blue-700">
            Go to Dashboard
        </a>
    </div>
</body>

</html>