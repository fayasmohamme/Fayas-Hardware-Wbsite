-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 10:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fayas_hardware`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddToCart` (IN `user_id_param` INT, IN `product_id_param` INT, IN `quantity_param` INT)   BEGIN
    DECLARE product_price DECIMAL(10,2);
    DECLARE product_name VARCHAR(255);
    DECLARE product_image VARCHAR(500);
    
    -- Get product details
    SELECT price, name, image INTO product_price, product_name, product_image
    FROM products WHERE id = product_id_param AND is_active = TRUE;
    
    -- Insert or update cart item
    INSERT INTO cart (user_id, product_id, product_name, price, quantity, image)
    VALUES (user_id_param, product_id_param, product_name, product_price, quantity_param, product_image)
    ON DUPLICATE KEY UPDATE quantity = quantity + quantity_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetCartTotal` (IN `user_id_param` INT)   BEGIN
    SELECT 
        user_id,
        COUNT(*) as item_count,
        SUM(price * quantity) as total_amount
    FROM cart 
    WHERE user_id = user_id_param 
    GROUP BY user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetProductsByCategory` (IN `category_name` VARCHAR(100))   BEGIN
    SELECT * FROM active_products 
    WHERE category = category_name 
    ORDER BY name;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_products`
-- (See below for the actual view)
--
CREATE TABLE `active_products` (
`id` int(11)
,`name` varchar(255)
,`description` text
,`price` decimal(10,2)
,`image` varchar(500)
,`category` varchar(100)
,`tags` varchar(500)
,`stock_quantity` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `best_sellers`
-- (See below for the actual view)
--
CREATE TABLE `best_sellers` (
`id` int(11)
,`name` varchar(255)
,`description` text
,`price` decimal(10,2)
,`image` varchar(500)
,`category` varchar(100)
,`tags` varchar(500)
,`stock_quantity` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `product_name`, `price`, `quantity`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'Safety Helmet', 1200.00, 1, '1-1-scaled.jpg', '2025-10-12 15:21:05', '2025-10-12 15:21:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Power Tools', 'Electric and battery-powered tools', 1, '2025-10-12 14:02:22'),
(2, 'Hand Tools', 'Manual tools for various applications', 1, '2025-10-12 14:02:22'),
(3, 'Hardware', 'Nuts, bolts, screws, and fasteners', 1, '2025-10-12 14:02:22'),
(4, 'Safety Equipment', 'Protective gear and safety devices', 1, '2025-10-12 14:02:22'),
(5, 'Plumbing', 'Pipes, fittings, and plumbing supplies', 1, '2025-10-12 14:02:22'),
(6, 'Electrical', 'Wires, outlets, switches, and electrical components', 1, '2025-10-12 14:02:22');

-- --------------------------------------------------------

--
-- Stand-in structure for view `new_arrivals`
-- (See below for the actual view)
--
CREATE TABLE `new_arrivals` (
`id` int(11)
,`name` varchar(255)
,`description` text
,`price` decimal(10,2)
,`image` varchar(500)
,`category` varchar(100)
,`tags` varchar(500)
,`stock_quantity` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `on_sale_products`
-- (See below for the actual view)
--
CREATE TABLE `on_sale_products` (
`id` int(11)
,`name` varchar(255)
,`description` text
,`price` decimal(10,2)
,`image` varchar(500)
,`category` varchar(100)
,`tags` varchar(500)
,`stock_quantity` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category`, `tags`, `stock_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(9, 'Power Tool', NULL, 4500.00, 'download - 2025-05-08T160049.985.png', 'Hardware', NULL, 0, 1, '2025-10-12 14:39:31', '2025-10-12 14:39:31'),
(10, 'Safety Helmet', NULL, 1200.00, '1-1-scaled.jpg', 'Safety Equipment', NULL, 0, 1, '2025-10-12 14:58:45', '2025-10-12 14:58:45'),
(11, 'Wire Strippers', NULL, 950.00, '71vD2csBGhL._AC_SL1500_.jpg', 'Electrical', NULL, 0, 1, '2025-10-12 15:02:07', '2025-10-12 15:02:07'),
(12, 'Nails Assorted Pack', NULL, 450.00, 'images.jpeg', 'Hardware', NULL, 0, 1, '2025-10-12 15:06:54', '2025-10-12 15:06:54'),
(13, 'Circuit Breaker 20A', NULL, 850.00, 'dz47-63-2p20c.jpg', 'Electrical', NULL, 0, 1, '2025-10-12 15:08:05', '2025-10-12 15:08:05'),
(14, 'Pipe Wrench 12\"', NULL, 3200.00, 'Pipe Wrench.png', 'Plumbing', NULL, 0, 1, '2025-10-12 15:09:02', '2025-10-12 15:09:02'),
(15, 'Screwdriver Set 12pc', NULL, 1800.00, '71sJ7IbukJL._UF1000,1000_QL80_.jpg', 'Hand Tools', NULL, 0, 1, '2025-10-12 15:10:34', '2025-10-12 15:10:34'),
(16, 'Hammer Set 5pc', NULL, 2500.00, '710uGcU62fL.jpg', 'Hand Tools', NULL, 0, 1, '2025-10-12 15:11:37', '2025-10-12 15:11:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin123', 'admin@fayashardware.com', 'Admin', 'User', NULL, NULL, '2025-10-12 14:02:22', '2025-10-12 14:16:02'),
(2, 'customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer1@example.com', 'John', 'Doe', NULL, NULL, '2025-10-12 14:02:22', '2025-10-12 14:02:22');

-- --------------------------------------------------------

--
-- Structure for view `active_products`
--
DROP TABLE IF EXISTS `active_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_products`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`description` AS `description`, `products`.`price` AS `price`, `products`.`image` AS `image`, `products`.`category` AS `category`, `products`.`tags` AS `tags`, `products`.`stock_quantity` AS `stock_quantity` FROM `products` WHERE `products`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `best_sellers`
--
DROP TABLE IF EXISTS `best_sellers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `best_sellers`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`description` AS `description`, `products`.`price` AS `price`, `products`.`image` AS `image`, `products`.`category` AS `category`, `products`.`tags` AS `tags`, `products`.`stock_quantity` AS `stock_quantity` FROM `products` WHERE `products`.`tags` like '%popular%' AND `products`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `new_arrivals`
--
DROP TABLE IF EXISTS `new_arrivals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `new_arrivals`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`description` AS `description`, `products`.`price` AS `price`, `products`.`image` AS `image`, `products`.`category` AS `category`, `products`.`tags` AS `tags`, `products`.`stock_quantity` AS `stock_quantity` FROM `products` WHERE `products`.`tags` like '%new%' AND `products`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `on_sale_products`
--
DROP TABLE IF EXISTS `on_sale_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `on_sale_products`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`description` AS `description`, `products`.`price` AS `price`, `products`.`image` AS `image`, `products`.`category` AS `category`, `products`.`tags` AS `tags`, `products`.`stock_quantity` AS `stock_quantity` FROM `products` WHERE `products`.`tags` like '%sale%' AND `products`.`is_active` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_cart_user_product` (`user_id`,`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_orders_date` (`order_date`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_tags` (`tags`),
  ADD KEY `idx_products_price` (`price`),
  ADD KEY `idx_products_active` (`is_active`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
