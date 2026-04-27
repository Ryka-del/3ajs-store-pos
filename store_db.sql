-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2025 at 01:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'bx-grid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `created_at`) VALUES
(1, 'Snacks', 'bx-cookie', '2025-09-01 18:00:00'),
(2, 'Beverages', 'bx-coffee-cup', '2025-09-01 18:00:00'),
(3, 'Canned Goods', 'bx-bowl-rice', '2025-09-01 18:00:00'),
(4, 'Instant Food', 'bx-baguette', '2025-09-01 18:00:00'),
(5, 'Frozen Goods', 'bx-cube', '2025-09-01 18:00:00'),
(6, 'Rice & Condiments', 'bx-bowl-hot', '2025-09-01 18:00:00'),
(7, 'Toiletries', 'bx-shower', '2025-09-01 18:00:00'),
(8, 'Household Supplies', 'bx-home', '2025-09-01 18:00:00'),
(9, 'Cigarettes/Alcohol', 'bx-wine', '2025-09-01 18:00:00'),
(10, 'Others', 'bx-mobile-alt', '2025-09-01 18:00:00'),
(11, 'All', 'bx-grid', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `payment_amount`, `created_at`) VALUES
(1, 1, 23.00, 24.00, '2025-09-04 22:01:24'),
(2, 1, 47.00, 100.00, '2025-09-05 03:27:01'),
(3, 1, 122.00, 200.00, '2025-09-05 03:27:43'),
(4, 1, 270.00, 500.00, '2025-09-05 22:10:11'),
(5, 1, 20.00, 20.00, '2025-09-07 01:22:33'),
(6, 1, 23.00, 40.00, '2025-09-07 01:22:59'),
(7, 1, 55.00, 100.00, '2025-09-07 01:25:57'),
(8, 1, 55.00, 60.00, '2025-09-07 01:26:15'),
(9, 1, 54.00, 122.00, '2025-09-07 01:35:05'),
(10, 1, 54.00, 100.00, '2025-09-08 17:54:51'),
(11, 1, 78.00, 100.00, '2025-09-08 20:42:40'),
(12, 1, 23.00, 25.00, '2025-09-08 21:04:56'),
(13, 1, 100.00, 100.00, '2025-09-08 21:17:20'),
(14, 1, 38.00, 50.00, '2025-09-08 21:18:06'),
(15, 1, 27.00, 30.00, '2025-09-08 21:20:38'),
(16, 1, 27.00, 30.00, '2025-09-08 21:20:39'),
(17, 1, 27.00, 30.00, '2025-09-08 21:20:40'),
(18, 1, 27.00, 30.00, '2025-09-08 21:20:41'),
(19, 1, 27.00, 30.00, '2025-09-08 21:20:42'),
(20, 1, 27.00, 30.00, '2025-09-08 21:20:42'),
(21, 1, 27.00, 30.00, '2025-09-08 21:20:43'),
(22, 1, 27.00, 30.00, '2025-09-08 21:20:43'),
(23, 1, 27.00, 30.00, '2025-09-08 21:21:17'),
(24, 1, 27.00, 30.00, '2025-09-08 21:21:18'),
(36, 1, 35.00, 40.00, '2025-09-08 21:27:34'),
(37, 1, 35.00, 40.00, '2025-09-08 21:27:35'),
(38, 1, 35.00, 40.00, '2025-09-08 21:27:35'),
(39, 1, 35.00, 40.00, '2025-09-08 21:27:35'),
(40, 1, 35.00, 40.00, '2025-09-08 21:27:36'),
(56, 1, 42.00, 50.00, '2025-09-08 21:37:00'),
(57, 1, 62.00, 100.00, '2025-09-08 21:40:06'),
(58, 1, 40.00, 50.00, '2025-09-08 21:40:49'),
(59, 1, 124.00, 200.00, '2025-09-08 21:50:40'),
(60, 1, 132.00, 200.00, '2025-09-08 21:51:00'),
(61, 1, 72.00, 80.00, '2025-09-08 21:52:52'),
(62, 1, 78.00, 100.00, '2025-09-09 21:38:30'),
(63, 1, 32.00, 50.00, '2025-09-13 21:06:32'),
(64, 1, 240.00, 300.00, '2025-09-13 21:09:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 11.00),
(2, 1, 2, 1, 12.00),
(3, 2, 1, 1, 11.00),
(4, 2, 2, 3, 12.00),
(5, 3, 1, 10, 11.00),
(6, 3, 2, 1, 12.00),
(7, 4, 6, 1, 23.00),
(8, 4, 11, 2, 42.00),
(9, 4, 12, 1, 55.00),
(10, 4, 13, 4, 15.00),
(11, 4, 15, 1, 18.00),
(12, 4, 21, 2, 15.00),
(13, 5, 7, 1, 20.00),
(14, 6, 6, 1, 23.00),
(15, 7, 12, 1, 55.00),
(16, 8, 12, 1, 55.00),
(17, 9, 2, 1, 12.00),
(18, 9, 11, 1, 42.00),
(19, 10, 2, 1, 12.00),
(20, 10, 11, 1, 42.00),
(21, 11, 6, 1, 23.00),
(22, 11, 12, 1, 55.00),
(23, 12, 6, 1, 23.00),
(24, 13, 8, 1, 70.00),
(25, 13, 19, 1, 30.00),
(26, 14, 6, 1, 23.00),
(27, 14, 21, 1, 15.00),
(28, 15, 10, 1, 15.00),
(29, 15, 23, 1, 12.00),
(30, 16, 10, 1, 15.00),
(31, 16, 23, 1, 12.00),
(32, 17, 10, 1, 15.00),
(33, 17, 23, 1, 12.00),
(34, 18, 10, 1, 15.00),
(35, 18, 23, 1, 12.00),
(36, 19, 10, 1, 15.00),
(37, 19, 23, 1, 12.00),
(38, 20, 10, 1, 15.00),
(39, 20, 23, 1, 12.00),
(40, 21, 10, 1, 15.00),
(41, 21, 23, 1, 12.00),
(42, 22, 10, 1, 15.00),
(43, 22, 23, 1, 12.00),
(44, 23, 10, 1, 15.00),
(45, 23, 23, 1, 12.00),
(46, 24, 10, 1, 15.00),
(47, 24, 23, 1, 12.00),
(48, 36, 6, 1, 23.00),
(49, 36, 23, 1, 12.00),
(50, 37, 6, 1, 23.00),
(51, 37, 23, 1, 12.00),
(52, 38, 6, 1, 23.00),
(53, 38, 23, 1, 12.00),
(54, 39, 6, 1, 23.00),
(55, 39, 23, 1, 12.00),
(56, 40, 6, 1, 23.00),
(57, 40, 23, 1, 12.00),
(73, 56, 11, 1, 42.00),
(74, 57, 7, 1, 20.00),
(75, 57, 11, 1, 42.00),
(76, 58, 7, 2, 20.00),
(77, 59, 2, 1, 12.00),
(78, 59, 11, 1, 42.00),
(79, 59, 12, 1, 55.00),
(80, 59, 21, 1, 15.00),
(81, 60, 2, 1, 12.00),
(82, 60, 6, 1, 23.00),
(83, 60, 11, 1, 42.00),
(84, 60, 12, 1, 55.00),
(85, 61, 15, 4, 18.00),
(86, 62, 2, 3, 12.00),
(87, 62, 11, 1, 42.00),
(88, 63, 2, 1, 12.00),
(89, 63, 24, 2, 10.00),
(90, 64, 5, 3, 25.00),
(91, 64, 13, 2, 15.00),
(92, 64, 16, 1, 45.00),
(93, 64, 19, 3, 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `barcode`, `name`, `category`, `price`, `cost_price`, `quantity`, `image_url`, `created_at`, `updated_at`) VALUES
(1, '4800361427289', 'Milo Champ Bites', 'Snacks', 11.00, 10.00, 15, 'uploads/1756879371_a06bcc9e2e898405900b6a3849ef68c8.jpg_960x960q80.jpg_.webp', '2025-09-03 06:02:51', '2025-09-05 13:06:41'),
(2, '748485401492', 'Birch Tree Fortified 33g', 'Rice & Condiments', 12.00, 10.00, 5, 'uploads/1756994020_69937056.webp', '2025-09-04 13:53:40', '2025-09-13 13:06:32'),
(5, '4807770251123', 'Piattos Cheese 40g', 'Snacks', 25.00, 18.00, 7, 'uploads/1757074909_1_b7666ca3-ad23-4859-8eb0-6ed33fd4e586_1024x1024@2x.webp', '2025-09-05 12:21:49', '2025-09-13 13:09:42'),
(6, '4807770012349', 'Chippy BBQ 110g', 'Snacks', 23.00, 15.00, 5, 'uploads/1757074956_4800016643095.png', '2025-09-05 12:22:36', '2025-09-13 13:04:13'),
(7, '4807770270013', 'Coke Mismo 295ml', 'Beverages', 20.00, 15.00, 12, 'uploads/1757075052_4801981118502.png', '2025-09-05 12:24:12', '2025-09-08 13:40:49'),
(8, '4807770180053', 'Sprite 1.5L', 'Beverages', 70.00, 55.00, 4, 'uploads/1757075106_sprite-15L.webp', '2025-09-05 12:25:06', '2025-09-08 13:17:20'),
(9, '4807770271218', 'Royal Tru-Orange 1L', 'Beverages', 65.00, 50.00, 5, 'uploads/1757075136_images.jpg', '2025-09-05 12:25:36', '2025-09-05 12:25:36'),
(10, '4807770123456', 'Nature Spring Water 500ml', 'Beverages', 15.00, 10.00, 10, 'uploads/1757075172_500ml.webp', '2025-09-05 12:26:12', '2025-09-13 13:25:28'),
(11, '4801981234567', 'Argentina Corned Beef 175g', 'Canned Goods', 42.00, 35.00, 7, 'uploads/1757075228_download.jpg', '2025-09-05 12:27:08', '2025-09-09 13:38:30'),
(12, '4800040250023', 'Century Tuna 180g', 'Canned Goods', 55.00, 45.00, 4, 'uploads/1757075261_images (1).jpg', '2025-09-05 12:27:41', '2025-09-08 13:51:00'),
(13, '4807770273674', 'Lucky Me Pancit Canton', 'Instant Food', 15.00, 12.00, 10, 'uploads/1757075296_4807770270123_copy1.jpg', '2025-09-05 12:28:16', '2025-09-13 13:17:11'),
(15, '4801981100987', 'Lucky Me Bulalo Flavor', 'Instant Food', 18.00, 14.00, 8, 'uploads/1757075484_OIP.jpg', '2025-09-05 12:31:24', '2025-09-08 13:52:52'),
(16, '4806517201235', 'Silver Swan Soy Sauce 1L', 'Rice & Condiments', 45.00, 35.00, 11, 'uploads/1757075526_download (1).jpg', '2025-09-05 12:32:06', '2025-09-13 13:09:42'),
(17, '4807770004569', 'Palmolive Shampoo 180ml', 'Toiletries', 120.00, 95.00, 6, 'uploads/1757075589_download (2).jpg', '2025-09-05 12:33:09', '2025-09-05 12:33:09'),
(18, '4807770250089', 'Safeguard Soap 90g', 'Toiletries', 35.00, 28.00, 8, 'uploads/1757075626_SM103388324-14.jpg', '2025-09-05 12:33:46', '2025-09-05 12:33:46'),
(19, '4801981145673', 'Surf Powder Detergent 350g', 'Household Supplies', 30.00, 22.00, 6, 'uploads/1757075688_download (3).jpg', '2025-09-05 12:34:48', '2025-09-13 13:09:42'),
(20, '4801981156782', 'Tide Powder 380g', 'Household Supplies', 15.00, 12.00, 10, 'uploads/1757075721_download (4).jpg', '2025-09-05 12:35:21', '2025-09-09 13:29:55'),
(21, '4801981198764', 'Great Taste White Coffee 3-in-1', 'Load & Others', 15.00, 12.00, 21, 'uploads/1757075811_download (5).jpg', '2025-09-05 12:36:51', '2025-09-08 13:50:40'),
(22, '4800361000058', 'Nescafe Classic 50g', 'Load & Others', 80.00, 65.00, 14, 'uploads/1757075850_download (6).jpg', '2025-09-05 12:37:30', '2025-09-05 13:07:11'),
(23, '4800361210098', 'Milo Sachet 22g', 'Rice & Condiments', 12.00, 9.00, 10, 'uploads/1757075885_download (7).jpg', '2025-09-05 12:38:05', '2025-09-13 13:20:47'),
(24, '4800092113307', 'Hansel Crackers', 'Snacks', 10.00, 8.00, 10, 'uploads/1757320996_hansel-mocha-sandwich.jpg', '2025-09-08 08:43:16', '2025-09-13 13:06:32'),
(25, '4800523441832', 'Tomi', 'Snacks', 9.00, 6.75, 10, 'uploads/1757769131_download__9_.jpg', '2025-09-13 13:12:11', '2025-09-13 13:13:25'),
(26, '', 'Asin', 'Others', 7.00, 5.00, 15, 'uploads/1757769848_images__2_.jpg', '2025-09-13 13:24:08', '2025-09-13 13:24:08'),
(27, '6952134281654', 'Angel Hair Wax', 'Others', 99.00, 85.00, 20, 'uploads/1758019716_images__3_.jpg', '2025-09-16 10:48:36', '2025-09-16 10:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','cashier') DEFAULT 'cashier',
  `fullname` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `fullname`, `created_at`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'Anastacio C. Lescano', '2025-08-28 13:54:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
