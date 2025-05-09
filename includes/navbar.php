<?php
include __DIR__ . '/../includes/db_connect.php'; //nsu
include __DIR__ . '/../includes/secure_headers.php'; // Import Zoho CRM functions re site status is checked before rendering

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $is_auth_page = (strpos($_SERVER['PHP_SELF'], "/auth/") !== false);
    $base_path = $is_auth_page ? "../" : "";
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Platform</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    .active-link {
  color: #CC9933 !important;
  font-weight: 600;
}
        .slider-image {
            transition: opacity 0.5s ease-in-out;
        }

        .property-details p {
            line-height: 1.6;
        }

        .key-features li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav id="navbar" class="fixed top-0 left-0 w-full bg-white shadow-md z-50">
        <div class="container mx-auto flex items-center justify-between py-4 px-6 md:px-10 lg:px-16">
            <!-- Logo -->
            <a href="<?php echo $base_path; ?>index.php" class="flex items-center space-x-2">
                <img src="<?php echo $base_path; ?>public/images/logo.png" alt="Logo" class="h-12">
            </a>

            <!-- Desktop Menu -->
            <ul class="hidden md:flex space-x-8 text-lg text-[#092468] font-medium">
                <li><a href="<?php echo $base_path; ?>index.php" class="nav-link hover:text-[#CC9933] transition">Home</a></li>
                <li><a href="<?php echo $base_path; ?>properties.php" class="nav-link hover:text-[#CC9933] transition">Listings</a></li>
                <li class="relative group">
                    <a href="#" class="nav-link hover:text-[#CC9933] transition">Interior</a>
                    <div class="absolute left-0 hidden group-hover:block bg-white shadow-md rounded mt-1 w-40 z-10">
                        <a href="<?php echo $base_path; ?>interior_deco.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Interior Deco</a>
                        <a href="<?php echo $base_path; ?>furniture.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Furniture</a>
                    </div>
                </li>
                <li><a href="<?php echo $base_path; ?>about.php" class="nav-link hover:text-[#CC9933] transition">About Us</a></li>
                <li><a href="<?php echo $base_path; ?>contact.php" class="nav-link hover:text-[#CC9933] transition">Contact</a></li>
                <li><a href="<?php echo $base_path; ?>career.php" class="nav-link hover:text-[#CC9933] transition">Career</a></li>
            </ul>

            <!-- Right Section: User and Create Listing -->
            <div class="flex items-center space-x-4">
                <!-- User Dropdown -->
                <div class="relative">
                    <button id="user-menu-btn" class="flex items-center text-[#092468] focus:outline-none">
                        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['name']) && isset($_SESSION['profile_image'])): ?>
                            <!-- Logged In: Show User Image and Name -->
                            <img src="<?php echo $base_path . 'public/uploads/' . htmlspecialchars($_SESSION['profile_image']); ?>"
                                alt="Profile" class="w-8 h-8 rounded-full mr-2 object-cover">
                            <span class="text-lg font-medium"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <?php else: ?>
                            <!-- Not Logged In: Show User Icon -->
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        <?php endif; ?>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="user-dropdown"
                        class="hidden absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg py-2 z-50">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <!-- Not Logged In -->
                            <a href="<?php echo $base_path; ?>auth/login.php"
                                class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Sign In</a>
                            <a href="<?php echo $base_path; ?>auth/register.php"
                                class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Sign Up</a>
                        <?php else: ?>
                            <!-- Logged In (Role-Based Menu) -->
                            <?php
                            $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'buyer'; // Default to 'buyer'
                            if ($user_role === 'buyer') {
                                echo '
                                    <a href="' . $base_path . 'dashboard/buyer_dashboard.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Dashboard</a>
                                    <a href="' . $base_path . 'dashboard/buyer_orders.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">My Orders</a>
                                    <a href="' . $base_path . 'dashboard/buyer_wishlist.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Wishlist</a>
                                    <a href="' . $base_path . 'dashboard/buyer_messages.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Messages</a>
                                    <a href="' . $base_path . 'dashboard/buyer_profile.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Profile</a>
                                    <a href="' . $base_path . 'dashboard/buyer_security.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Security</a>
                                ';
                            } elseif (in_array($user_role, ['agent', 'owner', 'hotel_owner','developer'])) {
                                echo '
                                    <a href="' . $base_path . 'dashboard/agent_dashboard.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Dashboard</a>
                                    <a href="' . $base_path . 'dashboard/agent_properties.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Properties</a>
                                    <a href="' . $base_path . 'dashboard/agent_inquiries.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Inquiries</a>
                                    <a href="' . $base_path . 'dashboard/agent_earnings.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Earnings</a>
                                    <a href="' . $base_path . 'dashboard/agent_transaction.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Transactions</a>
                                ';
                            } elseif ($user_role === 'admin') {
                                echo '
                                    <a href="' . $base_path . 'dashboard/admin_dashboard.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Dashboard</a>
                                    <a href="' . $base_path . 'dashboard/admin_users.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Manage Users</a>
                                    <a href="' . $base_path . 'dashboard/admin_properties.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Manage Properties</a>
                                    <a href="' . $base_path . 'dashboard/admin_transactions.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Transactions</a>
                                    <a href="' . $base_path . 'dashboard/admin_messages.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Messages</a>
                                ';
                            } elseif ($user_role === 'superadmin') {
                                echo '
                                    <a href="' . $base_path . 'dashboard/superadmin_dashboard.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Superadmin Dashboard</a>
                                    <a href="' . $base_path . 'dashboard/admin_users.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Manage Users</a>
                                    <a href="' . $base_path . 'dashboard/admin_properties.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Manage Properties</a>
                                    <a href="' . $base_path . 'dashboard/admin_transactions.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Transactions</a>
                                    <a href="' . $base_path . 'dashboard/admin_messages.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Messages</a>
                                    <a href="' . $base_path . 'dashboard/superadmin_manage.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Manage Admins</a>
                                    <a href="' . $base_path . 'dashboard/superadmin_reports.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Reports & Analytics</a>
                                    <a href="' . $base_path . 'dashboard/sync_to_zoho.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">Sync Existing User</a>
                                    <a href="' . $base_path . 'dashboard/superadmin_settings.php" class="block px-4 py-2 text-[#092468] hover:bg-gray-100">System Settings</a>
                                ';
                            }
                            ?>
                            <a href="<?php echo $base_path; ?>process/logout.php"
                                class="block px-4 py-2 text-red-500 hover:bg-gray-100">Logout</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Create Listing Button (Desktop Only) -->
                <a href="<?php echo $base_path; ?>dashboard/agent_properties.php"
                    class="hidden md:inline-block bg-[#CC9933] text-white px-5 py-3 rounded-lg hover:bg-[#d88b1c] transition">
                    Create Listing +
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-[#092468] focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu (Hidden by Default) -->
        <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-white shadow-md md:hidden">
            <ul class="text-center text-[#092468] text-lg">
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>index.php" class="hover:text-[#CC9933]">Home</a></li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>properties.php" class="hover:text-[#CC9933]">Listings</a></li>
                <li class="py-3 border-b relative group">
                    <span class="inline-block w-full hover:text-[#CC9933]">Interior</span>
                    <ul class="bg-white text-[#092468] text-sm hidden group-hover:block">
                        <li><a href="<?php echo $base_path; ?>interior_deco.php" class="block px-4 py-2 hover:bg-gray-100">Interior Deco</a></li>
                        <li><a href="<?php echo $base_path; ?>furniture.php" class="block px-4 py-2 hover:bg-gray-100">Furniture</a></li>
                    </ul>
                </li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>about.php" class="hover:text-[#CC9933]">About Us</a></li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>contact.php" class="hover:text-[#CC9933]">Contact</a></li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>career.php" class="hover:text-[#CC9933]">Career</a></li>
                <li class="py-3"><a href="<?php echo $base_path; ?>dashboard/agent_properties.php" class="bg-[#CC9933] text-white px-6 py-3 rounded hover:bg-[#d88b1c]">Create Listing +</a></li>
            </ul>
        </div>
    </nav>
</body>

</html>