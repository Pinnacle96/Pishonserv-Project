<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Transaction Details</h2>
    <p class="text-gray-600 dark:text-gray-400">View the full details of this transaction.</p>

    <!-- Transaction Summary -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Transaction Info</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction['transaction_id']); ?></p>
                <p><strong>Amount:</strong> ₦<?php echo number_format($transaction['amount'], 2); ?></p>
                <p><strong>Status:</strong>
                    <span
                        class="<?php echo $transaction['status'] === 'completed' ? 'text-green-500' : ($transaction['status'] === 'pending' ? 'text-yellow-500' : 'text-red-500'); ?>">
                        <?php echo ucfirst(htmlspecialchars($transaction['status'])); ?>
                    </span>
                </p>
                <p><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($transaction['created_at'])); ?></p>
            </div>
            <div>
                <p><strong>Payment Method:</strong>
                    <?php echo htmlspecialchars($transaction['payment_gateway'] ?? 'N/A'); ?></p>
                <!-- <p><strong>Reference:</strong>
                    <//?php echo htmlspecialchars($transaction['transaction_type'] ?? 'N/A'); ?></p> -->
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">User Info</h3>
        <p><strong>Name:</strong>
            <?php echo htmlspecialchars($transaction['user_name'] . " " . $transaction['user_lname']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($transaction['user_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($transaction['user_phone'] ?? 'N/A'); ?></p>
    </div>

    <!-- Property Info (If applicable) -->
    <?php if (!empty($transaction['property_title'])): ?>
        <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
            <h3 class="text-xl font-bold mb-4">Property Info</h3>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($transaction['property_title']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($transaction['property_location']); ?></p>
            <p><strong>Price:</strong> ₦<?php echo number_format($transaction['property_price'], 2); ?></p>
        </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="admin_transactions.php" class="px-5 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
            ← Back to Transactions
        </a>
    </div>
</div>