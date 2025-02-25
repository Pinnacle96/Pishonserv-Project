<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Transaction History</h2>

    <div class="flex justify-between items-center mt-4">
        <div>
            <a href="?export_pdf=1" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Export PDF
            </a>
        </div>
        <div>
            <select onchange="location = this.value;" class="p-2 border rounded">
                <option value="agent_transaction.php">All Transactions</option>
                <option value="agent_transaction.php?type=credit">Credits</option>
                <option value="agent_transaction.php?type=debit">Debits</option>
            </select>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                    <th class="p-3 border">Amount</th>
                    <th class="p-3 border">Type</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="p-3 border">₦<?php echo number_format($row['amount'], 2); ?></td>
                    <td class="p-3 border"><?php echo ucfirst($row['type']); ?></td>
                    <td class="p-3 border"><?php echo ucfirst($row['status']); ?></td>
                    <td class="p-3 border"><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>