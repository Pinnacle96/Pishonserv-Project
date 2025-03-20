<?php
//session_start();
include '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in to view your dashboard.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch Notifications
$notif_stmt = $conn->prepare("SELECT id, message FROM notifications WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC LIMIT 5");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();

// ✅ Fetch Total Orders
$order_stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM payments WHERE user_id = ?");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result()->fetch_assoc();
$total_orders = $order_result['total_orders'] ?? 0;

// ✅ Fetch Wishlist Count
$wishlist_stmt = $conn->prepare("SELECT COUNT(*) AS total_wishlist FROM wishlist WHERE user_id = ?");
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result()->fetch_assoc();
$total_wishlist = $wishlist_result['total_wishlist'] ?? 0;

// ✅ Fetch Unread Messages Count
$messages_stmt = $conn->prepare("SELECT COUNT(*) AS unread_messages FROM messages WHERE receiver_id = ? AND status = 'unread'");
$messages_stmt->bind_param("i", $user_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result()->fetch_assoc();
$total_messages = $messages_result['unread_messages'] ?? 0;

// ✅ Fetch Recent Messages
$message_list_stmt = $conn->prepare("SELECT sender_id, message, status, created_at FROM messages 
                                     WHERE receiver_id = ? 
                                     ORDER BY created_at DESC LIMIT 5");
$message_list_stmt->bind_param("i", $user_id);
$message_list_stmt->execute();
$messages = $message_list_stmt->get_result();

// ✅ Fetch Recent Orders
$order_list_stmt = $conn->prepare("
    SELECT p.title AS property_title, 
           pay.amount AS paid_amount, 
           pay.status AS transaction_status 
    FROM payments pay
    JOIN properties p ON pay.property_id = p.id
    WHERE pay.user_id = ?
    ORDER BY pay.created_at DESC
    LIMIT 5
");
$order_list_stmt->bind_param("i", $user_id);
$order_list_stmt->execute();
$orders = $order_list_stmt->get_result();
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Welcome,
        <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'User'; ?>
    </h2>
    <p class="text-gray-600 dark:text-gray-400">Manage your properties, orders, and wishlist.</p>

    <!-- Notifications Section -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">🔔 Notifications</h3>
        <div class="overflow-x-auto">
            <?php if ($notifications->num_rows > 0): ?>
                <ul>
                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <li class="border-b py-2 flex justify-between">
                            <span><?php echo htmlspecialchars($notif['message']); ?></span>
                            <a href="clear_notification.php?id=<?php echo $notif['id']; ?>" class="text-blue-500 ml-4">Mark as
                                Read</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Orders</h3>
            <p class="text-2xl font-bold"><?php echo $total_orders; ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Wishlist Items</h3>
            <p class="text-2xl font-bold"><?php echo $total_wishlist; ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Messages</h3>
            <p class="text-2xl font-bold"><?php echo $total_messages; ?></p>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">📩 Recent Messages</h3>
        <div class="overflow-x-auto">
            <?php if ($messages->num_rows > 0): ?>
                <ul>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <li class="border-b py-2">
                            <span class="<?php echo ($msg['status'] == 'unread') ? 'font-bold' : ''; ?>">
                                <?php echo htmlspecialchars($msg['message']); ?>
                            </span>
                            <small class="text-gray-400"> -
                                <?php echo date("M d, Y H:i", strtotime($msg['created_at'])); ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No new messages.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Recent Orders</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Property</th>
                        <th class="p-3 border">Amount</th>
                        <th class="p-3 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td class="p-3 border"><?php echo htmlspecialchars($order['property_title']); ?></td>
                                <td class="p-3 border">₦<?php echo number_format($order['paid_amount'], 2); ?></td>
                                <td
                                    class="p-3 border 
                                    <?php echo strtolower($order['transaction_status']) === 'completed' ? 'text-green-500' : 'text-red-500'; ?>">
                                    <?php echo ucfirst($order['transaction_status']); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="p-3 border text-center text-gray-500">No recent transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Close statements
$notif_stmt->close();
$order_stmt->close();
$wishlist_stmt->close();
$messages_stmt->close();
$message_list_stmt->close();
$order_list_stmt->close();
?>