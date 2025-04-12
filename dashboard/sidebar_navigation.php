<?php
// Ensure session is started
if (!isset($_SESSION)) {
        session_start();
}

// Fetch user details
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'buyer';
$user_name = $_SESSION['name'] ?? 'User';
$profile_image = $_SESSION['profile_image'] ?? 'default.png';
?>

<!-- Sidebar -->


<!-- Navigation Links -->
<ul>
        <?php if ($user_role === 'buyer') { ?>
                <li><a href="buyer_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="buyer_orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li><a href="buyer_wishlist.php" class="sidebar-link"><i class="fas fa-heart"></i> Wishlist</a></li>
                <li><a href="buyer_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> Messages</a></li>
                <li><a href="buyer_profile.php" class="sidebar-link"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="buyer_security.php" class="sidebar-link"><i class="fas fa-lock"></i> Security</a></li>
        <?php } elseif (in_array($user_role, ['agent', 'owner', 'hotel_owner'])) { ?>
                <li><a href="agent_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="agent_mou.php" class="sidebar-link"><i class="fas fa-envelope"></i> Download MOU</a></li>
                <li><a href="agent_properties.php" class="sidebar-link"><i class="fas fa-building"></i> Properties</a></li>
                <li><a href="agent_inquiries.php" class="sidebar-link"><i class="fas fa-envelope"></i> Inquiries</a></li>
                <!-- <li><a href="view_inquiry.php" class="sidebar-link"><i class="fas fa-envelope"></i> view inquiry</a></li> -->
                <li><a href="agent_earnings.php" class="sidebar-link"><i class="fas fa-wallet"></i> Earnings</a></li>
                <li><a href="agent_transaction.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i>
                                Transactions</a></li>
        <?php } elseif ($user_role === 'admin') { ?>
                <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="admin_users.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="admin_properties.php" class="sidebar-link"><i class="fas fa-building"></i> Manage Properties</a>
                </li>
                <li><a href="admin_transactions.php" class="sidebar-link"><i class="fas fa-money-bill"></i> Transactions</a>
                </li>
                <li><a href="admin_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> Messages</a></li>
        <?php } elseif ($user_role === 'superadmin') { ?>
                <li><a href="superadmin_dashboard.php" class="sidebar-link"><i class="fas fa-user-shield"></i> Superadmin
                                Dashboard</a></li>
                <li><a href="admin_users.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="admin_properties.php" class="sidebar-link"><i class="fas fa-building"></i> Manage Properties</a>
                </li>
                <li><a href="admin_transactions.php" class="sidebar-link"><i class="fas fa-money-bill"></i> Transactions</a>
                </li>
                <li><a href="admin_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> Messages</a></li>
                <li><a href="superadmin_manage.php" class="sidebar-link"><i class="fas fa-users-cog"></i> Manage Admins</a></li>
                <li><a href="superadmin_reports.php" class="sidebar-link"><i class="fas fa-chart-line"></i> Reports &
                                Analytics</a></li>
                <li><a href="sync_to_zoho.php" class="sidebar-link"><i class="fas fa-cogs"></i> Sync Existing user</a></li>
                <li><a href="superadmin_settings.php" class="sidebar-link"><i class="fas fa-cogs"></i> System Settings</a></li>
        <?php } ?>
        <li><a href="../process/logout.php" class="sidebar-link text-red-500"><i class="fas fa-sign-out-alt"></i>
                        Logout</a></li>
</ul>
</aside>