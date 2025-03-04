<?php
session_start();
include '../includes/db_connect.php';

// Ensure user is logged in or restore session from URL
if (!isset($_SESSION['user_id']) && isset($_GET['user_id'])) {
    $_SESSION['user_id'] = $_GET['user_id'];
}

// Redirect if session is still missing
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get transaction reference
$reference = $_GET['reference'] ?? '';

if (!$reference) {
    die("Invalid transaction reference.");
}

// Fetch transaction details
$stmt = $conn->prepare("
    SELECT p.title, p.location, p.price, t.amount, t.status, t.created_at 
    FROM payments t 
    JOIN properties p ON t.property_id = p.id 
    WHERE t.user_id = ? 
    AND t.transaction_id = ? 
    AND t.status = 'completed'
    ORDER BY t.created_at DESC 
    LIMIT 1
");
$stmt->bind_param("is", $_SESSION['user_id'], $reference);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

// Ensure transaction exists
if (!$transaction) {
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
            <p><strong>Amount Paid:</strong> ₦<?php echo number_format($transaction['amount'], 2); ?></p>
            <p><strong>Status:</strong> <span
                    class="text-green-500"><?php echo ucfirst(htmlspecialchars($transaction['status'])); ?></span></p>
            <p><strong>Transaction Reference:</strong> <?php echo htmlspecialchars($reference); ?></p>
            <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($transaction['created_at'])); ?></p>
        </div>

        <a href="buyer_dashboard.php" class="block mt-6 bg-blue-600 text-white px-5 py-3 rounded hover:bg-blue-700">
            Go to Dashboard
        </a>
    </div>
</body>

</html>