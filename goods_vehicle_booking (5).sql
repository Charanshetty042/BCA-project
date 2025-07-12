-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 14, 2025 at 02:09 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goods_vehicle_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_reports`
--

DROP TABLE IF EXISTS `admin_reports`;
CREATE TABLE IF NOT EXISTS `admin_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `report_type` enum('revenue','vehicle_usage','customer_feedback') NOT NULL,
  `details` text NOT NULL,
  `generated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `vehicle_id` int NOT NULL,
  `pickup_location` text NOT NULL,
  `drop_location` text NOT NULL,
  `date` datetime NOT NULL,
  `status` enum('pending','confirmed','on the way','reached','pickedup','delivered','completed','cancelled') DEFAULT 'pending',
  `cancelled_at` datetime DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `vehicle_type` varchar(50) NOT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `customer_id` (`user_id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `customer_name`, `phone_number`, `vehicle_id`, `pickup_location`, `drop_location`, `date`, `status`, `cancelled_at`, `price`, `created_at`, `vehicle_type`) VALUES
(67, 34, 'charan1', '8073196478', 5, 'Yalakki Shettar Colony, Gandhinagar, Dharwad, Dharawada taluku, Dharwad, Karnataka, 580001, India', 'Adangi Road, Vinayaka colony, Gonuru, Sirsi, Shirasi Taluk, Uttara Kannada, Karnataka, 581401, India', '2025-05-11 23:56:00', 'on the way', NULL, 2601.99, '2025-05-11 16:26:35', 'Mini Truck'),
(73, 34, 'charan1', '8073196478', 5, 'JSS Main Block, KHK College Road, Yalakki Shettar Colony, Gandhinagar, Dharwad, Dharawada taluku, Dharwad, Karnataka, 580001, India', 'Ramanabail, Sirsi, Shirasi Taluk, Uttara Kannada, Karnataka, 581401, India', '2025-05-14 17:04:00', 'pickedup', NULL, 2862.58, '2025-05-14 07:34:28', 'Mini Truck'),
(71, 34, 'charan1', '8073196478', 5, 'Yalakki Shettar Colony, Gandhinagar, Dharwad, Dharawada taluku, Dharwad, Karnataka, 580001, India', 'Kadadi, Gadag taluk, Gadag, Karnataka, India', '2025-05-12 02:11:00', 'confirmed', NULL, 2150.50, '2025-05-12 05:42:01', 'Mini Truck'),
(72, 34, 'charan1', '8073196478', 5, 'Yalakki Shettar Colony, Gandhinagar, Dharwad, Dharawada taluku, Dharwad, Karnataka, 580001, India', 'Konavadripalle, Khajipet, YSR, Andhra Pradesh, 516203, India', '2025-05-12 04:15:00', 'completed', NULL, 10649.23, '2025-05-12 05:45:53', 'Mini Truck');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Charan Shetty', 'charanff07@gmail.com', 'update some feature', 'please update payment option', '2025-05-12 12:02:33');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `license_no` varchar(255) NOT NULL,
  `vehicle_reg_no` varchar(255) NOT NULL,
  `vehicle_name` varchar(255) NOT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `vehicle_photo` varchar(255) NOT NULL,
  `license_verified` tinyint(1) DEFAULT '0',
  `vehicle_verified` tinyint(1) DEFAULT '0',
  `per_km_price` decimal(10,2) DEFAULT NULL,
  `capacity` decimal(5,2) DEFAULT NULL COMMENT 'Capacity in tons',
  `taluk` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `user_id`, `name`, `license_no`, `vehicle_reg_no`, `vehicle_name`, `vehicle_type`, `vehicle_photo`, `license_verified`, `vehicle_verified`, `per_km_price`, `capacity`, `taluk`) VALUES
(38, 35, 'guru3', 'DL0123456745', 'MH01AB1256', 'Tata Ace', 'Mini Truck', 'uploads/6820b98bef470_ace-gold-diesel-1739423140.webp', 0, 0, 23.00, NULL, 'Belgaum');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires`) VALUES
(1, 'bilagiamrut06@gmail.com', 'c2e113fd8bc28da9ae6b387329ba57bbad788bdce7a6ed3ffe0b6173a8d06e9e', 1743931733);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','UPI','wallet') NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `driver_id` int NOT NULL,
  `rating` int DEFAULT NULL,
  `review` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`),
  KEY `driver_id` (`driver_id`)
) ;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `booking_id`, `customer_id`, `driver_id`, `rating`, `review`, `created_at`) VALUES
(1, 72, 34, 38, 4, 'good', '2025-05-12 08:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` enum('customer','driver','admin') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password`, `user_type`, `created_at`, `reset_token`) VALUES
(1, 'Charan Shetty', 'charanff07@gmail.com', '8073196478', '$2y$10$lY7OKiE0MRr/sLgnrilbn.ARMdqiZ5Z5uTtjNWXnJhGPfSdXbOYn2', 'admin', '2025-04-03 13:18:16', NULL),
(34, 'charan1', 'charan1@gmail.com', '8073196478', '$2y$10$rhIwdN8XfDaKcO1ghVJuTu/vR7BpZULV1ULb/XCOtxG84MXlK3jx6', 'customer', '2025-05-11 14:41:18', NULL),
(35, 'guru2', 'guru2@gmail.com', '9481411866', '$2y$10$HtYl/odSke23iCozGm6tDOPxPoFJvmTBOklj2ZNeLgs1bSh495bda', 'driver', '2025-05-11 14:50:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int NOT NULL AUTO_INCREMENT,
  `driver_id` int NOT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `availability` enum('available','booked','maintenance') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(255) DEFAULT NULL,
  `price_per_km` decimal(10,2) DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id` int DEFAULT NULL,
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `registration_no` (`registration_no`),
  KEY `driver_id` (`driver_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `driver_id`, `driver_name`, `vehicle_type`, `registration_no`, `availability`, `created_at`, `name`, `price_per_km`, `capacity`, `photo`, `id`) VALUES
(1, 34, 'niranjan', '3 Wheeler', 'MH01AB1244', 'available', '2025-05-08 14:08:01', 'Tata Ace', 21.00, NULL, 'uploads/681cbac1eefea_ace-gold-diesel-1739423140.webp', NULL),
(2, 32, NULL, '3 Wheeler', 'MH01AB1201', 'available', '2025-05-08 14:09:40', 'Ace Gold', 21.00, NULL, 'uploads/6818da4d5b9bb_ace-gold-diesel-1739423140.webp', NULL),
(3, 35, 'niranjan', 'Mini Truck', 'MH01AB1245', 'available', '2025-05-09 16:31:02', 'Tata Ace', 20.00, NULL, 'uploads/681e2dcd5d65a_ace-gold-diesel-1739423140.webp', NULL),
(4, 37, 'guru', 'Mini Truck', 'MH01AB1255', 'available', '2025-05-11 14:39:04', '0', 23.00, NULL, 'uploads/6820b68868712_ace-gold-diesel-1739423140.webp', NULL),
(5, 38, 'guru3', 'Mini Truck', 'MH01AB1256', 'available', '2025-05-11 14:51:55', 'Tata Ace', 23.00, NULL, 'uploads/6820b98bef470_ace-gold-diesel-1739423140.webp', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
