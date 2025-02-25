<?php
//session_start();
include '../includes/db_connect.php';

// Ensure only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'buyer';
$user_name = $_SESSION['name'] ?? 'User';
$profile_image = $_SESSION['profile_image'] ?? 'default.png';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pishonserv</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    tailwind.config = {
        darkMode: 'class'
    };
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <!-- Success Alert -->
    <?php if (isset($_SESSION['success'])): ?>
    <script>
    Swal.fire({
        title: 'Success!',
        text: '<?php echo $_SESSION['success']; ?>',
        icon: 'success',
        confirmButtonText: 'OK'
    });
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="w-72 bg-white dark:bg-gray-800 shadow-md h-screen p-6 fixed md:relative md:block transition-transform transform -translate-x-full md:translate-x-0">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300">Dashboard</h2>
                <button id="close-menu" class="md:hidden text-gray-700 dark:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <ul>
                <?php
                if ($role === 'buyer') { ?>
                <li><a href="buyer_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="buyer_orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> My Orders</a>
                </li>
                <li><a href="buyer_wishlist.php" class="sidebar-link"><i class="fas fa-heart"></i> Wishlist</a></li>
                <li><a href="buyer_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> Messages</a></li>
                <li><a href="buyer_profile.php" class="sidebar-link"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="buyer_security.php" class="sidebar-link"><i class="fas fa-lock"></i> Security</a></li>
                <?php } elseif ($role === 'agent' || $role === 'owner' || $role === 'hotel_owner') { ?>
                <li><a href="agent_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="agent_properties.php" class="sidebar-link"><i class="fas fa-building"></i> Properties</a>
                </li>
                <li><a href="agent_messages.php" class="sidebar-link"><i class="fas fa-envelope"></i> Inquiries</a></li>
                <li><a href="agent_earnings.php" class="sidebar-link"><i class="fas fa-wallet"></i> Earnings</a></li>
                <li><a href="agent_transaction.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i>
                        Transactions</a></li>
                <?php } elseif ($role === 'admin' || $role === 'superadmin') { ?>
                <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="admin_users.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="admin_properties.php" class="sidebar-link"><i class="fas fa-building"></i> Manage
                        Properties</a></li>
                <li><a href="admin_transactions.php" class="sidebar-link"><i class="fas fa-money-bill"></i>
                        Transactions</a></li>
                <li><a href="admin_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> Messages</a></li>
                <li><a href="superadmin_manage.php" class="sidebar-link"><i class="fas fa-users-cog"></i> Manage
                        Admins</a></li>
                <li><a href="superadmin_reports.php" class="sidebar-link"><i class="fas fa-chart-line"></i> Reports &
                        Analytics</a></li>
                <li><a href="superadmin_settings.php" class="sidebar-link"><i class="fas fa-cogs"></i> System
                        Settings</a></li>
                <?php } ?>
                <li><a href="../process/logout.php" class="sidebar-link text-red-500"><i
                            class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Navbar -->
            <nav class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
                <button id="menu-toggle" class="md:hidden text-gray-900 dark:text-gray-100">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <span class="text-lg font-bold">Welcome, <?php echo $user_name; ?> 👋</span>
                <div class="flex items-center space-x-4">
                    <button id="dark-mode-toggle"
                        class="p-2 bg-gray-200 dark:bg-gray-700 rounded-full focus:outline-none">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block"></i>
                    </button>
                    <i class="fas fa-bell text-gray-600 dark:text-gray-300 cursor-pointer"></i>
                    <div class="relative">
                        <img src="../public/images/<?php echo $profile_image; ?>" alt="Profile"
                            class="w-10 h-10 rounded-full cursor-pointer" onclick="toggleDropdown()">
                        <div id="dropdown-menu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 shadow-lg rounded-md">
                            <a href="buyer_profile.php"
                                class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                View Profile</a>
                            <a href="buyer_security.php"
                                class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                Edit Profile</a>
                            <a href="../process/logout.php"
                                class="block px-4 py-2 text-red-500 hover:bg-gray-200 dark:hover:bg-gray-600">
                                Logout</a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="content">
                <?php include($page_content); ?>
            </div>
            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 shadow mt-10 p-4 text-center">
                <p class="text-gray-600 dark:text-gray-300">&copy; <?php echo date('Y'); ?> Dashboard. All rights
                    reserved.</p>
            </footer>
        </main>
    </div>

</body>
<script>
document.getElementById('dark-mode-toggle').addEventListener('click', function() {
    document.documentElement.classList.toggle('dark');
});

function toggleDropdown() {
    document.getElementById("dropdown-menu").classList.toggle("hidden");
}
document.getElementById('menu-toggle').addEventListener('click', () => document.getElementById('sidebar').classList
    .toggle('-translate-x-full'));
document.getElementById('close-menu').addEventListener('click', () => document.getElementById('sidebar').classList.add(
    '-translate-x-full'));
</script>

</html>