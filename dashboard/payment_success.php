<?php
session_start();
include '../includes/db_connect.php';

// ✅ Enable Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'paystack_success_errors.log'); // Log errors to a file

// ✅ Debugging: Log session and GET data
error_log("🔍 Payment Success - Session: " . print_r($_SESSION, true));
error_log("🔍 Payment Success - GET: " . print_r($_GET, true));

// ✅ Ensure user is logged in or restore session from GET
if (!isset($_SESSION['user_id'])) {
    $user_id = trim($_GET['user_id'] ?? '');
    if ($user_id === '') {
        error_log("❌ User not logged in. No user_id in session or GET.");
        $_SESSION['error'] = "User session expired. Please log in again.";
        header("Location: ../auth/login.php");
        exit();
    }
    $_SESSION['user_id'] = $user_id;
    error_log("✅ Restored user_id from GET: " . $_SESSION['user_id']);
}

$user_id = $_SESSION['user_id'];

// ✅ Get transaction reference
$reference = trim($_GET['reference'] ?? '');
if (!$reference) {
    error_log("❌ Invalid transaction reference.");
    $_SESSION['error'] = "Invalid transaction reference.";
    header("Location: ../dashboard/payment_failed.php");
    exit();
}

// ✅ Fetch transaction details
$stmt = $conn->prepare("
    SELECT p.title, p.location, p.price, t.amount, t.status, t.created_at 
    FROM payments t 
    JOIN properties p ON t.property_id = p.id 
    WHERE TRIM(t.transaction_id) = ? 
    AND t.user_id = ? 
    AND LOWER(TRIM(t.status)) = 'completed'
");
$stmt->bind_param("si", $reference, $user_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ✅ Debugging: Log transaction details
error_log("🔍 Transaction query result: " . print_r($transaction, true));

if (!$transaction) {
    // Check database manually
    $result = $conn->query("SELECT * FROM payments WHERE TRIM(transaction_id) = '" . $conn->real_escape_string($reference) . "' AND user_id = " . intval($user_id) . " AND LOWER(TRIM(status)) = 'completed'");
    error_log("🔍 Manual query result: " . print_r($result->fetch_all(MYSQLI_ASSOC), true));

    error_log("❌ Transaction not found for user_id: $user_id, reference: $reference");
    $_SESSION['error'] = "Transaction not found or not completed.";
    header("Location: ../dashboard/payment_failed.php");
    exit();
}

// ✅ Fetch User Details
$stmt = $conn->prepare("SELECT name, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    $_SESSION['name'] = $user['name'];
    $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
}

// ✅ Debugging: Log final confirmation
error_log("✅ Payment Success - Displaying confirmation page");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Real Estate Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

    <!-- ✅ SweetAlert2 Success Message -->
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Payment Successful ✅',
            text: 'Your payment has been confirmed!',
            confirmButtonColor: '#092468',
            confirmButtonText: 'OK'
        });
    </script>

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