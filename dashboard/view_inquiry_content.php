<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800">Inquiry Details</h2>

    <div class="bg-white p-6 rounded shadow-md mt-4">
        <p><strong>Buyer:</strong> <?php echo htmlspecialchars($inquiry['buyer_name']); ?></p>
        <p><strong>Property:</strong> <?php echo htmlspecialchars($inquiry['property_name']); ?></p>
        <p><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
        <p><strong>Sent On:</strong> <?php echo date("F j, Y, g:i a", strtotime($inquiry['created_at'])); ?></p>
    </div>

    <!-- Reply Form -->
    <div class="mt-6">
        <h3 class="text-xl font-bold">Reply to Inquiry</h3>
        <form action="reply_inquiry.php" method="POST" class="bg-gray-100 p-6 rounded-lg mt-4">
            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry_id; ?>">
            <input type="hidden" name="buyer_id" value="<?php echo $inquiry['sender_id']; ?>">
            <input type="hidden" name="property_id" value="<?php echo $inquiry['property_id']; ?>">

            <label class="block font-semibold">Your Reply:</label>
            <textarea name="reply_message" required class="w-full p-3 border rounded mt-1" rows="4"></textarea>

            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Send Reply
            </button>
        </form>
    </div>
</div>