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
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">;
    <script src="https://cdn.tailwindcss.com"></script>
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
                <li><a href="<?php echo $base_path; ?>index.php" class="hover:text-[#CC9933] transition">Home</a></li>
                <li><a href="<?php echo $base_path; ?>properties.php"
                        class="hover:text-[#CC9933] transition">Listings</a></li>
                <li><a href="<?php echo $base_path; ?>about.php" class="hover:text-[#CC9933] transition">About</a></li>
                <li><a href="<?php echo $base_path; ?>contact.php" class="hover:text-[#CC9933] transition">Contact</a>
                </li>
                <li><a href="<?php echo $base_path; ?>career.php" class="hover:text-[#CC9933] transition">Career</a>
                </li>
            </ul>

            <!-- Create Listing Button -->
            <a href="<?php echo $base_path; ?>create-listing.php"
                class="hidden md:inline-block bg-[#CC9933] text-white px-5 py-3 rounded-lg hover:bg-[#d88b1c] transition">
                Create Listing +
            </a>

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
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>index.php"
                        class="hover:text-[#CC9933]">Home</a></li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>properties.php"
                        class="hover:text-[#CC9933]">Listings</a></li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>about.php"
                        class="hover:text-[#CC9933]">About</a></li>
                <li class="py-3 border-b"><a href="<?php echo $base_path; ?>contact.php"
                        class="hover:text-[#CC9933]">Contact</a></li>
                <li><a href="<?php echo $base_path; ?>career.php" class="hover:text-[#CC9933] transition">Career</a>
                </li>
                <li class="py-3"><a href="<?php echo $base_path; ?>create-listing.php"
                        class="bg-[#CC9933] text-white px-6 py-3 rounded hover:bg-[#d88b1c]">Create Listing +</a></li>
            </ul>
        </div>
    </nav>