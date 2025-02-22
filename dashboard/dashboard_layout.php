<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pishonserv</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
    };
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <?php
    if (isset($_SESSION['success'])) {
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: '" . $_SESSION['success'] . "',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>";
        unset($_SESSION['success']);
    }
    ?>

    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="w-64 md:w-72 bg-white dark:bg-gray-800 shadow-md h-screen p-6 fixed md:relative md:block transition-transform transform -translate-x-full md:translate-x-0">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300">Dashboard</h2>
                <button id="close-menu" class="md:hidden text-gray-700 dark:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <ul>
                <li class="mb-4"><a href="buyer_dashboard.php"
                        class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i class="fas fa-home"></i>
                        Dashboard</a></li>
                <li class="mb-4"><a href="buyer_orders.php"
                        class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i
                            class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li class="mb-4"><a href="buyer_wishlist.php"
                        class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i class="fas fa-heart"></i>
                        Wishlist</a></li>
                <li class="mb-4"><a href="buyer_messages.php"
                        class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i class="fas fa-comments"></i>
                        Messages</a></li>
                <li class="mb-4"><a href="buyer_profile.php"
                        class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i class="fas fa-user"></i>
                        Profile</a></li>
                <li class="mb-4"><a href="buyer_security.php"
                        class="text-gray-600 dark:text-gray-300 hover:text-blue-500"><i class="fas fa-lock"></i>
                        Security</a></li>
                <li><a href="../process/logout.php" class="text-gray-600 dark:text-gray-300 hover:text-red-500"><i
                            class="fas fa-sign-out-alt"></i> Logout</a></li>
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

            <!-- Dynamic Content -->
            <div class="content">
                <?php 
if (file_exists($page_content)) {
    include($page_content);
} else {
    echo "<p class='text-red-500 text-center'>Error: Content file not found.</p>";
}
?>

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

document.getElementById('menu-toggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

document.getElementById('close-menu').addEventListener('click', function() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
});
</script>

</html>