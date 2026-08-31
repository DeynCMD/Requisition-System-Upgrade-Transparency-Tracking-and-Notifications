-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2026 at 02:26 PM
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
-- Database: `ze_electronic`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','APPROVER','REQUESTOR') NOT NULL DEFAULT 'REQUESTOR',
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `gender` char(1) DEFAULT '—',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `firstname`, `lastname`, `middlename`, `gender`, `is_active`, `created_at`, `last_login`, `reset_token`, `reset_expires`) VALUES
(1, 'andrei', 'andrei@ze-electronics.com', '$2y$10$mzgslVcdcZNrF6wERZSPfOzaGakvOEm28fjuVYIK2sc438KblR7K6', 'ADMIN', 'Andrei Christopher', 'Carillo', NULL, 'M', 1, '2026-01-14 08:28:02', NULL, NULL, NULL),
(2, 'user01', 'user01@ze-electronics.com', '$2y$10$y2cm4h1tAqYoeCRJDifr3uVtcLWeXmIMYzZpP9WuC9VMJ0Gn0JjnC', 'REQUESTOR', 'Normal', 'User', NULL, 'M', 1, '2026-01-14 09:12:40', NULL, NULL, NULL),
(6, 'admin', 'dane.rohan1112@gmail.com', '$2y$10$Zou4P5ZgMKRwrL73raT65eQAQ7cxNxM0f4pyqhMKrSU38NxXfNNr6', 'ADMIN', 'Dane Rohan', 'Dalisay', 'Llamas', 'M', 1, '2026-01-17 02:18:12', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
