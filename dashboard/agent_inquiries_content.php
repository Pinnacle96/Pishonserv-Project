<?php
include '../includes/db_connect.php';

$agent_id = $_SESSION['user_id'];

// Fetch inquiries (messages) sent to the agent
$stmt = $conn->prepare("
    SELECT m.*, u.name AS buyer_name, p.title AS property_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    JOIN properties p ON m.property_id = p.id
    WHERE m.receiver_id = ? AND m.receiver_role = 'agent'
    ORDER BY m.created_at DESC
");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$inquiries = $stmt->get_result();
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800">Property Inquiries</h2>

    <table class="mt-6 w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-100 text-gray-900">
                <th class="p-3 border">Buyer</th>
                <th class="p-3 border">Property</th>
                <th class="p-3 border">Message</th>
                <th class="p-3 border">Date</th>
                <th class="p-3 border">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($inquiries->num_rows > 0): ?>
                <?php while ($inquiry = $inquiries->fetch_assoc()): ?>
                    <tr class="<?php echo $inquiry['status'] == 'unread' ? 'bg-yellow-50' : ''; ?>">
                        <td class="p-3 border"><?php echo htmlspecialchars($inquiry['buyer_name']); ?></td>
                        <td class="p-3 border"><?php echo htmlspecialchars($inquiry['property_name']); ?></td>
                        <td class="p-3 border"><?php echo substr(htmlspecialchars($inquiry['message']), 0, 50) . '...'; ?></td>
                        <td class="p-3 border"><?php echo date("F j, Y", strtotime($inquiry['created_at'])); ?></td>
                        <td class="p-3 border">
                            <a href="agent_dashboard.php?page=view_inquiry&id=<?php echo $inquiry['id']; ?>"
                                class="bg-blue-600 text-white px-3 py-1 rounded">View</a>
                            <?php if ($inquiry['status'] == 'unread'): ?>
                                <a href="agent_dashboard.php?page=mark_as_read&id=<?php echo $inquiry['id']; ?>"
                                    class="bg-green-600 text-white px-3 py-1 rounded">Mark as Read</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="p-3 border text-center">No inquiries found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>