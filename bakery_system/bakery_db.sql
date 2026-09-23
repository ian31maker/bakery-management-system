-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 09:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Bread', 'Freshly baked bread varieties', '2026-04-05 05:00:00'),
(2, 'Cakes', 'Birthday, wedding, and celebration cakes', '2026-04-05 05:30:00'),
(3, 'Pastries', 'Croissants, meat pies, sausage rolls', '2026-04-05 06:00:00'),
(4, 'Donuts', 'Glazed, chocolate, and filled donuts', '2026-04-05 06:30:00'),
(5, 'Beverages', 'Coffee, tea, and fresh juices', '2026-04-05 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `recorded_by`, `description`, `amount`, `expense_date`, `category`, `created_at`) VALUES
(1, 1, 'Rent - April', 30000.00, '2026-04-01', 'rent', '2026-04-01 07:00:00'),
(2, 1, 'Electricity - April', 12000.00, '2026-04-15', 'utilities', '2026-04-15 09:00:00'),
(3, 1, 'Rent - May', 30000.00, '2026-05-01', 'rent', '2026-05-01 07:00:00'),
(4, 1, 'New oven purchase', 45000.00, '2026-05-20', 'equipment', '2026-05-20 11:00:00'),
(5, 1, 'Rent - June', 30000.00, '2026-06-01', 'rent', '2026-06-01 07:00:00'),
(6, 1, 'Electricity - June', 15000.00, '2026-06-30', 'utilities', '2026-06-30 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `log_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity_added` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `date_added` date NOT NULL,
  `added_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`log_id`, `material_id`, `supplier_id`, `quantity_added`, `unit_cost`, `date_added`, `added_by`, `notes`) VALUES
(1, 1, 1, 100.00, 65.00, '2026-04-10', 3, 'First flour delivery - April'),
(2, 2, 3, 50.00, 120.00, '2026-04-10', 3, 'First sugar delivery - April'),
(3, 4, 2, 200.00, 15.00, '2026-04-12', 3, 'First egg delivery - April'),
(4, 1, 1, 150.00, 65.00, '2026-05-15', 3, 'May flour restock'),
(5, 6, 2, 100.00, 80.00, '2026-05-20', 3, 'May milk delivery'),
(6, 1, 1, 200.00, 68.00, '2026-06-25', 3, 'June flour restock'),
(7, 2, 3, 80.00, 125.00, '2026-06-28', 3, 'June sugar restock'),
(8, 7, 2, 1.00, 100.00, '2026-09-02', 3, 'wdsf'),
(9, 7, 3, 4.00, 1.00, '2026-09-02', 3, '');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notif_id`, `user_id`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(3, 3, 'Low Stock Alert', 'Flour running low in May', 'baker/view_inventory.php', 0, '2026-05-20 06:00:00'),
(5, 4, 'Delivery Assigned', 'Deliver Order #2 to Karen', 'driver/assigned_deliveries.php', 0, '2026-06-15 07:05:00'),
(6, 3, 'Production Needed', 'High demand - bake more bread', 'baker/pending_orders.php', 0, '2026-06-20 05:00:00'),
(8, 5, 'Order Ready', 'Your order #4 is ready for pickup!', 'customer/track_order.php?order_id=4', 0, '2026-08-17 08:19:55'),
(10, 5, 'Order Ready', 'Your order #5 is ready for pickup!', 'customer/track_order.php?order_id=5', 0, '2026-08-17 08:23:59'),
(15, 5, 'Order Ready', 'Your order #7 is ready for pickup!', 'customer/track_order.php?order_id=7', 0, '2026-09-01 07:25:45'),
(16, 5, 'Order Ready', 'Your order #6 is ready for pickup!', 'customer/track_order.php?order_id=6', 0, '2026-09-01 07:25:45'),
(17, 5, 'Order Ready', 'Your order #8 is ready for pickup!', 'customer/track_order.php?order_id=8', 0, '2026-09-01 07:25:46'),
(18, 5, 'Order Ready', 'Your order #9 is ready for pickup!', 'customer/track_order.php?order_id=9', 0, '2026-09-01 07:25:47'),
(19, 5, 'Order Ready', 'Your order #10 is ready for pickup!', 'customer/track_order.php?order_id=10', 0, '2026-09-01 07:25:48'),
(20, 5, 'Order Ready', 'Your order #11 is ready for pickup!', 'customer/track_order.php?order_id=11', 0, '2026-09-01 07:25:49'),
(21, 5, 'Order Ready', 'Your order #12 is ready for pickup!', 'customer/track_order.php?order_id=12', 0, '2026-09-01 07:25:50'),
(23, 5, 'Order Ready', 'Your order #13 is ready for pickup!', 'customer/track_order.php?order_id=13', 0, '2026-09-01 07:31:42'),
(29, 5, 'Order Ready', 'Your order #17 is ready for pickup!', 'customer/track_order.php?order_id=17', 0, '2026-09-01 08:18:18'),
(46, 4, 'New Delivery', 'You have been assigned order #17', 'driver/assigned_deliveries.php', 0, '2026-09-01 09:54:42'),
(47, 5, 'Order Dispatched', 'Your order #17 is on the way!', 'customer/order_history.php', 0, '2026-09-01 09:54:42'),
(49, 5, 'Order Ready', 'Your order #8 is ready for pickup!', 'customer/track_order.php?order_id=8', 0, '2026-09-01 09:56:40'),
(50, 4, 'New Delivery', 'You have been assigned order #8', 'driver/assigned_deliveries.php', 0, '2026-09-01 09:57:15'),
(51, 5, 'Order Dispatched', 'Your order #8 is on the way!', 'customer/order_history.php', 0, '2026-09-01 09:57:15'),
(52, 4, 'New Delivery', 'You have been assigned order #3', 'driver/assigned_deliveries.php', 0, '2026-09-01 09:57:17'),
(53, 6, 'Order Dispatched', 'Your order #3 is on the way!', 'customer/order_history.php', 0, '2026-09-01 09:57:17'),
(56, 6, 'Order Ready', 'Your order #21 is ready for pickup!', 'customer/track_order.php?order_id=21', 0, '2026-09-01 10:29:02'),
(57, 4, 'New Delivery', 'You have been assigned order #21', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:29:18'),
(58, 6, 'Order Dispatched', 'Your order #21 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:29:18'),
(61, 5, 'Order Ready', 'Your order #22 is ready for pickup!', 'customer/track_order.php?order_id=22', 0, '2026-09-01 10:37:16'),
(62, 5, 'Order Ready', 'Your order #20 is ready for pickup!', 'customer/track_order.php?order_id=20', 0, '2026-09-01 10:37:18'),
(63, 4, 'New Delivery', 'You have been assigned order #22', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:37:35'),
(64, 5, 'Order Dispatched', 'Your order #22 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:37:35'),
(67, 5, 'Order Ready', 'Your order #4 is ready for pickup!', 'customer/track_order.php?order_id=4', 0, '2026-09-01 10:57:46'),
(68, 5, 'Order Ready', 'Your order #5 is ready for pickup!', 'customer/track_order.php?order_id=5', 0, '2026-09-01 10:57:47'),
(69, 5, 'Order Ready', 'Your order #6 is ready for pickup!', 'customer/track_order.php?order_id=6', 0, '2026-09-01 10:57:48'),
(70, 5, 'Order Ready', 'Your order #7 is ready for pickup!', 'customer/track_order.php?order_id=7', 0, '2026-09-01 10:57:50'),
(71, 5, 'Order Ready', 'Your order #9 is ready for pickup!', 'customer/track_order.php?order_id=9', 0, '2026-09-01 10:57:51'),
(72, 5, 'Order Ready', 'Your order #10 is ready for pickup!', 'customer/track_order.php?order_id=10', 0, '2026-09-01 10:57:52'),
(73, 5, 'Order Ready', 'Your order #11 is ready for pickup!', 'customer/track_order.php?order_id=11', 0, '2026-09-01 10:57:53'),
(74, 5, 'Order Ready', 'Your order #12 is ready for pickup!', 'customer/track_order.php?order_id=12', 0, '2026-09-01 10:57:54'),
(75, 5, 'Order Ready', 'Your order #12 is ready for pickup!', 'customer/track_order.php?order_id=12', 0, '2026-09-01 10:57:54'),
(76, 5, 'Order Ready', 'Your order #12 is ready for pickup!', 'customer/track_order.php?order_id=12', 0, '2026-09-01 10:57:54'),
(77, 5, 'Order Ready', 'Your order #12 is ready for pickup!', 'customer/track_order.php?order_id=12', 0, '2026-09-01 10:57:54'),
(78, 5, 'Order Ready', 'Your order #13 is ready for pickup!', 'customer/track_order.php?order_id=13', 0, '2026-09-01 10:57:55'),
(79, 5, 'Order Ready', 'Your order #13 is ready for pickup!', 'customer/track_order.php?order_id=13', 0, '2026-09-01 10:57:56'),
(80, 5, 'Order Ready', 'Your order #14 is ready for pickup!', 'customer/track_order.php?order_id=14', 0, '2026-09-01 10:57:57'),
(81, 5, 'Order Ready', 'Your order #14 is ready for pickup!', 'customer/track_order.php?order_id=14', 0, '2026-09-01 10:57:57'),
(82, 5, 'Order Ready', 'Your order #15 is ready for pickup!', 'customer/track_order.php?order_id=15', 0, '2026-09-01 10:57:57'),
(83, 5, 'Order Ready', 'Your order #15 is ready for pickup!', 'customer/track_order.php?order_id=15', 0, '2026-09-01 10:57:58'),
(84, 5, 'Order Ready', 'Your order #16 is ready for pickup!', 'customer/track_order.php?order_id=16', 0, '2026-09-01 10:57:58'),
(85, 6, 'Order Ready', 'Your order #18 is ready for pickup!', 'customer/track_order.php?order_id=18', 0, '2026-09-01 10:58:00'),
(86, 5, 'Order Ready', 'Your order #19 is ready for pickup!', 'customer/track_order.php?order_id=19', 0, '2026-09-01 10:58:01'),
(87, 5, 'Order Ready', 'Your order #23 is ready for pickup!', 'customer/track_order.php?order_id=23', 0, '2026-09-01 10:58:01'),
(88, 4, 'New Delivery', 'You have been assigned order #23', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:58:19'),
(89, 5, 'Order Dispatched', 'Your order #23 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:58:19'),
(90, 4, 'New Delivery', 'You have been assigned order #20', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:59:47'),
(91, 5, 'Order Dispatched', 'Your order #20 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:59:47'),
(92, 4, 'New Delivery', 'You have been assigned order #19', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:59:51'),
(93, 5, 'Order Dispatched', 'Your order #19 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:59:51'),
(94, 4, 'New Delivery', 'You have been assigned order #18', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:59:54'),
(95, 6, 'Order Dispatched', 'Your order #18 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:59:54'),
(96, 4, 'New Delivery', 'You have been assigned order #16', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:59:55'),
(97, 5, 'Order Dispatched', 'Your order #16 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:59:55'),
(98, 4, 'New Delivery', 'You have been assigned order #15', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:59:56'),
(99, 5, 'Order Dispatched', 'Your order #15 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:59:56'),
(100, 4, 'New Delivery', 'You have been assigned order #14', 'driver/assigned_deliveries.php', 0, '2026-09-01 10:59:59'),
(101, 5, 'Order Dispatched', 'Your order #14 is on the way!', 'customer/order_history.php', 0, '2026-09-01 10:59:59'),
(102, 4, 'New Delivery', 'You have been assigned order #13', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:01'),
(103, 5, 'Order Dispatched', 'Your order #13 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:01'),
(104, 4, 'New Delivery', 'You have been assigned order #12', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:04'),
(105, 5, 'Order Dispatched', 'Your order #12 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:04'),
(106, 4, 'New Delivery', 'You have been assigned order #11', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:09'),
(107, 5, 'Order Dispatched', 'Your order #11 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:09'),
(108, 4, 'New Delivery', 'You have been assigned order #10', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:11'),
(109, 5, 'Order Dispatched', 'Your order #10 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:11'),
(110, 4, 'New Delivery', 'You have been assigned order #9', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:16'),
(111, 5, 'Order Dispatched', 'Your order #9 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:16'),
(112, 4, 'New Delivery', 'You have been assigned order #7', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:18'),
(113, 5, 'Order Dispatched', 'Your order #7 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:18'),
(114, 4, 'New Delivery', 'You have been assigned order #6', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:20'),
(115, 5, 'Order Dispatched', 'Your order #6 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:20'),
(116, 4, 'New Delivery', 'You have been assigned order #5', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:26'),
(117, 5, 'Order Dispatched', 'Your order #5 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:26'),
(118, 4, 'New Delivery', 'You have been assigned order #4', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:00:29'),
(119, 5, 'Order Dispatched', 'Your order #4 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:00:29'),
(122, 5, 'Order Ready', 'Your order #24 is ready for pickup!', 'customer/track_order.php?order_id=24', 0, '2026-09-01 11:03:39'),
(123, 4, 'New Delivery', 'You have been assigned order #24', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:04:06'),
(124, 5, 'Order Dispatched', 'Your order #24 is on the way!', 'customer/order_history.php', 0, '2026-09-01 11:04:06'),
(127, 5, 'Order Ready', 'Your order #25 is ready for pickup!', 'customer/track_order.php?order_id=25', 0, '2026-09-01 11:21:04'),
(128, 4, 'New Delivery', 'You have been assigned order #25', 'driver/assigned_deliveries.php', 0, '2026-09-01 11:22:05'),
(129, 5, 'Order Dispatched', 'Your order #25 is on the way!', 'customer/track_order.php?order_id=25', 0, '2026-09-01 11:22:05'),
(130, 5, 'Order On The Way', 'Your order #25 is out for delivery!', 'customer/track_order.php?order_id=25', 0, '2026-09-01 11:23:52'),
(131, 5, 'Order Delivered', 'Your order #25 has been delivered!', 'customer/track_order.php?order_id=25', 0, '2026-09-01 11:24:28'),
(134, 5, 'Order Ready', 'Your order #26 is ready for pickup!', 'customer/track_order.php?order_id=26', 0, '2026-09-02 03:31:58'),
(135, 4, 'New Delivery', 'You have been assigned order #26', 'driver/assigned_deliveries.php', 0, '2026-09-02 03:32:11'),
(136, 5, 'Order Dispatched', 'Your order #26 is on the way!', 'customer/track_order.php?order_id=26', 0, '2026-09-02 03:32:11'),
(139, 5, 'Order Ready', 'Your order #27 is ready for pickup!', 'customer/track_order.php?order_id=27', 0, '2026-09-02 03:42:44'),
(140, 4, 'New Delivery', 'You have been assigned order #27', 'driver/assigned_deliveries.php', 0, '2026-09-02 03:42:54'),
(141, 5, 'Order Dispatched', 'Your order #27 is on the way!', 'customer/track_order.php?order_id=27', 0, '2026-09-02 03:42:54'),
(144, 5, 'Order Ready', 'Your order #28 is ready for pickup!', 'customer/track_order.php?order_id=28', 0, '2026-09-02 05:35:30'),
(145, 4, 'New Delivery', 'You have been assigned order #28', 'driver/assigned_deliveries.php', 0, '2026-09-02 05:35:53'),
(146, 5, 'Order Dispatched', 'Your order #28 is on the way!', 'customer/track_order.php?order_id=28', 0, '2026-09-02 05:35:53'),
(149, 8, 'Order Ready', 'Your order #29 is ready for pickup!', 'customer/track_order.php?order_id=29', 0, '2026-09-02 10:02:12'),
(150, 4, 'New Delivery', 'You have been assigned order #29', 'driver/assigned_deliveries.php', 0, '2026-09-02 10:02:23'),
(151, 8, 'Order Dispatched', 'Your order #29 is on the way!', 'customer/track_order.php?order_id=29', 0, '2026-09-02 10:02:23'),
(152, 8, 'Order On The Way', 'Your order #29 is out for delivery!', 'customer/track_order.php?order_id=29', 0, '2026-09-02 10:02:34'),
(153, 8, 'Order Delivered', 'Your order #29 has been delivered!', 'customer/track_order.php?order_id=29', 0, '2026-09-02 10:02:35'),
(156, 6, 'Order Ready', 'Your order #30 is ready for pickup!', 'customer/track_order.php?order_id=30', 0, '2026-09-11 10:34:40'),
(157, 6, 'Order Ready', 'Your order #31 is ready for pickup!', 'customer/track_order.php?order_id=31', 0, '2026-09-11 10:34:41'),
(158, 6, 'Order Ready', 'Your order #31 is ready for pickup!', 'customer/track_order.php?order_id=31', 0, '2026-09-11 10:34:41'),
(159, 4, 'New Delivery', 'You have been assigned order #31', '../driver/assigned_deliveries.php?order_id=31', 0, '2026-09-11 10:36:13'),
(160, 6, 'Order Dispatched', 'Your order #31 is on the way!', '../customer/track_order.php?order_id=31', 0, '2026-09-11 10:36:13'),
(161, 4, 'New Delivery', 'You have been assigned order #30', '../driver/assigned_deliveries.php?order_id=30', 0, '2026-09-11 10:36:14'),
(162, 6, 'Order Dispatched', 'Your order #30 is on the way!', '../customer/track_order.php?order_id=30', 0, '2026-09-11 10:36:14'),
(163, 4, 'New Delivery', 'You have been assigned order #30', '../driver/assigned_deliveries.php?order_id=30', 0, '2026-09-11 10:48:47'),
(164, 6, 'Order Dispatched', 'Your order #30 is on the way!', '../customer/track_order.php?order_id=30', 0, '2026-09-11 10:48:47');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `order_type` enum('pickup','delivery') NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `pickup_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `payment_method` enum('mpesa','card','cash_on_delivery') DEFAULT 'mpesa',
  `payment_status` varchar(50) DEFAULT 'pending',
  `mpesa_phone` varchar(20) DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `mpesa_checkout_id` varchar(100) DEFAULT NULL,
  `mpesa_result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `driver_id`, `total`, `order_type`, `status`, `pickup_date`, `delivery_date`, `delivery_address`, `payment_method`, `payment_status`, `mpesa_phone`, `cancelled_by`, `cancel_reason`, `cancelled_at`, `created_at`, `updated_at`, `mpesa_checkout_id`, `mpesa_result`) VALUES
(1, 5, NULL, 1270.00, 'pickup', 'delivered', '2026-05-15', NULL, NULL, 'mpesa', 'paid', '0744444444', NULL, NULL, NULL, '2026-05-10 11:00:00', '2026-07-15 06:28:38', NULL, NULL),
(2, 5, 4, 215.00, 'delivery', 'delivered', NULL, '2026-06-20', 'Karen, Nairobi', 'mpesa', 'paid', '0744444444', NULL, NULL, NULL, '2026-06-15 07:00:00', '2026-07-15 06:28:38', NULL, NULL),
(3, 6, 4, 1000.00, 'pickup', '', '2026-07-15', NULL, NULL, 'mpesa', 'paid', '0755555555', NULL, NULL, NULL, '2026-07-10 13:00:00', '2026-09-01 09:57:17', NULL, NULL),
(4, 5, 4, 570.00, 'pickup', '', '2026-08-18', NULL, NULL, 'mpesa', 'paid', '0791753693', NULL, NULL, NULL, '2026-08-17 08:19:22', '2026-09-01 11:00:29', NULL, 'Confirmed by admin/cashier'),
(5, 5, 4, 500.00, 'pickup', '', '2026-08-18', NULL, NULL, 'mpesa', 'paid', '0791753693', NULL, NULL, NULL, '2026-08-17 08:23:42', '2026-09-01 11:00:26', NULL, 'Confirmed by admin/cashier'),
(6, 5, 4, 850.00, 'delivery', '', NULL, '2026-08-18', 'olkeri', 'mpesa', 'paid', '0791753693', NULL, NULL, NULL, '2026-08-17 08:43:58', '2026-09-01 11:00:20', NULL, 'Confirmed by admin/cashier'),
(7, 5, 4, 450.00, 'pickup', '', NULL, NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-08-31 13:58:55', '2026-09-01 11:00:18', NULL, 'Confirmed by admin/cashier'),
(8, 5, 4, 200.00, 'delivery', '', NULL, '2026-09-04', NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 06:16:22', '2026-09-01 09:57:15', NULL, 'Confirmed by admin/cashier'),
(9, 5, 4, 350.00, 'pickup', '', '2026-09-02', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 06:34:01', '2026-09-01 11:00:16', NULL, 'Confirmed by admin/cashier'),
(10, 5, 4, 350.00, 'pickup', '', '2026-09-03', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 06:36:44', '2026-09-01 11:00:11', 'ws_CO_010920260936451791753693', 'Confirmed by admin/cashier'),
(11, 5, 4, 200.00, 'pickup', '', '2026-09-01', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 06:38:27', '2026-09-01 11:00:09', 'ws_CO_010920260938285791753693', 'Confirmed by admin/cashier'),
(12, 5, 4, 150.00, 'pickup', '', '2026-09-01', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 06:38:58', '2026-09-01 11:00:04', 'ws_CO_010920260938592791753693', 'Confirmed by admin/cashier'),
(13, 5, 4, 300.00, 'delivery', '', '2026-09-03', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 07:31:07', '2026-09-01 11:00:01', 'ws_CO_010920261031080791753693', 'Confirmed by admin/cashier'),
(14, 5, 4, 200.00, 'delivery', '', NULL, '2026-09-02', 'RUNDDA', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 07:42:27', '2026-09-01 10:59:59', 'ws_CO_010920261042289791753693', 'Confirmed by admin/cashier'),
(15, 5, 4, 1.00, 'delivery', '', '2026-09-10', NULL, 'WFW', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 07:44:43', '2026-09-01 10:59:56', 'ws_CO_010920261044442791753693', 'Confirmed by admin/cashier'),
(16, 5, 4, 1.00, 'delivery', '', NULL, '2026-09-01', 'EG', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 07:45:16', '2026-09-01 10:59:55', 'ws_CO_010920261045172791753693', 'Confirmed by admin/cashier'),
(17, 5, 4, 2.00, 'delivery', '', NULL, '2026-09-10', 'V BV', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 07:45:59', '2026-09-01 09:54:42', 'ws_CO_010920261046003791753693', 'Payment confirmed - callback simulated for localhost demo'),
(18, 6, 4, 2.00, 'pickup', '', '2026-09-02', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 08:17:11', '2026-09-01 10:59:54', 'ws_CO_010920261117124791753693', 'Confirmed by admin/cashier'),
(19, 5, 4, 1.00, 'delivery', '', '2026-09-09', NULL, NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 08:20:58', '2026-09-01 10:59:51', 'ws_CO_010920261120596791753693', 'Confirmed by admin/cashier'),
(20, 5, 4, 2.00, 'delivery', '', NULL, '2026-09-03', 'yr', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 09:55:29', '2026-09-01 10:59:47', 'ws_CO_010920261255309791753693', 'Auto-confirmed via API check'),
(21, 6, 4, 1.00, 'delivery', '', NULL, '2026-09-04', NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 10:14:09', '2026-09-01 10:29:18', 'ws_CO_010920261314112791753693', 'Confirmed manually by admin'),
(22, 5, 4, 1.00, 'delivery', '', '2026-09-11', NULL, 'vfd', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 10:36:24', '2026-09-01 10:37:35', 'ws_CO_010920261336261791753693', 'Confirmed manually by admin'),
(23, 5, 4, 2.00, 'delivery', '', NULL, '2026-09-03', 'cfghjk', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 10:56:53', '2026-09-01 10:58:19', 'ws_CO_010920261356553791753693', 'Confirmed manually by admin'),
(24, 5, 4, 1.00, 'delivery', '', '2026-09-08', '2026-09-03', 'gbhd', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 11:02:01', '2026-09-01 11:04:06', 'ws_CO_010920261402026791753693', 'Confirmed manually by admin'),
(25, 5, 4, 1.00, 'delivery', 'delivered', NULL, '2026-09-10', NULL, 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-01 11:19:01', '2026-09-01 11:24:28', 'ws_CO_010920261419027791753693', 'Confirmed manually by admin'),
(26, 5, 4, 1.00, 'delivery', 'dispatched', NULL, '2026-09-03', 'wrffr', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-02 03:31:11', '2026-09-02 03:32:11', 'ws_CO_020920260631128791753693', 'Confirmed manually by admin'),
(27, 5, 4, 1.00, 'delivery', 'dispatched', NULL, '2026-09-04', 'wtrehrey', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-02 03:42:12', '2026-09-02 03:42:54', 'ws_CO_020920260642143791753693', 'Confirmed manually by admin'),
(28, 5, 4, 2.00, 'delivery', 'dispatched', '2026-09-03', '2026-09-03', 'dxdcfvgbnjmkhgfd', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-02 05:34:45', '2026-09-02 05:35:53', 'ws_CO_020920260834467791753693', 'Confirmed manually by admin'),
(29, 8, 4, 3.00, 'delivery', 'delivered', NULL, '2026-09-03', 'ddfgh', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-02 10:01:18', '2026-09-02 10:02:35', 'ws_CO_020920261301189791753693', 'Confirmed manually by admin'),
(30, 6, 4, 3.00, 'delivery', 'dispatched', NULL, '2026-09-08', 'kg', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-07 14:02:07', '2026-09-11 10:36:14', NULL, 'Confirmed manually by admin'),
(31, 6, 4, 3.00, 'delivery', 'dispatched', NULL, '2026-09-08', 'jbh', 'mpesa', 'paid', '254791753693', NULL, NULL, NULL, '2026-09-07 14:02:24', '2026-09-11 10:36:13', NULL, 'Confirmed manually by admin'),
(32, 5, NULL, 2.00, 'delivery', 'pending', NULL, '2026-09-12', 'GHJ', 'mpesa', 'pending', '254791753693', NULL, NULL, NULL, '2026-09-11 11:01:00', '2026-09-11 11:01:02', 'ws_CO_110920261401023791753693', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 1, 1200.00, 1200.00),
(2, 1, 7, 1, 70.00, 70.00),
(3, 2, 1, 2, 55.00, 110.00),
(4, 2, 9, 1, 100.00, 100.00),
(5, 2, 6, 1, 70.00, 70.00),
(6, 3, 4, 1, 1000.00, 1000.00),
(8, 4, 1, 1, 150.00, 150.00),
(9, 4, 2, 2, 60.00, 120.00),
(11, 5, 1, 2, 100.00, 200.00),
(13, 6, 1, 1, 100.00, 100.00),
(17, 9, 1, 1, 150.00, 150.00),
(19, 10, 1, 1, 150.00, 150.00),
(35, 28, 9, 2, 1.00, 2.00),
(36, 29, 10, 2, 1.00, 2.00),
(37, 29, 9, 1, 1.00, 1.00),
(38, 30, 9, 3, 1.00, 3.00),
(39, 31, 9, 3, 1.00, 3.00),
(40, 32, 9, 2, 1.00, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `production_log`
--

CREATE TABLE `production_log` (
  `production_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_produced` int(11) NOT NULL,
  `produced_by` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_log`
--

INSERT INTO `production_log` (`production_id`, `product_id`, `quantity_produced`, `produced_by`, `production_date`, `notes`, `created_at`) VALUES
(1, 1, 50, 3, '2026-04-15', 'First batch of white bread', '2026-04-15 03:00:00'),
(2, 3, 10, 3, '2026-04-20', 'Chocolate cakes for weekend', '2026-04-20 02:00:00'),
(3, 1, 80, 3, '2026-05-10', 'Regular white bread batch', '2026-05-10 03:00:00'),
(4, 5, 30, 3, '2026-05-15', 'Meat pies for lunch rush', '2026-05-15 01:00:00'),
(5, 1, 100, 3, '2026-06-20', 'High demand bread batch', '2026-06-20 02:30:00'),
(6, 7, 40, 3, '2026-06-25', 'Weekend donut batch', '2026-06-25 01:00:00'),
(7, 2, 1, 3, '2026-09-02', '', '2026-09-02 10:34:30'),
(8, 1, 250, 3, '2026-09-02', '', '2026-09-02 20:27:24');

-- --------------------------------------------------------

--
-- Table structure for table `production_materials`
--

CREATE TABLE `production_materials` (
  `pm_id` int(11) NOT NULL,
  `production_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_materials`
--

INSERT INTO `production_materials` (`pm_id`, `production_id`, `material_id`, `quantity_used`) VALUES
(1, 1, 1, 15.00),
(2, 1, 4, 100.00),
(3, 1, 5, 0.50),
(4, 2, 1, 5.00),
(5, 2, 2, 3.00),
(6, 2, 4, 40.00),
(7, 2, 7, 1.00),
(8, 3, 1, 24.00),
(9, 3, 4, 160.00),
(10, 3, 5, 0.80),
(11, 8, 1, 75.00),
(12, 8, 4, 500.00),
(13, 8, 5, 2.50);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT 'images/products/default.jpg',
  `is_available` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `image`, `price`, `category_id`, `image_path`, `is_available`, `created_at`) VALUES
(1, 'White Bread', 'Soft white loaf, 400g', 'white_bread.jpg', 1.00, 1, 'white_bread.jpg', 1, '2026-04-05 07:30:00'),
(2, 'Brown Bread', 'Whole wheat loaf, 400g', 'brown_bread.jpg', 1.00, 1, 'brown_bread.jpg', 1, '2026-04-05 08:00:00'),
(3, 'Chocolate Cake', 'Rich chocolate cake, 1kg', 'chocolate_cake.jpg', 1.00, 2, 'chocolate_cake.jpg', 1, '2026-04-10 06:00:00'),
(4, 'Vanilla Cake', 'Classic vanilla sponge, 1kg', 'vanilla_cake.jpg', 1.00, 2, 'vanilla_cake.jpg', 1, '2026-04-10 07:00:00'),
(5, 'Meat Pie', 'Savory beef pie', 'meat_pie.jpg', 1.00, 3, 'meat_pie.jpg', 1, '2026-04-15 05:00:00'),
(6, 'Sausage Roll', 'Flaky pastry with sausage', 'sausage_roll.jpg', 1.00, 3, 'sausage_roll.jpg', 1, '2026-04-15 06:00:00'),
(7, 'Glazed Donut', 'Classic sugar glazed', 'glazed_donut.jpg', 1.00, 4, 'glazed_donut.jpg', 1, '2026-05-01 07:00:00'),
(8, 'Chocolate Donut', 'Chocolate frosted donut', 'chocolate_donut.jpg', 1.00, 4, 'chocolate_donut.jpg', 1, '2026-05-01 08:00:00'),
(9, 'Black Coffee', 'Fresh brewed Kenyan coffee', 'black_coffee.jpg', 1.00, 5, 'black_coffee.jpg', 1, '2026-05-10 05:00:00'),
(10, 'Fresh Juice', 'Natural fruit juice, 300ml', 'fresh_juice.jpg', 1.00, 5, 'fresh_juice.jpg', 1, '2026-05-10 06:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `raw_materials`
--

CREATE TABLE `raw_materials` (
  `material_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) NOT NULL,
  `min_stock` decimal(10,2) NOT NULL DEFAULT 10.00,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_materials`
--

INSERT INTO `raw_materials` (`material_id`, `name`, `quantity`, `unit`, `min_stock`, `supplier_id`, `created_at`, `updated_at`) VALUES
(1, 'Wheat Flour', 425.00, 'kg', 50.00, 1, '2026-04-01 08:00:00', '2026-09-02 20:27:24'),
(2, 'Sugar', 200.00, 'kg', 20.00, 3, '2026-04-01 09:00:00', '2026-07-15 06:28:38'),
(3, 'Butter', 100.00, 'kg', 10.00, 2, '2026-04-01 10:00:00', '2026-07-15 06:28:38'),
(4, 'Eggs', 0.00, 'pieces', 50.00, 2, '2026-04-01 11:00:00', '2026-09-02 20:27:24'),
(5, 'Yeast', 17.50, 'kg', 2.00, 1, '2026-04-02 05:00:00', '2026-09-02 20:27:24'),
(6, 'Milk', 200.00, 'liters', 20.00, 2, '2026-04-02 06:00:00', '2026-07-15 06:28:38'),
(7, 'Cocoa Powder', 35.00, 'kg', 5.00, 1, '2026-04-02 07:00:00', '2026-09-02 20:26:26'),
(8, 'Vanilla Extract', 10.00, 'liters', 1.00, 1, '2026-04-02 08:00:00', '2026-07-15 06:28:38');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity_needed` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `product_id`, `material_id`, `quantity_needed`) VALUES
(1, 1, 1, 0.30),
(2, 1, 4, 2.00),
(3, 1, 5, 0.01),
(4, 3, 1, 0.50),
(5, 3, 2, 0.30),
(6, 3, 4, 4.00),
(7, 3, 7, 0.10);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `order_id`, `customer_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 5, 5, 'Amazing chocolate cake! Will order again.', '2026-05-20 07:00:00'),
(2, 2, 5, 4, 'Delivery was fast, bread was fresh.', '2026-06-25 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `cashier_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','mpesa','card') DEFAULT 'cash',
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `ticket_id`, `cashier_id`, `total`, `payment_method`, `amount_paid`, `change_amount`, `sale_date`) VALUES
(1, 1, 2, 215.00, 'cash', 250.00, 35.00, '2026-04-20 05:05:00'),
(2, 2, 2, 130.00, 'mpesa', 130.00, 0.00, '2026-05-10 06:08:00'),
(3, 3, 2, 340.00, 'cash', 400.00, 60.00, '2026-06-15 07:10:00'),
(4, NULL, 2, 150.00, 'mpesa', 150.00, 0.00, '2026-09-01 07:06:00'),
(5, 1, 2, 2.00, 'cash', 2.00, 0.00, '2026-09-02 10:03:27'),
(6, 4, 2, 2.00, 'cash', 1.00, -1.00, '2026-09-02 20:25:12'),
(7, NULL, 2, 2.00, 'card', 10.00, 8.00, '2026-09-11 19:28:09'),
(8, NULL, 2, 2.00, 'mpesa', 7.00, 5.00, '2026-09-11 19:28:24');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`sale_item_id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 2, 55.00, 110.00),
(2, 1, 6, 1, 70.00, 70.00),
(3, 1, 9, 1, 100.00, 100.00),
(4, 2, 2, 1, 60.00, 60.00),
(5, 2, 5, 1, 80.00, 80.00),
(6, 3, 3, 1, 1200.00, 1200.00),
(7, 3, 8, 2, 70.00, 140.00),
(8, 4, 10, 1, 150.00, 150.00),
(9, 5, 2, 2, 1.00, 2.00),
(10, 6, 2, 2, 1.00, 2.00),
(11, 7, 3, 2, 1.00, 2.00),
(12, 8, 8, 2, 1.00, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'Unga Limited', '0755555555', 'sales@unga.co.ke', 'Industrial Area, Nairobi', '2026-04-01 05:00:00'),
(2, 'Kenyatta Dairy', '0766666666', 'info@kenyattadairy.co.ke', 'Ruiru, Kenya', '2026-04-01 06:00:00'),
(3, 'Sugar Board of Kenya', '0777777777', 'procurement@sugarboard.go.ke', 'Mumias, Kenya', '2026-04-01 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `ticket_number` int(11) NOT NULL,
  `status` enum('waiting','serving','done') DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `served_at` timestamp NULL DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `ticket_number`, `status`, `created_at`, `served_at`, `sale_id`) VALUES
(1, 1, 'done', '2026-04-20 05:00:00', '2026-09-02 10:03:28', 5),
(2, 2, 'done', '2026-05-10 06:00:00', '2026-05-10 06:08:00', 2),
(3, 3, 'done', '2026-06-15 07:00:00', '2026-06-15 07:10:00', 3),
(4, 4, 'done', '2026-07-14 05:00:00', '2026-09-02 20:25:12', 6),
(5, 5, 'serving', '2026-09-02 20:24:34', '2026-09-02 20:24:37', NULL),
(6, 6, 'serving', '2026-09-11 19:27:35', '2026-09-11 19:27:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','cashier','baker','driver','customer') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `password_hash`, `role`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0700000000', 'admin@bakery.com', NULL, 1, '2026-04-01 05:00:00', '2026-07-15 06:28:37'),
(2, 'John Cashier', 'cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', '0711111111', 'cashier@bakery.com', NULL, 1, '2026-04-03 06:00:00', '2026-07-15 06:28:37'),
(3, 'Mary Baker', 'baker', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'baker', '0722222222', 'baker@bakery.com', NULL, 1, '2026-04-03 06:30:00', '2026-07-15 06:28:37'),
(4, 'Peter Driver', 'driver', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', '0733333333', 'driver@bakery.com', 'Nairobi, Kenya', 1, '2026-05-15 07:00:00', '2026-07-15 06:28:37'),
(5, 'Alice Customer', 'alice', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '0744444444', 'alice@email.com', 'Karen, Nairobi', 1, '2026-04-10 11:00:00', '2026-07-15 06:28:37'),
(6, 'Bob Customer', 'bob', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '0755555555', 'bob@email.com', 'Westlands, Nairobi', 1, '2026-05-20 08:00:00', '2026-07-15 06:28:37'),
(8, 'IAN KIRUI', 'ian', '$2y$10$.Ci42iH6P4uc/TSI3p..s.fLOwlIxVAHSRgV07PXPKNXqfK3SEGhO', 'customer', '0791753693', NULL, 'nkoroi north', 1, '2026-09-02 06:07:50', '2026-09-02 06:07:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `cancelled_by` (`cancelled_by`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `production_log`
--
ALTER TABLE `production_log`
  ADD PRIMARY KEY (`production_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `produced_by` (`produced_by`);

--
-- Indexes for table `production_materials`
--
ALTER TABLE `production_materials`
  ADD PRIMARY KEY (`pm_id`),
  ADD KEY `production_id` (`production_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `cashier_id` (`cashier_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `production_log`
--
ALTER TABLE `production_log`
  MODIFY `production_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `production_materials`
--
ALTER TABLE `production_materials`
  MODIFY `pm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `raw_materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_log_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_log_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `production_log`
--
ALTER TABLE `production_log`
  ADD CONSTRAINT `production_log_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_log_ibfk_2` FOREIGN KEY (`produced_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `production_materials`
--
ALTER TABLE `production_materials`
  ADD CONSTRAINT `production_materials_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `production_log` (`production_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `raw_materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD CONSTRAINT `raw_materials_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `raw_materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
