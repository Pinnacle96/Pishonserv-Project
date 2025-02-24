<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Messages</h2>
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Buyer Inquiries</h3>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
            <li class="p-3 border-b">
                <strong><?php echo $row['buyer_name']; ?>:</strong> <?php echo $row['message']; ?>
                <span class="text-sm text-gray-500"><?php echo $row['created_at']; ?></span>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>