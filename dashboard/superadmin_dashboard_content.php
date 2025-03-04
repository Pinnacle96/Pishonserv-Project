<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Superadmin Dashboard</h2>
    <p class="text-gray-600 dark:text-gray-400">Overview of platform activities.</p>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Users</h3>
            <p class="text-2xl font-bold">
                <?php
                $user_count = $conn->query("SELECT COUNT(id) AS count FROM users")->fetch_assoc();
                echo $user_count['count'];
                ?>
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Properties</h3>
            <p class="text-2xl font-bold">
                <?php
                $property_count = $conn->query("SELECT COUNT(id) AS count FROM properties")->fetch_assoc();
                echo $property_count['count'];
                ?>
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
            <h3 class="text-gray-600 dark:text-gray-300">Total Earnings</h3>
            <p class="text-2xl font-bold">
                ₦<?php
                    $earnings = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE status='completed'")->fetch_assoc();
                    echo number_format($earnings['total'] ?? 0, 2);
                    ?>
            </p>
        </div>
    </div>

    <a href="zoho_auth.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Connect Zoho CRM
    </a>

    <!-- Transactions Chart -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Earnings Overview</h3>
        <canvas id="earningsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("earningsChart").getContext("2d");
        var earningsChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
                datasets: [{
                    label: "Total Earnings",
                    data: [12000, 15000, 11000, 18000, 17000, 21000],
                    backgroundColor: "rgba(255, 99, 132, 0.5)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>