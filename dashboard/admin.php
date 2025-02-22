<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
    };
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="w-64 bg-white dark:bg-gray-800 shadow-md h-full p-6 fixed md:relative md:block transition-transform transform -translate-x-full md:translate-x-0">
            <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 mb-6">Admin Dashboard</h2>
            <ul>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-chart-line"></i> Dashboard</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-user"></i> Users & Roles</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-building"></i> Properties</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-money-bill"></i> Transactions</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-chart-bar"></i> Analytics</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-comments"></i> Messages</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-bell"></i> Notifications</a></li>
                <li class="mb-4"><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-wallet"></i> Wallet System</a></li>
                <li><a href="#" class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i class="fas fa-cog"></i>
                        Settings</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 ">
            <!-- Navbar -->
            <nav class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-900 dark:text-gray-100">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <span class="text-lg font-bold">Dashboard</span>
                <div class="flex items-center">
                    <button id="dark-mode-toggle" class="mr-4 p-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block"></i>
                    </button>
                    <i class="fas fa-bell text-gray-600 dark:text-gray-300 mx-4"></i>
                    <i class="fas fa-user text-gray-600 dark:text-gray-300"></i>
                </div>
            </nav>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
                    <h3 class="text-gray-600 dark:text-gray-300">Total Users</h3>
                    <p class="text-2xl font-bold">1,245</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
                    <h3 class="text-gray-600 dark:text-gray-300">Total Properties</h3>
                    <p class="text-2xl font-bold">532</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md">
                    <h3 class="text-gray-600 dark:text-gray-300">Earnings</h3>
                    <p class="text-2xl font-bold">$50,245</p>
                </div>
            </div>

            <!-- Analytics Chart -->
            <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
                <h3 class="text-xl font-bold mb-4">Analytics</h3>
                <canvas id="analyticsChart"></canvas>
            </div>

            <!-- Recent Transactions Table -->
            <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
                <h3 class="text-xl font-bold mb-4">Recent Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                                <th class="p-3 border">User</th>
                                <th class="p-3 border">Amount</th>
                                <th class="p-3 border">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-3 border">John Doe</td>
                                <td class="p-3 border">$500</td>
                                <td class="p-3 border text-green-500">Completed</td>
                            </tr>
                            <tr>
                                <td class="p-3 border">Jane Smith</td>
                                <td class="p-3 border">$320</td>
                                <td class="p-3 border text-red-500">Failed</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

<script>
document.getElementById('dark-mode-toggle').addEventListener('click', function() {
    document.documentElement.classList.toggle('dark');
});

document.getElementById('menu-toggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>


</html>