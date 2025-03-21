<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Messages</h2>
    <p class="text-gray-600 dark:text-gray-400">View and manage messages between users.</p>

    <!-- Messages Table -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">All Messages</h3>

        <!-- Search Box -->
        <div class="mb-4">
            <input type="text" id="searchMessages" class="w-full p-3 border rounded" placeholder="Search messages...">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700" id="messagesTable">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Sender</th>
                        <th class="p-3 border">Receiver</th>
                        <th class="p-3 border">Message</th>
                        <th class="p-3 border">Property</th>
                        <th class="p-3 border">Date</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $messages->fetch_assoc()): ?>
                        <tr>
                            <td class="p-3 border">
                                <?php echo htmlspecialchars($row['sender_name'] . " " . $row['sender_lname']); ?>
                                <br><small
                                    class="text-gray-500"><?php echo htmlspecialchars($row['sender_email']); ?></small>
                            </td>
                            <td class="p-3 border">
                                <?php echo htmlspecialchars($row['receiver_name'] . " " . $row['receiver_lname']); ?>
                                <br><small
                                    class="text-gray-500"><?php echo htmlspecialchars($row['receiver_email']); ?></small>
                            </td>
                            <td class="p-3 border">
                                <?php echo nl2br(htmlspecialchars(substr($row['message'], 0, 50))) . "..."; ?>
                            </td>
                            <td class="p-3 border">
                                <?php echo htmlspecialchars($row['property_title'] ?? 'N/A'); ?>
                            </td>
                            <td class="p-3 border">
                                <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
                            </td>
                            <td
                                class="p-3 border font-bold <?php echo $row['status'] === 'unread' ? 'text-yellow-500' : 'text-green-500'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </td>
                            <td class="p-3 border flex space-x-3">
                                <a href="admin_view_message.php?id=<?php echo $row['id']; ?>" class="text-blue-500">View</a>
                                <form action="../process/admin_delete_message.php" method="POST"
                                    onsubmit="return confirmDelete();">
                                    <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="text-red-500">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript for Search & Delete Confirmation -->
<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this message?");
    }

    document.getElementById('searchMessages').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#messagesTable tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
</script>