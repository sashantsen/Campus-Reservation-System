-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2025 at 04:52 AM
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
-- Database: `campus_reservation`
--

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('pending','approved','cancelled') DEFAULT 'pending',
  `checked_in` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_id`, `start_time`, `end_time`, `status`, `checked_in`, `created_at`) VALUES
(1, 2, 1, '2025-10-05 14:00:00', '2025-10-05 15:00:00', 'pending', 0, '2025-10-05 14:19:08'),
(2, 3, 1, '2025-10-23 10:30:00', '2025-10-23 17:30:00', 'cancelled', 1, '2025-10-06 09:32:06'),
(4, 3, 1, '2025-10-08 10:10:00', '2025-10-08 23:11:00', 'cancelled', 0, '2025-10-06 11:34:10'),
(5, 3, 3, '2025-11-05 10:20:00', '2025-11-05 23:12:00', 'cancelled', 0, '2025-10-06 11:58:06'),
(6, 3, 1, '2025-10-06 10:11:00', '2025-10-06 17:10:00', 'approved', 1, '2025-10-06 11:59:10'),
(7, 3, 3, '2025-10-07 10:10:00', '2025-10-07 23:11:00', 'cancelled', 1, '2025-10-06 11:59:53'),
(8, 3, 1, '2025-10-07 03:00:00', '2025-10-07 17:50:00', 'approved', 1, '2025-10-06 12:31:43'),
(9, 4, 1, '2025-10-31 10:20:00', '2025-10-31 23:30:00', 'approved', 1, '2025-10-06 12:33:52'),
(10, 4, 1, '2025-10-09 10:10:00', '2025-10-09 23:11:00', 'cancelled', 1, '2025-10-06 12:34:38'),
(11, 4, 1, '2025-10-24 11:11:00', '2025-10-24 17:30:00', 'approved', 1, '2025-10-06 12:37:07'),
(12, 4, 1, '2025-10-06 23:11:00', '2025-10-06 23:30:00', 'cancelled', 0, '2025-10-06 12:40:51'),
(13, 4, 1, '2025-10-08 10:30:00', '2025-10-08 23:30:00', 'approved', 1, '2025-10-06 12:55:50'),
(14, 3, 1, '2025-10-16 10:30:00', '2025-10-16 23:30:00', 'cancelled', 1, '2025-10-06 23:06:05'),
(15, 3, 1, '2025-10-30 08:00:00', '2025-10-30 21:00:00', 'approved', 1, '2025-10-07 07:22:48'),
(16, 5, 1, '2025-10-16 10:10:00', '2025-10-16 16:40:00', 'approved', 1, '2025-10-07 10:29:38'),
(17, 5, 1, '2025-10-26 10:10:00', '2025-10-26 23:30:00', 'approved', 0, '2025-10-07 12:08:32'),
(18, 4, 5, '2025-10-09 10:20:00', '2025-10-09 22:11:00', 'cancelled', 1, '2025-10-07 12:27:31'),
(19, 6, 4, '2025-10-09 16:00:00', '2025-10-09 17:00:00', 'pending', 0, '2025-10-07 18:48:54'),
(20, 6, 1, '2025-10-10 22:15:00', '2025-10-10 23:05:00', 'cancelled', 0, '2025-10-09 00:04:33'),
(21, 6, 3, '2025-10-22 10:05:00', '2025-10-22 11:05:00', 'approved', 0, '2025-10-09 00:05:24');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `location` varchar(120) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `equipment` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `open_from` time DEFAULT NULL,
  `open_to` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `location`, `capacity`, `equipment`, `is_active`, `created_at`, `open_from`, `open_to`, `description`, `updated_at`) VALUES
(1, 'Library-103', 'Library 1st Floor', 6, 'Projector, Whiteboard', 1, '2025-10-05 04:56:03', NULL, NULL, NULL, '2025-10-07 12:15:03'),
(2, 'Library-102', 'Library 1st Floor', 4, 'Whiteboard', 1, '2025-10-05 04:56:03', NULL, NULL, NULL, '2025-10-07 16:17:35'),
(3, 'Science-204', 'Science Block 2nd', 8, NULL, 1, '2025-10-05 04:56:03', NULL, NULL, NULL, '2025-10-07 16:17:35'),
(4, 'Melbourne', 'Melbourne Street', 20, 'Teacher', 1, '2025-10-07 12:09:17', NULL, NULL, NULL, '2025-10-07 17:54:17'),
(5, 'Library-101', 'Library 3rd', 20, 'Whiteboard', 1, '2025-10-07 12:26:29', '01:58:00', '14:30:00', NULL, '2025-10-07 18:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `student_id` varchar(40) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `student_id`, `password_hash`, `role`, `created_at`) VALUES
(2, 'John Doe', 'john@campus.local', 'S0001', '$2y$10$vcfy01gZSrhAaF7Ig84QM.PsQhAI3k8SMkLncgM0FkwsVK4H8eEeO', 'student', '2025-10-05 14:16:40'),
(3, 'Shashank Pandey', 'pandeyshashank5577@gmail.com', '2408414', '$2y$10$CzaSFnboKakbYUogKmxj/OPs2ayAXxzwXkF2/x13u0r9YEA33HN9a', 'admin', '2025-10-06 09:31:09'),
(4, 'Arpita Rajbhandari', 'rajbhandariarpita9@gmail.com', '10010', '$2y$10$0kQjZYZG1DHgXaFPnxMLNuSR9XwQbdhbwyj.n133B0wq8ilghtsie', 'student', '2025-10-06 12:32:54'),
(5, 'Admin', 'admin@example.com', 'A000', '$2y$10$LQO3hvd2d2a0B7RWa7BNc.SzknAgKt7j.wUF5I8rfNQZmvj0pdTfq', 'admin', '2025-10-07 16:10:32'),
(6, 'Barun Basnet', '986656@win.edu.au', '986656', '$2y$10$SyhMAPsr0zWh/SKZfjESDONWF715yXD2WBEnseuzuSpZol3I3ev/C', 'student', '2025-10-07 18:48:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_time` (`room_id`,`start_time`,`end_time`),
  ADD KEY `idx_user_time` (`user_id`,`start_time`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_student_id_unique` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
