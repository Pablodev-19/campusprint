-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 04:48 PM
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
-- Database: `campusprint_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `print_rates`
--

CREATE TABLE `print_rates` (
  `id` int(10) UNSIGNED NOT NULL,
  `colour_mode` enum('black_white','colour') NOT NULL,
  `price_per_page` decimal(10,2) NOT NULL
) ;

--
-- Dumping data for table `print_rates`
--

INSERT INTO `print_rates` (`id`, `colour_mode`, `price_per_page`) VALUES
(1, 'black_white', 2.00),
(2, 'colour', 3.00);

-- --------------------------------------------------------

--
-- Table structure for table `print_requests`
--

CREATE TABLE `print_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `rate_id` int(10) UNSIGNED NOT NULL,
  `print_type` varchar(30) NOT NULL DEFAULT 'document',
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `page_count` int(10) UNSIGNED NOT NULL,
  `copies` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `color_mode` enum('black_white','colour') NOT NULL DEFAULT 'black_white',
  `sides` enum('single','double') NOT NULL,
  `instructions` text DEFAULT NULL,
  `estimated_cost` decimal(10,2) NOT NULL,
  `final_cost` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','printing','ready','collected','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `print_requests`
--

INSERT INTO `print_requests` (`id`, `student_id`, `rate_id`, `print_type`, `original_filename`, `stored_filename`, `page_count`, `copies`, `color_mode`, `sides`, `instructions`, `estimated_cost`, `final_cost`, `status`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'document', 'Van.pdf', '786a1e0834fdedb490cbb5889c4e4b20.pdf', 8, 1, 'black_white', 'single', 'binding', 4.00, NULL, 'accepted', NULL, '2026-11-07 11:27:01', '2026-11-08 03:00:09'),
(2, 2, 1, 'document', 'Van.pdf', '131e2b4efa6890b0b8ac2722d500f2b8.pdf', 2, 1, 'black_white', 'single', '', 1.00, NULL, 'cancelled', NULL, '2026-11-07 13:57:23', '2026-11-08 03:24:27'),
(3, 2, 1, 'document', 'Van.pdf', 'bc1df9244a2c8450289490e24eea35f7.pdf', 2, 1, 'black_white', 'single', '', 1.00, 2.00, 'ready', NULL, '2026-11-07 18:09:51', '2026-11-08 02:56:42'),
(4, 2, 1, 'document', '10316494.pdf', 'd0a5ecf06c7cef73e0e5df90be63b5e2.pdf', 2, 1, 'black_white', 'single', '', 1.00, NULL, 'cancelled', NULL, '2026-11-08 07:34:45', '2026-11-08 16:06:08'),
(5, 2, 2, 'presentation', 'WEEK 3 - Environmental Management and its Evolution.pdf', '1e795db05e0de8595a83127ac5a12be2.pdf', 26, 1, 'colour', 'single', '', 52.00, NULL, 'cancelled', NULL, '2026-11-08 10:09:34', '2026-11-09 23:50:18'),
(6, 2, 2, 'presentation', 'WEEK 3 - Environmental Management and its Evolution.pdf', '09a6ca6de56564da212f1eb963d8a3a5.pdf', 26, 1, 'colour', 'single', '', 52.00, NULL, 'pending', NULL, '2026-11-08 10:33:48', '2026-11-08 10:33:48'),
(7, 2, 2, 'presentation', 'Presentation 11 Digital Notes.pdf', '8fb53c8dcef76989d42238ade4bcf7a3.pdf', 46, 1, 'colour', 'double', 'Binding\r\n', 46.00, 46.00, 'ready', NULL, '2026-11-10 00:26:27', '2026-11-10 00:43:27'),
(8, 2, 1, 'document', 'Assignment_4 (10316494).pdf', '5c2842485184fb44cb8f400d08f625a4.pdf', 3, 1, 'black_white', 'double', '', 2.00, 3.00, 'ready', NULL, '2026-11-10 01:05:03', '2026-11-10 01:08:32'),
(9, 6, 1, 'document', 'Assignment_4 (10316494).pdf', '3956c61f6bd4d6fd3bb831a843bc81de.pdf', 3, 2, 'black_white', 'double', '', 4.00, 6.00, 'ready', NULL, '2026-11-10 15:12:23', '2026-11-10 15:29:56'),
(10, 5, 1, 'document', 'Assignment_4 (10316494).pdf', '49bb599c9ea3352aa04c00344740386f.pdf', 3, 1, 'black_white', 'double', '', 4.00, NULL, 'pending', NULL, '2026-11-10 15:26:11', '2026-11-10 15:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `request_status_history`
--

CREATE TABLE `request_status_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_id` int(10) UNSIGNED NOT NULL,
  `changed_by` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','printing','ready','collected','cancelled','rejected') NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_status_history`
--

INSERT INTO `request_status_history` (`id`, `request_id`, `changed_by`, `status`, `note`, `created_at`) VALUES
(1, 3, 3, 'accepted', NULL, '2026-11-08 02:54:41'),
(2, 3, 3, 'ready', NULL, '2026-11-08 02:56:42'),
(3, 3, 3, 'ready', NULL, '2026-11-08 02:59:52'),
(4, 1, 3, 'accepted', NULL, '2026-11-08 03:00:09'),
(5, 7, 3, 'accepted', NULL, '2026-11-10 00:42:21'),
(6, 1, 3, 'accepted', NULL, '2026-11-10 00:42:44'),
(7, 7, 3, 'ready', NULL, '2026-11-10 00:43:27'),
(8, 8, 3, 'ready', NULL, '2026-11-10 01:08:32'),
(9, 9, 3, 'ready', NULL, '2026-11-10 15:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','staff') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(2, 'Pachomius Adjei', 'adjeipachomius@gmail.com', '$2y$10$.f3BXYP68gCUG/q./wnpjeGw6.ed.P.VFD/cbSXbvJVBK1/ioj5ly', 'student', '2026-11-06 21:57:35'),
(3, 'Printing Staff', 'staff@campusprint.com', '$2y$10$ZlpvjFn6jY7jBlM/LRZYzuBB4TR.Im6Qk42RmK2ikM1KhiWC1qMvG', 'staff', '2026-11-08 01:24:35'),
(5, 'User 1', 'user1@campus.com', '$2y$10$aNV83hRMhYgoulkQwRvvB./FnE6RGMwujAbh3T6IaVQlLGuVVM0RW', 'student', '2026-11-10 01:40:53'),
(6, 'Romeo Agyemang', 'agyemangromeo74@gmail.com', '$2y$10$iUa3/c/YrLMP2VzyvR1XlOkh8bivCDu0ZlhmXsfV3rs3fS1mXXLVS', 'student', '2026-11-10 14:55:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `print_rates`
--
ALTER TABLE `print_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colour_mode` (`colour_mode`);

--
-- Indexes for table `print_requests`
--
ALTER TABLE `print_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stored_filename` (`stored_filename`),
  ADD KEY `fk_requests_student` (`student_id`),
  ADD KEY `fk_requests_rate` (`rate_id`);

--
-- Indexes for table `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_request` (`request_id`),
  ADD KEY `fk_history_user` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `print_rates`
--
ALTER TABLE `print_rates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_requests`
--
ALTER TABLE `print_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_status_history`
--
ALTER TABLE `request_status_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `print_requests`
--
ALTER TABLE `print_requests`
  ADD CONSTRAINT `fk_requests_rate` FOREIGN KEY (`rate_id`) REFERENCES `print_rates` (`id`),
  ADD CONSTRAINT `fk_requests_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD CONSTRAINT `fk_history_request` FOREIGN KEY (`request_id`) REFERENCES `print_requests` (`id`),
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
