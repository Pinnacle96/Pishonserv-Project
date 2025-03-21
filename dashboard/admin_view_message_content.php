<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Message Details</h2>
    <p class="text-gray-600 dark:text-gray-400">View full details of the message.</p>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Message Information</h3>

        <div class="mb-4">
            <strong class="text-gray-700">From:</strong>
            <p class="text-gray-600">
                <?php echo htmlspecialchars($message['sender_name'] . " " . $message['sender_lname']); ?>
                (<a href="mailto:<?php echo $message['sender_email']; ?>"
                    class="text-blue-500"><?php echo $message['sender_email']; ?></a>)
            </p>
        </div>

        <div class="mb-4">
            <strong class="text-gray-700">To:</strong>
            <p class="text-gray-600">
                <?php echo htmlspecialchars($message['receiver_name'] . " " . $message['receiver_lname']); ?>
                (<a href="mailto:<?php echo $message['receiver_email']; ?>"
                    class="text-blue-500"><?php echo $message['receiver_email']; ?></a>)
            </p>
        </div>

        <div class="mb-4">
            <strong class="text-gray-700">Property:</strong>
            <p class="text-gray-600">
                <?php echo $message['property_title'] ? htmlspecialchars($message['property_title']) : "N/A"; ?>
            </p>
        </div>

        <div class="mb-4">
            <strong class="text-gray-700">Sent On:</strong>
            <p class="text-gray-600"><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></p>
        </div>

        <div class="mb-4">
            <strong class="text-gray-700">Message:</strong>
            <p class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-gray-700 dark:text-gray-300">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </p>
        </div>

        <div class="flex justify-between mt-6">
            <a href="admin_messages.php" class="bg-blue-600 text-white px-5 py-3 rounded hover:bg-blue-700">
                Back to Messages
            </a>
            <button type="button" id="deleteMessageBtn"
                class="bg-red-600 text-white px-5 py-3 rounded hover:bg-red-700">
                Delete Message
            </button>
        </div>
    </div>
</div>

<!-- Include SweetAlert Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript: SweetAlert Confirm Delete -->
<script>
    document.getElementById('deleteMessageBtn').addEventListener('click', function() {
        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit delete request
                fetch("../process/admin_delete_message.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "message_id=<?php echo $message['id']; ?>"
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "The message has been deleted.",
                                icon: "success"
                            }).then(() => {
                                window.location.href = "admin_messages.php";
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: data.error,
                                icon: "error"
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to delete the message.",
                            icon: "error"
                        });
                    });
            }
        });
    });
</script>