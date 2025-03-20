<?php
//session_start();
include '../includes/db_connect.php';

// Logged-in agent ID
$agent_id = $_SESSION['user_id'];

// Fetch Total Properties listed by agent
$stmt = $conn->prepare("SELECT COUNT(*) AS total_properties FROM properties WHERE owner_id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$total_properties = $result->fetch_assoc()['total_properties'] ?? 0;

// Fetch Pending Inquiries (Unread Messages)
$stmt = $conn->prepare("SELECT COUNT(*) AS pending_inquiries FROM messages WHERE receiver_id = ? AND receiver_role = 'agent' AND status = 'unread'");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_inquiries = $result->fetch_assoc()['pending_inquiries'] ?? 0;

// Fetch Total Earnings
$stmt = $conn->prepare("SELECT SUM(amount) AS total_earnings FROM transactions WHERE user_id = ? AND status = 'completed'");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$total_earnings = $result->fetch_assoc()['total_earnings'] ?? 0;
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Welcome, <?php echo $_SESSION['name']; ?></h2>
    <p class="text-gray-600 dark:text-gray-400">Manage your listings, inquiries, and earnings.</p>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Properties</h3>
            <p class="text-2xl font-bold"><?php echo $total_properties; ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Pending Inquiries</h3>
            <p class="text-2xl font-bold"><?php echo $pending_inquiries; ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Earnings</h3>
            <p class="text-2xl font-bold">₦<?php echo number_format($total_earnings, 2); ?></p>
        </div>
    </div>

    <!-- Recent Inquiries -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Recent Inquiries</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Buyer</th>
                        <th class="p-3 border">Property</th>
                        <th class="p-3 border">Message</th>
                        <th class="p-3 border">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch the latest inquiries (messages)
                    $stmt = $conn->prepare("
                        SELECT m.*, u.name AS buyer_name, p.title AS property_name 
                        FROM messages m
                        JOIN users u ON m.sender_id = u.id
                        JOIN properties p ON m.property_id = p.id
                        WHERE m.receiver_id = ? AND m.receiver_role = 'agent'
                        ORDER BY m.created_at DESC
                        LIMIT 5
                    ");
                    $stmt->bind_param("i", $agent_id);
                    $stmt->execute();
                    $inquiries = $stmt->get_result();

                    if ($inquiries->num_rows > 0) {
                        while ($inquiry = $inquiries->fetch_assoc()) {
                            echo "<tr>
                                <td class='p-3 border'>{$inquiry['buyer_name']}</td>
                                <td class='p-3 border'>{$inquiry['property_name']}</td>
                                <td class='p-3 border'>" . substr($inquiry['message'], 0, 50) . "...</td>
                                <td class='p-3 border'>" . date("F j, Y", strtotime($inquiry['created_at'])) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='p-3 border text-center'>No inquiries found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>