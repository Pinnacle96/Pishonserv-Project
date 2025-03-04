

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



--
-- Database: `real_estate_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `description`, `created_at`) VALUES
(4, 9, 'Admin approved a new property', '2025-02-26 11:30:43'),
(5, 3, 'Agent updated their profile', '2025-02-26 11:30:43'),
(6, 6, 'User made a successful transaction', '2025-02-26 11:30:43');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `sender_role` enum('buyer','agent','owner','hotel_owner','admin','superadmin') NOT NULL,
  `receiver_role` enum('buyer','agent','owner','hotel_owner','admin','superadmin') NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_gateway` enum('paystack','wallet','bnpl') NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(20,2) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `type` enum('house','apartment','land','shortlet','hotel') NOT NULL,
  `description` text NOT NULL,
  `owner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `images` text DEFAULT NULL,
  `status` enum('pending','available','sold','rented') DEFAULT 'pending',
  `admin_approved` tinyint(1) DEFAULT 0,
  `listing_type` enum('for_sale','for_rent','short_let') NOT NULL DEFAULT 'for_sale',
  `bedrooms` int(11) DEFAULT 1,
  `bathrooms` int(11) DEFAULT 1,
  `size` varchar(50) DEFAULT 'N/A',
  `garage` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `price`, `location`, `type`, `description`, `owner_id`, `created_at`, `images`, `status`, `admin_approved`, `listing_type`, `bedrooms`, `bathrooms`, `size`, `garage`) VALUES
(3, 'Apart', 200000000.00, 'Ikeja', 'apartment', 'gfshgh', 3, '2025-02-25 12:44:55', '67bdddf270ec1_leo_visions-fFqqVSi96z0-unsplash.jpg,67bdddf3128b1_jason-dent-w3eFhqXjkZE-unsplash.jpg,67bdddf38e148_anatolii-nesterov-pIqUc3A97V0-unsplash.jpg', 'available', 1, 'for_sale', 1, 1, 'N/A', 0),
(4, 'House', 200000000.00, 'Lagos', 'apartment', 'fdgdhggj', 3, '2025-02-25 13:42:51', '67bddde11507a_leo_visions-fFqqVSi96z0-unsplash.jpg,67bddde1a81ee_jason-dent-w3eFhqXjkZE-unsplash.jpg,67bddde22ecb8_anatolii-nesterov-pIqUc3A97V0-unsplash.jpg', 'sold', 1, 'for_sale', 1, 1, 'N/A', 0),
(5, 'Apartment', 200000000.00, 'Lagos', 'shortlet', 'fdhsjhklulsul', 7, '2025-02-26 11:12:27', '67c041b4b76d2_point3d-commercial-imaging-ltd-REl9gTW2YFM-unsplash.jpg,67c041b54759f_lotus-design-n-print-ZyaIBrLApiM-unsplash.jpg,67c041b5adbda_douglas-sheppard-9rYfG8sWRVo-unsplash.jpg,67c041b5c5af5_leo_visions-fFqqVSi96z0-unsplash.jpg', 'available', 1, 'for_rent', 1, 1, 'N/A', 0),
(6, 'Shorlet', 200000.00, 'Ikeja', 'shortlet', 'fdgsshgjhkjlhklgfhshfdgdfg', 9, '2025-02-27 11:01:33', '67c046539b2f2_point3d-commercial-imaging-ltd-REl9gTW2YFM-unsplash.jpg,67c04654219c0_lotus-design-n-print-ZyaIBrLApiM-unsplash.jpg,67c0465487b5f_douglas-sheppard-9rYfG8sWRVo-unsplash.jpg', 'available', 1, 'short_let', 1, 1, 'N/A', 0);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `commission` decimal(5,2) DEFAULT 0.00,
  `max_users` int(11) DEFAULT 100000000,
  `site_status` enum('active','maintenance','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('buyer','agent','owner','hotel_owner','admin','superadmin') NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `profile_image`, `otp`, `otp_expires_at`, `email_verified`, `created_at`, `phone`) VALUES
(3, 'Noah', 'grccglobal@gmail.com', '$2y$10$79vWV.dxeQAe37RKdAiitu5yaMN1oK3e8WgvLRG5IxlHv8cpeJany', 'agent', 'default.png', NULL, NULL, 1, '2025-02-20 21:47:19', NULL),
(4, 'Imanche', 'noah1@gmail.com', '$2y$10$p5v91xxn2K22GBB7uX21ce2BN6MPBTDVWdFeY/0NJrJpfxyyYtbi.', 'owner', 'default.png', NULL, '2025-02-21 16:25:24', 1, '2025-02-21 15:15:24', NULL),
(5, 'Imanche', 'noah@gmail.com', '$2y$10$vQsuKMa4Nm2n2xK7HMJq8OqTpfk1oZP264P0Bda.MuuYsr3Nnz5qi', 'buyer', 'default.png', NULL, NULL, 1, '2025-02-21 15:18:47', NULL),
(6, 'Noah', 'grc@gmail.com', '$2y$10$cxR9aZ9jUeR.eBbIHyjdpOgtjzytiBKrttEZRNDRz8VUZ8fmUhxoO', 'hotel_owner', 'default.png', NULL, NULL, 1, '2025-02-21 15:33:02', NULL),
(7, 'Noah', 'grcc@gmail.com', '$2y$10$yKpf6hai87uhjtKa2dT2wu7olH9Qa8Qi7ANG9hG6KluR3v.LeIx8m', 'hotel_owner', '67b8ae3823672.jpg', NULL, NULL, 1, '2025-02-21 16:47:52', NULL),
(8, 'Admin User', 'admin@example.com', '$2y$10$3FstVCpCILnnbuySSGMgtu/xWCxmdKshPVt7pbYGRtd18C7NhqZ2u', 'admin', 'default.png', NULL, NULL, 1, '2025-02-25 16:20:52', NULL),
(9, 'Super Admin', 'superadmin@example.com', '$2y$10$/Gzn8b7/X/m0P2vhbseDbuEXfhQ8KS8.qNwaXmwCWWBS4mDnl51US', 'superadmin', 'default.png', NULL, NULL, 1, '2025-02-25 16:20:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bank_name` varchar(255) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;


