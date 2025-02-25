<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Messages</h2>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Inbox</h3>

        <?php if ($result->num_rows > 0): ?>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
            <li class="p-3 border-b">
                <strong
                    class="text-blue-500"><?php echo ucfirst($row['sender_role']) . ' - ' . $row['sender_name']; ?>:</strong>
                <?php echo $row['message']; ?>
                <span
                    class="text-sm text-gray-500 float-right"><?php echo date("M d, Y H:i", strtotime($row['created_at'])); ?></span>

                <?php if ($row['status'] === 'unread'): ?>
                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">Unread</span>
                <?php endif; ?>
            </li>
            <?php endwhile; ?>
        </ul>
        <?php else: ?>
        <p class="text-center text-gray-500">No messages found.</p>
        <?php endif; ?>
    </div>

    <!-- Message Reply Form -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Send a Message</h3>
        <form action="../process/send_message.php" method="POST">
            <input type="hidden" name="sender_id" value="<?php echo $_SESSION['user_id']; ?>">
            <label class="block text-gray-600 dark:text-gray-300 mb-2">Select Receiver:</label>
            <select name="receiver_id" required class="w-full p-3 border rounded mb-3">
                <option value="">Choose a User</option>
                <?php
                $user_query = $conn->query("SELECT id, name, role FROM users WHERE id != $agent_id");
                while ($user = $user_query->fetch_assoc()) {
                    echo "<option value='{$user['id']}'>{$user['name']} ({$user['role']})</option>";
                }
                ?>
            </select>

            <label class="block text-gray-600 dark:text-gray-300 mb-2">Message:</label>
            <textarea name="message" required class="w-full p-3 border rounded mb-3"
                placeholder="Enter your message..."></textarea>

            <button type="submit" class="bg-[#F4A124] text-white px-5 py-3 rounded hover:bg-[#d88b1c]">
                Send Message
            </button>
        </form>
    </div>
</div>