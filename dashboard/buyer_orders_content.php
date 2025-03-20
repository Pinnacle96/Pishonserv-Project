<?php
//session_start();
include '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in to view your orders.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders (transactions linked to properties)
$stmt = $conn->prepare("
    SELECT p.title AS property_title, 
           p.location AS property_location, 
           p.price AS property_price, 
           pay.amount AS paid_amount, 
           pay.status AS transaction_status, 
           pay.created_at AS transaction_date, 
           pay.transaction_id 
    FROM payments pay
    JOIN properties p ON pay.property_id = p.id
    WHERE pay.user_id = ?
    ORDER BY pay.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Order History</h2>
    <p class="text-gray-600 dark:text-gray-400">View your past transactions.</p>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded-lg shadow-md">
        <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                    <th class="p-3 border">Property</th>
                    <th class="p-3 border">Location</th>
                    <th class="p-3 border">Amount</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Date</th>
                    <th class="p-3 border">Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td class="p-3 border"><?php echo htmlspecialchars($order['property_title']); ?></td>
                            <td class="p-3 border"><?php echo htmlspecialchars($order['property_location']); ?></td>
                            <td class="p-3 border">₦<?php echo number_format($order['paid_amount'], 2); ?></td>
                            <td
                                class="p-3 border 
                                <?php echo strtolower($order['transaction_status']) === 'completed' ? 'text-green-500' : 'text-red-500'; ?>">
                                <?php echo ucfirst($order['transaction_status']); ?>
                            </td>
                            <td class="p-3 border"><?php echo date("F j, Y, g:i a", strtotime($order['transaction_date'])); ?>
                            </td>
                            <td class="p-3 border"><?php echo htmlspecialchars($order['transaction_id']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="p-3 border text-center text-gray-500">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $stmt->close(); ?>