<?php
include '../includes/db_connect.php';
include 'superadmin_reports_functions.php'; // ✅ Include the missing functions file

?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Reports & Analytics</h2>
    <p class="text-gray-600 dark:text-gray-400">Platform-wide insights and statistics.</p>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Users</h3>
            <p class="text-2xl font-bold"><?php echo getTotalUsers($conn); ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Properties</h3>
            <p class="text-2xl font-bold"><?php echo getTotalProperties($conn); ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Transactions</h3>
            <p class="text-2xl font-bold">₦<?php echo number_format(getTotalRevenue($conn), 2); ?></p>
        </div>
    </div>


    <!-- Transactions Overview -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-xl font-bold mb-4">Filter Analytics Reports</h3>
            <form method="GET" action="superadmin_reports.php">
                <div class="flex space-x-4">
                    <div>
                        <label class="block text-gray-700 font-semibold">Start Date</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"
                            class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold">End Date</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"
                            class="w-full p-2 border rounded">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <h3 class="text-xl font-bold mb-4">Transactions Overview</h3>
        <canvas id="transactionsChart"></canvas>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Recent Activities</h3>
        <ul>
            <?php
            $activities = getRecentActivities($conn);
            foreach ($activities as $activity) {
                echo "<li class='p-3 border-b text-gray-600 dark:text-gray-300'>{$activity}</li>";
            }
            ?>
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('transactionsChart').getContext('2d');
const transactionsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Completed', 'Pending', 'Failed'],
        datasets: [{
            label: 'Transactions',
            data: <?php echo json_encode(getTransactionStats($conn)); ?>,
            backgroundColor: ['#22c55e', '#facc15', '#ef4444']
        }]
    }
});
</script>