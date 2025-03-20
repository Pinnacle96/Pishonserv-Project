<?php
//session_start();
include '../includes/db_connect.php';

// ✅ Ensure User is Logged In
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in to access messages.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch Messages for the Logged-in User
$stmt = $conn->prepare("
    SELECT m.id, m.sender_id, m.receiver_id, m.sender_role, m.receiver_role, m.property_id, 
           m.message, m.status, m.created_at, 
           u.name AS sender_name, u.profile_image AS sender_image
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Messages</h2>
    <p class="text-gray-600 dark:text-gray-400">Chat with agents & admins.</p>

    <?php if ($messages->num_rows > 0): ?>
        <div class="space-y-6 mt-6">
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <div class="flex justify-between">
                        <div class="flex items-center">
                            <img src="../public/uploads/<?php echo $msg['sender_image'] ?: 'default.png'; ?>"
                                class="w-10 h-10 rounded-full mr-3">
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($msg['sender_name']); ?>
                                (<?php echo ucfirst($msg['sender_role']); ?>)</h3>
                        </div>
                        <p class="text-gray-500"><?php echo date("F j, Y, g:i a", strtotime($msg['created_at'])); ?></p>
                    </div>
                    <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($msg['message']); ?></p>
                    <div class="flex justify-between mt-4">
                        <button class="text-blue-500 reply-btn" data-msg-id="<?php echo $msg['id']; ?>"
                            data-sender-id="<?php echo $msg['sender_id']; ?>">Reply</button>
                        <a href="delete_message.php?id=<?php echo $msg['id']; ?>" class="text-red-500">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500 mt-4">No new messages.</p>
    <?php endif; ?>
</div>

<!-- Reply Modal (Hidden by Default) -->
<div id="replyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-96 shadow-lg">
        <h3 class="text-xl font-bold mb-4">Reply to Message</h3>
        <form id="replyForm" method="POST">
            <input type="hidden" name="receiver_id" id="receiver_id">
            <textarea name="message" id="replyMessage" class="w-full p-2 border rounded"
                placeholder="Type your message..." required></textarea>
            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Send
                Reply</button>
            <button type="button" class="mt-4 text-gray-500 close-modal">Cancel</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const replyButtons = document.querySelectorAll('.reply-btn');
        const replyModal = document.getElementById('replyModal');
        const replyForm = document.getElementById('replyForm');
        const receiverInput = document.getElementById('receiver_id');
        const replyMessage = document.getElementById('replyMessage');

        replyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const senderId = this.getAttribute('data-sender-id');
                receiverInput.value = senderId;
                replyMessage.value = "";
                replyModal.classList.remove('hidden');
            });
        });

        document.querySelector('.close-modal').addEventListener('click', function() {
            replyModal.classList.add('hidden');
        });

        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(replyForm);

            fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Reply sent successfully!");
                        replyModal.classList.add('hidden');
                    } else {
                        alert("Error sending reply. Try again.");
                    }
                });
        });
    });
</script>

<?php $stmt->close(); ?>