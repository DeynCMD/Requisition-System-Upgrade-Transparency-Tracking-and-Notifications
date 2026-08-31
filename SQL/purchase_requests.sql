-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2026 at 02:25 PM
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
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(11) NOT NULL,
  `pr_number` varchar(30) NOT NULL,
  `requestor_name` varchar(120) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `mpn` varchar(150) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,4) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `reason` text NOT NULL,
  `notes` text DEFAULT NULL,
  `urgency` varchar(50) DEFAULT NULL,
  `required_by` date DEFAULT NULL,
  `distributor` varchar(150) DEFAULT NULL,
  `selected_distributor_text` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'PENDING',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` varchar(120) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `pr_number`, `requestor_name`, `request_date`, `category`, `mpn`, `quantity`, `unit_price`, `currency`, `reason`, `notes`, `urgency`, `required_by`, `distributor`, `selected_distributor_text`, `status`, `rejection_reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(14, 'PR-2026-0001', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99LT1G', 2213213, 0.0212, 'USD', 'HEAGA', 'HAHAWHAW', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'PENDING', NULL, NULL, NULL, '2026-01-17 12:30:51', '2026-01-17 12:30:51'),
(15, 'PR-2026-0002', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99-7-F', 321321, 0.0192, 'USD', 'DASDSADS', 'DSADSA', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0192 @ 3000 units', 'PENDING', NULL, NULL, NULL, '2026-01-17 12:40:20', '2026-01-17 12:40:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pr_number` (`pr_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
