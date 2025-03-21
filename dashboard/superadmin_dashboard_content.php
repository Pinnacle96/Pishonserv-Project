<div class="mt-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-200">Superadmin Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Overview of platform activities and performance.</p>
        </div>
        <a href="zoho_auth.php"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Connect Zoho CRM
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition">
            <h3 class="text-gray-600 dark:text-gray-300 text-sm md:text-base">Total Users</h3>
            <p class="text-2xl md:text-3xl font-bold text-[#092468] dark:text-[#CC9933] mt-2">
                <?php
                $user_count = $conn->query("SELECT COUNT(id) AS count FROM users")->fetch_assoc();
                echo number_format($user_count['count']);
                ?>
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition">
            <h3 class="text-gray-600 dark:text-gray-300 text-sm md:text-base">Total Properties</h3>
            <p class="text-2xl md:text-3xl font-bold text-[#092468] dark:text-[#CC9933] mt-2">
                <?php
                $property_count = $conn->query("SELECT COUNT(id) AS count FROM properties")->fetch_assoc();
                echo number_format($property_count['count']);
                ?>
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition">
            <h3 class="text-gray-600 dark:text-gray-300 text-sm md:text-base">Total Earnings</h3>
            <p class="text-2xl md:text-3xl font-bold text-[#092468] dark:text-[#CC9933] mt-2">
                ₦<?php
                    $earnings = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE status='completed'")->fetch_assoc();
                    echo number_format($earnings['total'] ?? 0, 2);
                    ?>
            </p>
        </div>
    </div>

    <!-- Earnings Overview -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded-lg shadow-md">
        <h3 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Earnings Overview</h3>
        <canvas id="earningsChart" class="w-full h-64"></canvas>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fetch earnings data from PHP
    const earningsData = <?php
                                $query = "SELECT DATE_FORMAT(created_at, '%b') AS month, SUM(amount) AS total 
                      FROM transactions 
                      WHERE status = 'completed' 
                      GROUP BY YEAR(created_at), MONTH(created_at) 
                      ORDER BY created_at ASC 
                      LIMIT 6";
                                $result = $conn->query($query);
                                $labels = [];
                                $data = [];
                                while ($row = $result->fetch_assoc()) {
                                    $labels[] = $row['month'];
                                    $data[] = floatval($row['total']);
                                }
                                echo json_encode(['labels' => $labels, 'data' => $data]);
                                ?>;

    var ctx = document.getElementById("earningsChart").getContext("2d");
    var earningsChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: earningsData.labels.length ? earningsData.labels : ["No Data"],
            datasets: [{
                label: "Total Earnings (₦)",
                data: earningsData.data.length ? earningsData.data : [0],
                backgroundColor: "rgba(15, 82, 186, 0.6)", // #092468 with opacity
                borderColor: "rgba(15, 82, 186, 1)",
                borderWidth: 1,
                hoverBackgroundColor: "rgba(204, 153, 51, 0.8)", // #CC9933 with opacity
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#092468'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Earnings: ₦${context.raw.toLocaleString()}`;
                        }
                    }
                }
            }
        }
    });
});
</script>