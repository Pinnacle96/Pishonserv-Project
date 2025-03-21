<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Transactions</h2>
    <p class="text-gray-600 dark:text-gray-400">View and manage all payments and transactions.</p>

    <!-- Search & Filters -->
    <div class="flex justify-between items-center mt-4">
        <input type="text" id="search" placeholder="Search Transactions..."
            class="w-full md:w-1/3 p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500">
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Transaction History</h3>
        <div class="overflow-x-auto">
            <table id="transactionTable" class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Transaction ID</th>
                        <th class="p-3 border">User</th>
                        <th class="p-3 border">Amount (₦)</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Date</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    // Pagination settings
                    $limit = 10;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Fetch transactions with pagination
                    $transactions = $conn->query("
                        SELECT t.id, t.transaction_id, t.amount, t.status, t.created_at, 
                               u.name, u.lname, u.email
                        FROM payments t
                        JOIN users u ON t.user_id = u.id
                        ORDER BY t.created_at DESC
                        LIMIT $limit OFFSET $offset
                    ");

                    while ($row = $transactions->fetch_assoc()) {
                        $statusClass = $row['status'] === 'completed' ? 'text-green-500' : ($row['status'] === 'pending' ? 'text-yellow-500' : 'text-red-500');

                        echo "<tr class='table-row'>
                            <td class='p-3 border'>{$row['transaction_id']}</td>
                            <td class='p-3 border'>{$row['name']} {$row['lname']} <br> <small class='text-gray-500'>{$row['email']}</small></td>
                            <td class='p-3 border'>₦" . number_format($row['amount'], 2) . "</td>
                            <td class='p-3 border font-bold $statusClass'>" . ucfirst($row['status']) . "</td>
                            <td class='p-3 border'>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>
                            <td class='p-3 border flex space-x-3'>
                                <a href='admin_view_transaction.php?id={$row['id']}' class='text-blue-500'>View</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM payments");
        $total_transactions = $result->fetch_assoc()['total'];
        $total_pages = ceil($total_transactions / $limit);
        ?>

        <div class="flex justify-center mt-6">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>"
                    class="px-4 py-2 bg-gray-200 rounded-l-lg hover:bg-gray-300 text-sm md:text-base">← Previous</a>
            <?php endif; ?>

            <span class="px-4 py-2 bg-[#CC9933] text-white"><?= $page ?> / <?= $total_pages ?></span>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>"
                    class="px-4 py-2 bg-gray-200 rounded-r-lg hover:bg-gray-300 text-sm md:text-base">Next →</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for Search and Filtering -->
<script>
    document.getElementById('search').addEventListener('keyup', function() {
        let searchValue = this.value.toLowerCase();
        let rows = document.querySelectorAll('.table-row');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
</script>