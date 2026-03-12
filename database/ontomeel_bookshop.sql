ontomeel_bookshopontomeel_bookshop-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2026 at 01:25 PM
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
-- Database: `ontomeel_bookshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('SuperAdmin','Manager','Editor') DEFAULT 'Editor',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@ontomeel.com', '$2y$10$Vs2Zt498giPSkmHRNSjVUuk4000yOI9Kc2m8u0ovUoRu5gyzQPr0.', 'Ontomeel Admin', 'SuperAdmin', NULL, '2026-03-09 01:15:17'),
(2, 'abir', 'info.nowshad@proton.me', '$2y$10$hXxyQCQ3nAYYJimapNmgX.k6oCkff5IGzUoi7hYNabVCNzc5ukJLe', 'Abir', 'SuperAdmin', '2026-03-12 17:30:41', '2026-03-09 02:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `language` varchar(50) DEFAULT 'Bengali',
  `author` varchar(150) NOT NULL,
  `co_author` varchar(150) DEFAULT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `publish_year` varchar(4) DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `format` enum('Hardcover','Paperback','E-book') DEFAULT 'Paperback',
  `page_count` int(11) DEFAULT 0,
  `book_condition` enum('New','Used','Damaged') DEFAULT 'New',
  `shelf_location` varchar(50) DEFAULT NULL,
  `rack_number` varchar(50) DEFAULT NULL,
  `stock_qty` int(11) DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 2,
  `is_borrowable` tinyint(1) DEFAULT 1,
  `is_suggested` tinyint(1) DEFAULT 0,
  `purchase_price` decimal(10,2) DEFAULT 0.00,
  `sell_price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT 0.00,
  `supplier_name` varchar(150) DEFAULT NULL,
  `supplier_contact` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `photo_2` varchar(255) DEFAULT NULL,
  `photo_3` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `subtitle`, `description`, `category_id`, `genre`, `language`, `author`, `co_author`, `publisher`, `publish_year`, `edition`, `isbn`, `format`, `page_count`, `book_condition`, `shelf_location`, `rack_number`, `stock_qty`, `min_stock_level`, `is_borrowable`, `is_suggested`, `purchase_price`, `sell_price`, `discount_price`, `supplier_name`, `supplier_contact`, `cover_image`, `photo_2`, `photo_3`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'পথের পাঁচালী', NULL, 'বিভূতিভূষণ বন্দ্যোপাধ্যায়ের অমর সৃষ্টি।', 1, NULL, 'Bengali', 'বিভূতিভূষণ বন্দ্যোপাধ্যায়', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 15, 2, 1, 1, 0.00, 250.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-11 13:33:15'),
(2, 'হিমু সমগ্র', NULL, 'হিমুর রহস্যময় জীবনের এক অপূর্ব সংগ্রহ।', 1, NULL, 'Bengali', 'হুমায়ূন আহমেদ', NULL, NULL, NULL, NULL, NULL, 'Hardcover', 0, 'New', NULL, NULL, 12, 2, 1, 1, 0.00, 700.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-10 14:36:09'),
(3, 'স্যাপিয়েন্স', NULL, 'মানবজাতির প্রকৃত ইতিহাস ও বিবর্তনের গল্প।', 2, NULL, 'Bengali', 'ইউভাল নোয়াহ হারারি', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 10, 2, 0, 1, 0.00, 850.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-09 01:51:41'),
(4, 'অ্যাটোমিক হ্যাবিটস', NULL, 'ছোট ছোট ভালো অভ্যাস গড়ার বৈজ্ঞানিক উপায়।', 2, NULL, 'Bengali', 'জেমস ক্লিয়ার', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 25, 2, 0, 1, 0.00, 500.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-09 01:51:41'),
(5, 'দ্য মিডনাইট লাইব্রেরি', NULL, 'জীবন ও রিগ্রেট নিয়ে এক চমৎকার ফ্যান্টাসি উপন্যাস।', 1, NULL, 'Bengali', 'ম্যাট হেইগ', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 12, 2, 1, 1, 0.00, 450.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-09 14:12:56'),
(6, 'প্রদোষে প্রাকৃত', NULL, 'ঐতিহাসিক পটভূমিতে রচিত এক অনন্য উপন্যাস।', 1, NULL, 'Bengali', 'শওকত আলী', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 7, 2, 1, 1, 0.00, 320.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-12 11:50:49'),
(7, 'পদ্মা নদীর মাঝি', NULL, 'জেলে জীবনের জীবনসংগ্রাম ও বাস্তবচিত্র।', 1, NULL, 'Bengali', 'মানিক বন্দ্যোপাধ্যায়', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'Used', NULL, NULL, 29, 2, 1, 1, 0.00, 220.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-12 11:38:34'),
(8, 'ইকিগাই', NULL, 'জাপানি জীবন দর্শনে দীর্ঘ ও সুখী জীবনের রহস্য।', 2, NULL, 'Bengali', 'হেক্টর গার্সিয়া', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 18, 2, 1, 1, 0.00, 400.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-09 01:51:41'),
(9, 'মা', NULL, 'একজন বীর মুক্তিযোদ্ধার মায়ের ত্যাগের গল্প।', 1, NULL, 'Bengali', 'আনিসুল হক', NULL, NULL, NULL, NULL, NULL, 'Hardcover', 0, 'New', NULL, NULL, 13, 2, 1, 1, 0.00, 350.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-09 13:58:23'),
(10, 'জীবন ও দর্শন', NULL, 'চিন্তা ও দর্শনের এক সমৃদ্ধ সংকলন।', 4, NULL, 'Bengali', 'আনিসুজ্জামান', NULL, NULL, NULL, NULL, NULL, 'Paperback', 0, 'New', NULL, NULL, 5, 2, 0, 1, 0.00, 380.00, 0.00, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-09 01:51:41', '2026-03-09 01:51:41'),
(11, 'Deserunt impedit do', 'Corrupti accusamus ', 'Dolores vel quas tem', 5, 'Sed est qui qui sap', 'Possimus necessitat', 'Odit nulla aliquip u', 'Ut ipsum esse duis e', 'Tempore est asperio', '2019', 'Quibusdam alias tene', 'Nemo et deleniti mol', 'Paperback', 31, 'New', 'Ut aliquip voluptate', '375', 0, 2, 1, 0, 54.00, 69.00, 0.00, 'Jarrod Garner', 'Pariatur Quia sapie', '1773066134_rental-car-poster-tripzone.png', '1773066134_upscalemedia-transformed.png', '1773066134_1500x1500 logo.png', 1, '2026-03-09 14:22:14', '2026-03-09 15:03:57');

-- --------------------------------------------------------

--
-- Table structure for table `borrows`
--

CREATE TABLE `borrows` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Processing','Active','Returned','Overdue','Cancelled') DEFAULT NULL,
  `reading_progress` int(11) DEFAULT 0,
  `fine_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'ফিকশন', 'fiction', NULL, '2026-03-09 01:13:48'),
(2, 'নন-ফিকশন', 'non-fiction', NULL, '2026-03-09 01:13:48'),
(3, 'কবিতা', 'poetry', NULL, '2026-03-09 01:13:48'),
(4, 'প্রবন্ধ', 'essay', NULL, '2026-03-09 01:13:48'),
(5, 'নতুন ক্যাট', '', NULL, '2026-03-09 14:22:14');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `membership_id` varchar(50) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `membership_plan` enum('None','General','BookLover','Collector') DEFAULT 'None',
  `acc_balance` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `plan_expire_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `membership_id`, `full_name`, `email`, `phone`, `password`, `address`, `membership_plan`, `acc_balance`, `is_active`, `created_at`, `updated_at`, `plan_expire_date`) VALUES
(3, 'OM-2026-636F', 'Abir', 'info.nowshad@proton.me', '01595378750', '$2y$10$hmIFag5QfHSLNkBThvqCS.zQYKDxd.CMvp2eTi55IcnXS2n/VtLnG', 'jyvuv', 'Collector', 0.00, 1, '2026-03-09 01:37:42', '2026-03-12 11:17:06', NULL),
(4, 'OM-2026-1D37', 'kazi sayed', 'ksayed118@gmail.com', '01721695880', '$2y$10$VrJDQUs/ku./G/JfCCid/etBtaMJfgKuCVXd9lHpuFAfVcHYiujEC', NULL, 'BookLover', 0.00, 1, '2026-03-09 09:20:23', '2026-03-09 13:58:07', '2026-04-08 14:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `member_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `payment_method` enum('Cash','Bkash','Nagad','Card','Wallet') DEFAULT 'Cash',
  `trx_id` varchar(100) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `order_status` enum('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing',
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice_no`, `member_id`, `order_date`, `subtotal`, `discount`, `shipping_cost`, `total_amount`, `payment_status`, `payment_method`, `trx_id`, `payment_id`, `order_status`, `shipping_address`, `notes`) VALUES
(25, 'OM-260312-BCFB5', 3, '2026-03-12 11:50:49', 320.00, 0.00, 50.00, 370.00, 'Pending', 'Cash', NULL, NULL, 'Processing', 'jyvuv, ঢাকা', 'Purchase Order');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `reading_progress` int(11) DEFAULT 0,
  `preorder_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `quantity`, `unit_price`, `total_price`, `reading_progress`, `preorder_id`) VALUES
(32, 25, 6, 1, 320.00, 320.00, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_key` varchar(50) DEFAULT NULL,
  `method_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `config_json` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_key`, `method_name`, `is_active`, `config_json`, `updated_at`) VALUES
(1, 'bkash', 'bKash Payment', 0, NULL, '2026-03-12 09:41:17'),
(2, 'nagad', 'Nagad Payment', 0, NULL, '2026-03-12 09:41:18'),
(3, 'cod', 'Cash on Delivery', 1, NULL, '2026-03-12 11:37:51'),
(4, 'fund', 'Account Fund', 1, NULL, '2026-03-12 09:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `pre_orders`
--

CREATE TABLE `pre_orders` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `sub_title` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('Upcoming','Open','Closed') DEFAULT 'Upcoming',
  `is_hot_deal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('Deposit','Purchase','Refund','Penalty') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_books_status` (`is_active`,`is_suggested`),
  ADD KEY `idx_books_category` (`category_id`),
  ADD KEY `idx_books_created` (`created_at`);

--
-- Indexes for table `borrows`
--
ALTER TABLE `borrows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `membership_id` (`membership_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `preorder_id` (`preorder_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `method_key` (`method_key`);

--
-- Indexes for table `pre_orders`
--
ALTER TABLE `pre_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `borrows`
--
ALTER TABLE `borrows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pre_orders`
--
ALTER TABLE `pre_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `borrows`
--
ALTER TABLE `borrows`
  ADD CONSTRAINT `borrows_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrows_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrows_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`preorder_id`) REFERENCES `pre_orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
