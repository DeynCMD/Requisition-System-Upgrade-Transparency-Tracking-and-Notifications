-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 05:27 AM
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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `activity_type` enum('user_added','user_edited','user_deleted','request_approved','request_rejected','request_created','login','logout') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `target_user` varchar(255) DEFAULT NULL,
  `pr_number` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `activity_type`, `user_id`, `performed_by`, `target_user`, `pr_number`, `description`, `details`, `ip_address`, `created_at`) VALUES
(5, 'user_deleted', NULL, 'Admin', 'Dane Rohan Llamas Dalisay', NULL, 'User Dane Rohan Llamas Dalisay was removed from the system', 'Previous role: APPROVER, Email: dane.rohan1111@gmail.com', '::1', '2026-01-17 13:50:05'),
(6, 'request_created', NULL, 'Current User', NULL, 'PR-2026-0004', 'New purchase request submitted for 42131 Spare Parts', 'MPN: BAV99LT1G, Total value: USD 893.18, Distributor: Digi-Key — $0.0212 @ 3000 units, Urgency: Normal', '::1', '2026-01-17 13:58:16'),
(7, 'request_created', NULL, 'Current User', NULL, 'PR-2026-0005', 'New purchase request submitted for 100 Spare Parts', 'MPN: BAV99LT1G, Total value: USD 2.12, Distributor: Digi-Key — $0.0212 @ 3000 units, Urgency: Normal', '::1', '2026-01-18 06:16:51'),
(8, 'request_created', NULL, 'Current User', NULL, 'PR-2026-0006', 'New purchase request submitted for 200 Spare Parts', 'MPN: ESDALC5-1BM2, Manufacturer: STMicroelectronics, Total value: USD 8.40, Distributor: Digi-Key — $0.0420 @ 12000 units, Urgency: Normal', '::1', '2026-01-18 06:19:54'),
(9, 'request_rejected', NULL, 'Approver', NULL, 'PR-2026-0004', 'Purchase request rejected - PR-2026-0004', 'Requestor: Current User, Item: Spare Parts (42131 units), MPN: BAV99LT1G, Manufacturer: N/A, Total value: USD 893.18, Reason: testing', '::1', '2026-01-18 07:08:49'),
(10, 'request_rejected', NULL, 'Approver', NULL, 'PR-2026-0005', 'Purchase request rejected - PR-2026-0005', 'Requestor: Current User, Item: Spare Parts (100 units), MPN: BAV99LT1G, Manufacturer: N/A, Total value: USD 2.12, Reason: Testing', '::1', '2026-01-19 06:08:26'),
(11, 'request_rejected', NULL, 'Approver', NULL, 'PR-2026-0005', 'Purchase request rejected - PR-2026-0005', 'Requestor: Current User, Item: Spare Parts (100 units), MPN: BAV99LT1G, Manufacturer: N/A, Total value: USD 2.12, Reason: Testing', '::1', '2026-01-19 06:26:40'),
(12, 'request_created', NULL, 'Current User', NULL, 'PR-2026-0007', 'New purchase request submitted for 200 Spare Parts', 'MPN: BAV99-13-F, Manufacturer: Diodes Incorporated, Total value: USD 3.24, Distributor: Digi-Key — $0.0162 @ 10000 units, Urgency: Normal', '::1', '2026-01-19 11:33:30'),
(13, 'request_created', NULL, 'Current User', NULL, 'PR-2026-0008', 'New purchase request submitted for 200 Components', 'MPN: BAV99-7-F, Manufacturer: Diodes Incorporated, Total value: USD 3.84, Distributor: Digi-Key — $0.0192 @ 3000 units, Urgency: Normal', '::1', '2026-01-22 04:46:05'),
(14, 'request_created', NULL, 'Current User', NULL, 'PR-2026-0009', 'New purchase request submitted for 2000 Components', 'MPN: AL-06-18-0-C, Manufacturer: Advanced Cable Ties, Inc., Total value: USD 71.00, Distributor: Digi-Key — $0.0355 @ 100 units, Urgency: Normal', '::1', '2026-01-23 11:11:05'),
(15, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 202020 Spare Parts', 'MPN: SK12BAW01, Manufacturer: NKK Switches, Total value: USD 4,129,288.80, Distributor: Digi-Key — $20.4400 @ 1 units, Urgency: Normal', '::1', '2026-01-23 11:36:21'),
(16, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 32131 Components', 'MPN: AL-04-18-9-M, Manufacturer: Advanced Cable Ties, Inc., Total value: PHP 21,479.57, Distributor: Digi-Key — $0.0118 @ 1000 units, Urgency: Normal', '::1', '2026-01-23 14:32:47'),
(17, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 213321 Components', 'MPN: 10046, Manufacturer: SCS, Total value: PHP 1,211,663.28, Distributor: Digi-Key — $0.1000 @ 1 units, Urgency: Normal', '::1', '2026-01-23 14:35:49'),
(18, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 3232 Spare Parts', 'MPN: RCWCTE, Manufacturer: KOA Speer Electronics, Inc., Total value: PHP 13,766.38, Distributor: Digi-Key — $0.0750 @ 2000 units, Urgency: Normal', '::1', '2026-01-23 14:37:00'),
(19, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 2131 Components', 'MPN: RCUCTE, Manufacturer: KOA Speer Electronics, Inc., Total value: PHP 9,076.78, Distributor: Digi-Key — $0.0750 @ 2000 units, Urgency: Normal', '::1', '2026-01-23 14:40:02'),
(20, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 90000 IT Hardware', 'MPN: RCWCTE, Manufacturer: KOA Speer Electronics, Inc., Total value: PHP 383,346.00, Distributor: Digi-Key — $0.0750 @ 2000 units, Urgency: Normal', '::1', '2026-01-23 14:42:57'),
(21, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0010', 'New purchase request submitted for 32138921 Components', 'MPN: SBP100143WE5, Manufacturer: TE Connectivity Raychem Cable Protection, Total value: PHP 135,909,069.12, Distributor: Digi-Key — $0.0745 @ 5000 units, Urgency: Normal', '::1', '2026-01-23 15:04:54'),
(22, 'request_rejected', NULL, 'Approver', NULL, 'PR-2026-0009', 'Purchase request rejected - PR-2026-0009', 'Requestor: Current User, Item: Components (2000 units), MPN: AL-06-18-0-C, Manufacturer: Advanced Cable Ties, Inc., Total value: USD 71.00, Reason: Testing', '::1', '2026-01-24 05:01:44'),
(23, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0010', 'Purchase request approved for 32138921 Components', 'Requestor: Robert Dagooc Dalisay, MPN: SBP100143WE5, Manufacturer: TE Connectivity Raychem Cable Protection, Total value: PHP 135,909,069.12', '::1', '2026-01-24 05:01:52'),
(24, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0011', 'New purchase request submitted for 3213213 Spare Parts', 'MPN: AL-07-50-0-M, Manufacturer: Advanced Cable Ties, Inc., Total value: PHP 5,738,155.78, Distributor: Digi-Key — $0.0314 @ 1000 units, Urgency: Normal', '::1', '2026-01-24 14:57:55'),
(25, 'user_deleted', NULL, 'Admin', 'John Prades Doe', NULL, 'User John Prades Doe was removed from the system', 'Previous role: APPROVER, Email: john@ze.com', '::1', '2026-01-24 14:59:45'),
(26, 'user_added', NULL, 'Admin', 'Pikay  Dalisay', NULL, 'New user Pikay  Dalisay added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Female', '::1', '2026-01-26 13:34:05'),
(27, 'user_added', NULL, 'Admin', 'Pikay  Dal', NULL, 'New user Pikay  Dal added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Female', '::1', '2026-01-26 13:36:41'),
(28, 'user_deleted', NULL, 'Admin', 'Pikay  Dal', NULL, 'User Pikay  Dal was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 13:46:04'),
(29, 'user_added', NULL, 'Admin', 'Pikay Llamas Dalisay', NULL, 'New user Pikay Llamas Dalisay added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Female', '::1', '2026-01-26 13:53:18'),
(30, 'user_deleted', NULL, 'admin', 'Pikay Llamas Dalisay', NULL, 'User Pikay Llamas Dalisay was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:08:26'),
(31, 'user_added', NULL, 'Admin', 'Pikay Llamas Dalisay', NULL, 'New user Pikay Llamas Dalisay added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Female', '::1', '2026-01-26 14:08:44'),
(32, 'user_deleted', NULL, 'admin', 'Pikay Llamas Dalisay', NULL, 'User Pikay Llamas Dalisay was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:09:25'),
(33, 'user_deleted', NULL, 'admin', 'Pikay Dagooc Dalisay', NULL, 'User Pikay Dagooc Dalisay was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:10:37'),
(34, 'user_deleted', NULL, 'admin', 'Pikay Llamas Dalisay', NULL, 'User Pikay Llamas Dalisay was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:10:59'),
(35, 'user_deleted', NULL, 'admin', 'Robert Dagooc 321321', NULL, 'User Robert Dagooc 321321 was removed from the system', 'Previous role: ADMIN, Email: 321321321@gmail.com', '::1', '2026-01-26 14:11:25'),
(36, 'user_deleted', NULL, 'admin', 'Robert Llamas Solpico', NULL, 'User Robert Llamas Solpico was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:12:03'),
(37, 'user_deleted', NULL, 'admin', 'Robert dsadsa dsad', NULL, 'User Robert dsadsa dsad was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:13:23'),
(38, 'user_added', NULL, 'admin', 'Pikay  Solpico', NULL, 'New user Pikay  Solpico added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Male', '::1', '2026-01-26 14:13:37'),
(39, 'user_deleted', NULL, 'admin', 'Pikay  Solpico', NULL, 'User Pikay  Solpico was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:18:33'),
(40, 'user_added', NULL, 'admin', 'Robert  Dalisay', NULL, 'New user Robert  Dalisay added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Female', '::1', '2026-01-26 14:18:48'),
(41, 'user_deleted', NULL, 'admin', 'Robert  Dalisay', NULL, 'User Robert  Dalisay was removed from the system', 'Previous role: , Email: finance@gmail.com', '::1', '2026-01-26 14:20:19'),
(42, 'user_added', NULL, 'admin', 'Robert  Dalisay', NULL, 'New user Robert  Dalisay added as FINANCE', 'Username: finance, Email: finance@gmail.com, Gender: Male', '::1', '2026-01-26 14:20:46'),
(43, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0012', 'New purchase request submitted for 123123 Components', 'MPN: BAV99W,135, Manufacturer: Nexperia USA Inc., Total value: USD 2,573.27, Distributor: Digi-Key — $0.0209 @ 10000 units, Urgency: Normal', '::1', '2026-01-26 23:49:34'),
(44, 'request_rejected', NULL, 'Approver', NULL, '0', 'Purchase request rejected - 0', 'Requestor: Robert Dagooc Dalisay, Item: IT Hardware (90000 units), MPN: RCWCTE, Manufacturer: KOA Speer Electronics, Inc., Total value: PHP 383,346.00, Reason: Bad entry', '::1', '2026-01-26 23:50:46'),
(45, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0013', 'New purchase request submitted for 100 Spare Parts', 'MPN: NOZZLE-GREEN, Manufacturer: 3M, Total value: USD 478.00, Distributor: Digi-Key — $4.7800 @ 1 units, Urgency: Normal', '::1', '2026-01-28 16:45:57'),
(46, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0013', 'Purchase request approved for 100 Spare Parts', 'Requestor: Robert Dagooc Dalisay, MPN: NOZZLE-GREEN, Manufacturer: 3M, Total value: USD 478.00', '::1', '2026-01-29 14:10:51'),
(47, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0008', 'Purchase request approved for 200 Components', 'Requestor: Current User, MPN: BAV99-7-F, Manufacturer: Diodes Incorporated, Total value: USD 3.84', '::1', '2026-01-29 14:10:56'),
(48, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0006', 'Purchase request approved for 200 Spare Parts', 'Requestor: Current User, MPN: ESDALC5-1BM2, Manufacturer: STMicroelectronics, Total value: USD 8.40', '::1', '2026-01-29 15:18:44'),
(49, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0014', 'New purchase request submitted for 1000 Spare Parts', 'MPN: 1N5819, Manufacturer: STMicroelectronics, Total value: USD 130.00, Distributor: Digi-Key — $0.1300 @ 1 units, Urgency: Normal', '::1', '2026-01-29 17:32:12'),
(50, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0015', 'New purchase request submitted for 100 Spare Parts', 'MPN: LM358DR, Manufacturer: Texas Instruments, Total value: USD 8.84, Distributor: Digi-Key — $0.0884 @ 2500 units, Urgency: Normal', '::1', '2026-01-30 19:02:42'),
(51, 'user_added', NULL, 'admin', 'Buyer  Account', NULL, 'New user Buyer  Account added as ADMIN', 'Username: buyer, Email: buyer@gmail.com, Gender: Male', '::1', '2026-01-31 06:24:05'),
(52, 'user_deleted', NULL, 'admin', 'Buyer  Account', NULL, 'User Buyer  Account was removed from the system', 'Previous role: ADMIN, Email: buyer@gmail.com', '::1', '2026-01-31 06:24:45'),
(53, 'user_added', NULL, 'admin', 'Buyer  Account', NULL, 'New user Buyer  Account added as BUYER', 'Username: buyer, Email: buyer@gmail.com, Gender: Male', '::1', '2026-01-31 06:25:06'),
(54, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 43', NULL, NULL, '2026-01-31 06:55:29'),
(55, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 21', NULL, NULL, '2026-01-31 10:26:57'),
(56, 'request_created', NULL, 'Robert Dagooc Dalisay', '14', 'PR-2026-0016', 'New purchase request submitted for 100 Spare Parts', 'MPN: RC0603FR-0710KL, Manufacturer: YAGEO, Total value: USD 0.20, Distributor: Digi-Key — $0.0020 @ 5000 units, Urgency: High', '::1', '2026-01-31 12:24:52'),
(57, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0016', 'Purchase request approved for 100 Spare Parts', 'Requestor: Robert Dagooc Dalisay, MPN: RC0603FR-0710KL, Manufacturer: YAGEO, Total value: USD 0.20', '::1', '2026-01-31 14:25:38'),
(58, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 497', NULL, NULL, '2026-01-31 14:26:51'),
(59, 'user_added', NULL, 'admin', 'Kenneth  Jolloso', NULL, 'New user Kenneth  Jolloso added as REQUESTOR', 'Username: kenneth, Email: kennethjolloso@gmail.com, Gender: Male', '::1', '2026-02-02 16:03:54'),
(60, 'request_created', NULL, 'Kenneth Jolloso', '30', 'PR-2026-0017', 'New purchase request submitted for 5000 Spare Parts', 'MPN: BAV99LT1G, Manufacturer: onsemi, Total value: USD 106.00, Distributor: Digi-Key — $0.0212 @ 3000 units, Urgency: Normal', '::1', '2026-02-02 17:20:13'),
(61, 'request_created', NULL, 'Kenneth Jolloso', '30', 'PR-2026-0018', 'New purchase request submitted for 5000 Spare Parts', 'MPN: BAV99LT1G, Manufacturer: onsemi, Total value: USD 106.00, Distributor: Digi-Key — $0.0212 @ 3000 units, Urgency: Normal', '::1', '2026-02-02 17:20:17'),
(62, 'request_created', NULL, 'Kenneth Jolloso', '30', 'PR-2026-0019', 'New purchase request submitted for 5000 Spare Parts', 'MPN: BAV99LT1G, Manufacturer: onsemi, Total value: USD 106.00, Distributor: Digi-Key — $0.0212 @ 3000 units, Urgency: Normal', '::1', '2026-02-02 17:20:22'),
(63, 'user_added', NULL, 'admin', 'Dane L. Dalisay', NULL, 'New user Dane L. Dalisay added as REQUESTOR', 'Username: dane, Email: dane.rohan1111@gmail.com, Gender: Male', '::1', '2026-02-02 17:22:31'),
(64, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0018', 'New purchase request submitted for 50000 Spare Parts', 'MPN: BAV99LT1G, Manufacturer: onsemi, Total value: USD 1,060.00, Distributor: Digi-Key — $0.0212 @ 3000 units, Urgency: Normal', '::1', '2026-02-02 17:23:09'),
(65, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0018', 'Purchase request approved for 50000 Spare Parts', 'Requestor: Dane L. Dalisay, MPN: BAV99LT1G, Manufacturer: onsemi, Total value: USD 1,060.00', '::1', '2026-02-02 17:24:38'),
(66, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0019', 'New purchase request submitted for 5000 Spare Parts', 'MPN: RC0402FR-0710KL, Manufacturer: YAGEO, Total value: USD 10.00, Distributor: Digi-Key — $0.0020 @ 10000 units, Urgency: Normal', '::1', '2026-02-02 18:03:09'),
(67, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0019', 'Purchase request approved for 5000 Spare Parts', 'Requestor: Dane L. Dalisay, MPN: RC0402FR-0710KL, Manufacturer: YAGEO, Total value: USD 10.00', '::1', '2026-02-02 18:03:52'),
(68, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0020', 'New purchase request submitted for 100 Spare Parts', 'MPN: V3D4S004-1ZZ00-000, Manufacturer: Carling Technologies, Total value: USD 1,394.08, Distributor: Digi-Key — $13.9408 @ 13 units, Urgency: Normal', '::1', '2026-02-02 18:15:57'),
(69, 'request_approved', NULL, 'Approver', NULL, 'PR-2026-0020', 'Purchase request approved for 100 Spare Parts', 'Requestor: Dane L. Dalisay, MPN: V3D4S004-1ZZ00-000, Manufacturer: Carling Technologies, Total value: USD 1,394.08', '::1', '2026-02-02 18:17:01'),
(70, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0021', 'New purchase request submitted for 5000 Spare Parts', 'MPN: ESDALC5-1BM2, Manufacturer: STMicroelectronics, Total value: USD 210.00, Distributor: Digi-Key — $0.0420 @ 12000 units, Urgency: Normal', '::1', '2026-02-02 18:26:33'),
(71, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0018', 'New purchase request submitted for 5000 Spare Parts', 'MPN: JB3030AWT-P-H65EA0000-N0000001, Manufacturer: Cree LED, Total value: USD 231.00, Distributor: Digi-Key — $0.0462 @ 5000 units, Urgency: Normal', '::1', '2026-02-02 18:29:00'),
(72, 'request_approved', NULL, 'Robert Dagooc Solpico', '10', NULL, 'Purchase request approved for ID 505', 'Status changed to \'approved\'', '::1', '2026-02-02 18:45:35'),
(73, 'request_approved', NULL, 'Robert Dagooc Solpico', '10', NULL, 'Purchase request approved for ID 498', 'Status changed to \'approved\'', '::1', '2026-02-02 18:50:07'),
(74, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0019', 'New purchase request submitted for 5000 Spare Parts', 'MPN: LTST-C170GKT, Manufacturer: Lite-On Inc., Total value: USD 140.00, Distributor: Digi-Key — $0.0280 @ 3000 units, Urgency: Normal', '::1', '2026-02-03 04:37:18'),
(75, 'request_approved', NULL, 'Robert Dagooc Solpico', '10', NULL, 'Purchase request approved for ID 506', 'Status changed to \'approved\'', '::1', '2026-02-03 04:37:43'),
(76, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0020', 'New purchase request submitted for 2020 Spare Parts', 'MPN: JB3030AWT-P-U50EA0000-N0000001, Manufacturer: Cree LED, Total value: USD 98.37, Distributor: Digi-Key — $0.0487 @ 5000 units, Urgency: Normal', '::1', '2026-02-03 04:48:26'),
(77, 'request_approved', NULL, 'Robert Dagooc Solpico', '10', NULL, 'Purchase request approved for ID 507', 'Status changed to \'approved\'', '::1', '2026-02-03 05:05:46'),
(78, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0018', 'New purchase request submitted for 5000 Spare Parts', 'MPN: BAR43SFILM, Manufacturer: STMicroelectronics, Total value: USD 182.00, Distributor: Digi-Key — $0.0364 @ 3000 units, Urgency: Normal', '::1', '2026-02-03 05:47:29'),
(79, 'request_approved', NULL, 'Robert Dagooc Solpico', '10', NULL, 'Purchase request approved for ID 508', 'Status changed to \'approved\'', '::1', '2026-02-03 05:47:49'),
(80, 'request_created', NULL, 'Dane L. Dalisay', '31', 'PR-2026-0019', 'New purchase request submitted for 5000 Spare Parts', 'MPN: MP-2016-1100-50-70, Manufacturer: Luminus Devices Inc., Total value: USD 124.00, Distributor: Digi-Key — $0.0248 @ 5000 units, Urgency: Normal', '::1', '2026-02-03 05:59:27'),
(81, 'request_approved', NULL, 'Robert Dagooc Solpico', '10', NULL, 'Purchase request approved for ID 509', 'Status changed to \'approved\'', '::1', '2026-02-03 05:59:49'),
(82, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508', NULL, NULL, '2026-02-03 06:45:08'),
(83, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508', NULL, NULL, '2026-02-03 06:48:15'),
(84, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508', NULL, NULL, '2026-02-03 06:49:07'),
(85, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508', NULL, NULL, '2026-02-03 06:50:55'),
(86, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 498', NULL, NULL, '2026-02-03 06:51:31'),
(87, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508', NULL, NULL, '2026-02-03 06:59:46'),
(88, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508', NULL, NULL, '2026-02-03 07:22:23'),
(89, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 07:26:03'),
(90, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 07:27:43'),
(91, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 08:03:02'),
(92, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 08:04:41'),
(93, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 12:34:35'),
(94, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 12:43:08'),
(95, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018 (status: )', NULL, NULL, '2026-02-03 12:52:57'),
(96, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018 (status now: )', NULL, NULL, '2026-02-03 13:03:44'),
(97, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 13:14:43'),
(98, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 13:17:25'),
(99, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 13:23:12'),
(100, '', NULL, 'buyer', NULL, NULL, 'Purchased request ID 508 - PR-2026-0018', NULL, NULL, '2026-02-03 13:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `budget_transactions`
--

CREATE TABLE `budget_transactions` (
  `id` int(11) NOT NULL,
  `transaction_type` enum('add','deduct','allocate','spend','adjust') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_transactions`
--

INSERT INTO `budget_transactions` (`id`, `transaction_type`, `amount`, `department`, `description`, `performed_by`, `created_at`) VALUES
(1, 'add', 10000.00, NULL, 'for Spare Parts', 1, '2026-01-26 13:08:43'),
(2, 'deduct', 5000.00, NULL, 'Misinput', 1, '2026-01-26 13:28:07'),
(3, 'spend', 478.00, NULL, 'Approved PR #43 - ₱478.00', 27, '2026-01-29 15:06:15'),
(4, 'spend', 3.84, NULL, 'Approved PR #21 - ₱3.84', 27, '2026-01-29 15:12:06'),
(5, 'spend', 3.84, NULL, 'Approved PR #21 - ₱3.84', 27, '2026-01-29 15:13:57'),
(6, 'spend', 3.84, NULL, 'Approved PR #21 - ₱3.84', 27, '2026-01-29 15:14:18'),
(7, 'spend', 8.40, NULL, 'Approved PR #19 - ₱8.40', 27, '2026-01-29 15:20:06'),
(8, 'spend', 0.20, NULL, 'Approved PR #497 - ₱0.20', 27, '2026-01-31 14:26:00'),
(9, 'add', 5000.00, NULL, 'Test', 1, '2026-01-31 18:55:34'),
(10, 'add', 5000.00, NULL, 'Test', 1, '2026-01-31 18:59:45'),
(11, 'spend', 4.00, NULL, 'Approved PR #51 - ₱4.00', 27, '2026-02-01 19:48:42'),
(12, 'spend', 231.00, NULL, 'Approved PR #505 - ₱231.00', 27, '2026-02-02 18:46:55'),
(13, 'spend', 140.00, NULL, 'Approved PR #506 - ₱140.00', 27, '2026-02-03 04:38:08'),
(14, 'spend', 98.37, NULL, 'Approved PR #507 - ₱98.37', 27, '2026-02-03 05:08:18'),
(15, 'spend', 182.00, NULL, 'Approved PR #508 - ₱182.00', 27, '2026-02-03 05:48:18'),
(16, 'spend', 106.00, NULL, 'Approved PR #498 - ₱106.00', 27, '2026-02-03 05:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `company_budget`
--

CREATE TABLE `company_budget` (
  `id` int(11) NOT NULL,
  `total_available` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_budget`
--

INSERT INTO `company_budget` (`id`, `total_available`, `last_updated`, `updated_by`) VALUES
(1, 5000000.00, '2026-01-26 14:56:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `finance_approvals`
--

CREATE TABLE `finance_approvals` (
  `id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `pr_number` varchar(50) NOT NULL,
  `requestor_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `finance_approved_by` int(11) DEFAULT NULL,
  `finance_approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_approvals`
--

INSERT INTO `finance_approvals` (`id`, `pr_id`, `pr_number`, `requestor_name`, `department`, `total_amount`, `status`, `finance_approved_by`, `finance_approved_at`, `rejection_reason`, `created_at`) VALUES
(1, 19, 'PR-2026-0006', 'Current User', '', 8.40, 'approved', 27, '2026-01-29 15:20:06', NULL, '2026-01-29 15:20:06'),
(2, 497, 'PR-2026-0016', 'Robert Dagooc Dalisay', '', 0.20, 'approved', 27, '2026-01-31 14:26:00', NULL, '2026-01-31 14:26:00'),
(3, 51, 'DEMO-RES-202601-001', 'Historical User', '', 4.00, 'approved', 27, '2026-02-01 19:48:42', NULL, '2026-02-01 19:48:42'),
(7, 508, 'PR-2026-0018', 'Dane L. Dalisay', '', 182.00, 'approved', 27, '2026-02-03 05:48:18', NULL, '2026-02-03 05:48:18'),
(8, 498, 'PR-2026-0017', 'Kenneth Jolloso', '', 106.00, 'approved', 27, '2026-02-03 05:48:41', NULL, '2026-02-03 05:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `finance_budget`
--

CREATE TABLE `finance_budget` (
  `id` int(11) NOT NULL,
  `total_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `allocated_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `spent_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_budget`
--

INSERT INTO `finance_budget` (`id`, `total_budget`, `allocated_budget`, `spent_budget`, `remaining_budget`, `updated_at`, `updated_by`) VALUES
(1, 15000.00, 0.00, 1737.49, 13262.51, '2026-02-03 05:48:41', 27);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pr_number` varchar(20) NOT NULL DEFAULT '',
  `requestor_name` varchar(120) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `mpn` varchar(150) DEFAULT NULL,
  `manufacturer` varchar(150) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,4) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `reason` text NOT NULL,
  `notes` text DEFAULT NULL,
  `urgency` varchar(50) DEFAULT NULL,
  `required_by` date DEFAULT NULL,
  `distributor` varchar(150) DEFAULT NULL,
  `selected_distributor_text` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','rejected','approved','finance_pending','finance_approved','finance_rejected') DEFAULT 'PENDING',
  `buyer_status` enum('pending_payment','purchased') DEFAULT 'pending_payment',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` varchar(120) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `finance_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `finance_approved_by` int(11) DEFAULT NULL,
  `finance_approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `user_id`, `pr_number`, `requestor_name`, `request_date`, `category`, `mpn`, `manufacturer`, `quantity`, `unit_price`, `total_amount`, `currency`, `reason`, `notes`, `urgency`, `required_by`, `distributor`, `selected_distributor_text`, `status`, `buyer_status`, `rejection_reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `finance_status`, `finance_approved_by`, `finance_approved_at`) VALUES
(14, 0, 'PR-2026-0001', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99LT1G', NULL, 2213213, 0.0212, 46920.12, 'USD', 'HEAGA', 'HAHAWHAW', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-17 12:30:51', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(15, 0, 'PR-2026-0002', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99-7-F', NULL, 321321, 0.0192, 6169.36, 'USD', 'DASDSADS', 'DSADSA', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0192 @ 3000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-17 12:40:20', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(16, 0, 'PR-2026-0003', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAT54SWFILM', NULL, 2024, 0.0525, 106.26, 'USD', 'Test History', 'Hello', 'Normal', '2026-02-24', 'Digi-Key', 'Digi-Key — $0.0525 @ 3000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-17 13:50:53', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(17, 0, 'PR-2026-0004', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99LT1G', NULL, 42131, 0.0212, 893.18, 'USD', 'Test', 'TEst', 'Normal', '2026-02-22', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-17 13:58:16', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(18, 0, 'PR-2026-0005', 'Current User', '2026-01-18 00:00:00', 'Spare Parts', 'BAV99LT1G', NULL, 100, 0.0212, 2.12, 'USD', 'TEst', 'TEst', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-18 06:16:51', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(19, 0, 'PR-2026-0006', 'Current User', '2026-01-18 00:00:00', 'Spare Parts', 'ESDALC5-1BM2', 'STMicroelectronics', 200, 0.0420, 8.40, 'USD', 'SPare', 'Spare', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0420 @ 12000 units', '', 'pending_payment', NULL, NULL, NULL, '2026-01-18 06:19:54', '2026-02-03 13:34:13', 'approved', 27, '2026-01-29 23:20:06'),
(20, 0, 'PR-2026-0007', 'Current User', '2026-01-19 00:00:00', 'Spare Parts', 'BAV99-13-F', 'Diodes Incorporated', 200, 0.0162, 3.24, 'USD', 'Testing', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0162 @ 10000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-19 11:33:30', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(21, 0, 'PR-2026-0008', 'Current User', '2026-01-22 00:00:00', 'Components', 'BAV99-7-F', 'Diodes Incorporated', 200, 0.0192, 3.84, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0192 @ 3000 units', '', 'pending_payment', NULL, NULL, NULL, '2026-01-22 04:46:05', '2026-02-03 13:34:13', 'approved', 27, '2026-01-29 23:14:18'),
(22, 0, 'PR-2026-0009', 'Current User', '2026-01-23 00:00:00', 'Components', 'AL-06-18-0-C', 'Advanced Cable Ties, Inc.', 2000, 0.0355, 71.00, 'USD', 'Test', '', 'Normal', '0000-00-00', 'Digi-Key', 'Digi-Key — $0.0355 @ 100 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-23 11:11:05', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(35, 14, '0', 'Robert Dagooc Dalisay', '2026-01-23 00:00:00', 'IT Hardware', 'RCWCTE', 'KOA Speer Electronics, Inc.', 90000, 4.2594, 383346.00, 'PHP', 'Testung', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0750 @ 2000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-23 14:42:57', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(40, 14, 'PR-2026-0010', 'Robert Dagooc Dalisay', '2026-01-23 00:00:00', 'Components', 'SBP100143WE5', 'TE Connectivity Raychem Cable Protection', 32138921, 4.2288, 135909069.12, 'PHP', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0745 @ 5000 units', 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-23 15:04:54', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(41, 14, 'PR-2026-0011', 'Robert Dagooc Dalisay', '2026-01-24 00:00:00', 'Spare Parts', 'AL-07-50-0-M', 'Advanced Cable Ties, Inc.', 3213213, 1.7858, 5738155.78, 'PHP', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0314 @ 1000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-24 14:57:55', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(42, 14, 'PR-2026-0012', 'Robert Dagooc Dalisay', '2026-01-27 00:00:00', 'Components', 'BAV99W,135', 'Nexperia USA Inc.', 123123, 0.0209, 2573.27, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0209 @ 10000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-26 23:49:34', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(43, 14, 'PR-2026-0013', 'Robert Dagooc Dalisay', '2026-01-29 00:00:00', 'Spare Parts', 'NOZZLE-GREEN', '3M', 100, 4.7800, 478.00, 'USD', 'Spare parts', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $4.7800 @ 1 units', '', 'pending_payment', NULL, NULL, NULL, '2026-01-28 16:45:57', '2026-02-03 13:34:13', 'approved', 27, '2026-01-29 23:06:14'),
(44, 0, '', 'Historical User', '2026-01-29 12:44:49', 'Components', 'RESISTOR-10K', NULL, 100, 0.0500, 5.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(46, 1, 'DEMO-RES-202508-001', 'Historical User', '2025-08-15 12:00:00', 'Components', 'RESISTOR-10K', NULL, 100, 0.0500, 5.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(47, 1, 'DEMO-RES-202509-001', 'Historical User', '2025-09-15 12:00:00', 'Components', 'RESISTOR-10K', NULL, 100, 0.0480, 4.80, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(48, 1, 'DEMO-RES-202510-001', 'Historical User', '2025-10-15 12:00:00', 'Components', 'RESISTOR-10K', NULL, 100, 0.0460, 4.60, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(49, 1, 'DEMO-RES-202511-001', 'Historical User', '2025-11-15 12:00:00', 'Components', 'RESISTOR-10K', NULL, 100, 0.0440, 4.40, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(50, 1, 'DEMO-RES-202512-001', 'Historical User', '2025-12-15 12:00:00', 'Components', 'RESISTOR-10K', NULL, 100, 0.0420, 4.20, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(51, 1, 'DEMO-RES-202601-001', 'Historical User', '2026-01-15 12:00:00', 'Components', 'RESISTOR-10K', NULL, 100, 0.0400, 4.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-15 04:00:00', '2026-02-03 13:34:13', 'approved', 27, '2026-02-02 03:48:42'),
(52, 1, 'DEMO-CAP-202508-001', 'Historical User', '2025-08-15 12:00:00', 'Components', 'CAPACITOR-100uF', NULL, 100, 0.1000, 10.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(53, 1, 'DEMO-CAP-202509-001', 'Historical User', '2025-09-15 12:00:00', 'Components', 'CAPACITOR-100uF', NULL, 100, 0.1050, 10.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(54, 1, 'DEMO-CAP-202510-001', 'Historical User', '2025-10-15 12:00:00', 'Components', 'CAPACITOR-100uF', NULL, 100, 0.1100, 11.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(55, 1, 'DEMO-CAP-202511-001', 'Historical User', '2025-11-15 12:00:00', 'Components', 'CAPACITOR-100uF', NULL, 100, 0.1150, 11.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(56, 1, 'DEMO-CAP-202512-001', 'Historical User', '2025-12-15 12:00:00', 'Components', 'CAPACITOR-100uF', NULL, 100, 0.1200, 12.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(57, 1, 'DEMO-CAP-202601-001', 'Historical User', '2026-01-15 12:00:00', 'Components', 'CAPACITOR-100uF', NULL, 100, 0.1250, 12.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(58, 1, 'DEMO-TRA-202508-001', 'Historical User', '2025-08-15 12:00:00', 'Components', 'TRANSISTOR-2N2222', NULL, 100, 1.0000, 100.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(59, 1, 'DEMO-TRA-202509-001', 'Historical User', '2025-09-15 12:00:00', 'Components', 'TRANSISTOR-2N2222', NULL, 100, 1.0100, 101.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(60, 1, 'DEMO-TRA-202510-001', 'Historical User', '2025-10-15 12:00:00', 'Components', 'TRANSISTOR-2N2222', NULL, 100, 0.9900, 99.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(61, 1, 'DEMO-TRA-202511-001', 'Historical User', '2025-11-15 12:00:00', 'Components', 'TRANSISTOR-2N2222', NULL, 100, 1.0000, 100.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(62, 1, 'DEMO-TRA-202512-001', 'Historical User', '2025-12-15 12:00:00', 'Components', 'TRANSISTOR-2N2222', NULL, 100, 1.0050, 100.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(63, 1, 'DEMO-TRA-202601-001', 'Historical User', '2026-01-15 12:00:00', 'Components', 'TRANSISTOR-2N2222', NULL, 100, 0.9950, 99.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(64, 1, 'DEMO-DIO-202508-001', 'Historical User', '2025-08-15 12:00:00', 'Components', 'DIODE-1N4148', NULL, 100, 0.0200, 2.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(65, 1, 'DEMO-DIO-202509-001', 'Historical User', '2025-09-15 12:00:00', 'Components', 'DIODE-1N4148', NULL, 100, 0.0300, 3.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(66, 1, 'DEMO-DIO-202510-001', 'Historical User', '2025-10-15 12:00:00', 'Components', 'DIODE-1N4148', NULL, 100, 0.0250, 2.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(67, 1, 'DEMO-DIO-202511-001', 'Historical User', '2025-11-15 12:00:00', 'Components', 'DIODE-1N4148', NULL, 100, 0.0350, 3.50, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(68, 1, 'DEMO-DIO-202512-001', 'Historical User', '2025-12-15 12:00:00', 'Components', 'DIODE-1N4148', NULL, 100, 0.0200, 2.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(69, 1, 'DEMO-DIO-202601-001', 'Historical User', '2026-01-15 12:00:00', 'Components', 'DIODE-1N4148', NULL, 100, 0.0280, 2.80, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(70, 1, 'DEMO-MCU-202508-001', 'Historical User', '2025-08-15 12:00:00', 'Components', 'MCU-ATMEGA328', NULL, 100, 10.0000, 1000.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(71, 1, 'DEMO-MCU-202509-001', 'Historical User', '2025-09-15 12:00:00', 'Components', 'MCU-ATMEGA328', NULL, 100, 9.5000, 950.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(72, 1, 'DEMO-MCU-202510-001', 'Historical User', '2025-10-15 12:00:00', 'Components', 'MCU-ATMEGA328', NULL, 100, 9.0000, 900.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(73, 1, 'DEMO-MCU-202511-001', 'Historical User', '2025-11-15 12:00:00', 'Components', 'MCU-ATMEGA328', NULL, 100, 8.5000, 850.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(74, 1, 'DEMO-MCU-202512-001', 'Historical User', '2025-12-15 12:00:00', 'Components', 'MCU-ATMEGA328', NULL, 100, 4.0000, 400.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(75, 1, 'DEMO-MCU-202601-001', 'Historical User', '2026-01-15 12:00:00', 'Components', 'MCU-ATMEGA328', NULL, 100, 3.5000, 350.00, 'USD', 'Historical data for price prediction demo', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(76, 14, 'PR-2026-0014', 'Robert Dagooc Dalisay', '2026-01-30 00:00:00', 'Spare Parts', '1N5819', 'STMicroelectronics', 1000, 0.1300, 130.00, 'USD', 'Testing', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.1300 @ 1 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-29 17:32:12', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(149, 0, 'DEMO-BAV99-202001', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1480, 148.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(150, 0, 'DEMO-BAV99-202002', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1455, 145.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(151, 0, 'DEMO-BAV99-202003', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1472, 147.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(152, 0, 'DEMO-BAV99-202004', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1428, 142.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(153, 0, 'DEMO-BAV99-202005', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1401, 140.10, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(154, 0, 'DEMO-BAV99-202006', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1389, 138.90, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(155, 0, 'DEMO-BAV99-202007', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1364, 136.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(156, 0, 'DEMO-BAV99-202008', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1347, 134.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(157, 0, 'DEMO-BAV99-202009', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1320, 132.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(158, 0, 'DEMO-BAV99-202010', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1298, 129.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(159, 0, 'DEMO-BAV99-202011', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1275, 127.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(160, 0, 'DEMO-BAV99-202012', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1253, 125.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(161, 0, 'DEMO-BAV99-202101', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1221, 122.10, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(162, 0, 'DEMO-BAV99-202102', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1198, 119.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(163, 0, 'DEMO-BAV99-202103', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1174, 117.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(164, 0, 'DEMO-BAV99-202104', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1150, 115.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(165, 0, 'DEMO-BAV99-202105', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1127, 112.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(166, 0, 'DEMO-BAV99-202106', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1103, 110.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(167, 0, 'DEMO-BAV99-202107', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1080, 108.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(168, 0, 'DEMO-BAV99-202108', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1056, 105.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(169, 0, 'DEMO-BAV99-202109', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1033, 103.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(170, 0, 'DEMO-BAV99-202110', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.1010, 101.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(171, 0, 'DEMO-BAV99-202111', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0987, 98.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(172, 0, 'DEMO-BAV99-202112', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0964, 96.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(173, 0, 'DEMO-BAV99-202201', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0941, 94.10, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(174, 0, 'DEMO-BAV99-202202', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0918, 91.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(175, 0, 'DEMO-BAV99-202203', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0895, 89.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(176, 0, 'DEMO-BAV99-202204', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0872, 87.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(177, 0, 'DEMO-BAV99-202205', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0849, 84.90, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(178, 0, 'DEMO-BAV99-202206', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0826, 82.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(179, 0, 'DEMO-BAV99-202207', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0803, 80.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(180, 0, 'DEMO-BAV99-202208', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0780, 78.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(181, 0, 'DEMO-BAV99-202209', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0757, 75.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(182, 0, 'DEMO-BAV99-202210', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0734, 73.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(183, 0, 'DEMO-BAV99-202211', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0711, 71.10, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(184, 0, 'DEMO-BAV99-202212', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0688, 68.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(185, 0, 'DEMO-BAV99-202301', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0665, 66.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(186, 0, 'DEMO-BAV99-202302', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0642, 64.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(187, 0, 'DEMO-BAV99-202303', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0619, 61.90, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(188, 0, 'DEMO-BAV99-202304', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0596, 59.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(189, 0, 'DEMO-BAV99-202305', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0573, 57.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(190, 0, 'DEMO-BAV99-202306', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0550, 55.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(191, 0, 'DEMO-BAV99-202307', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0527, 52.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(192, 0, 'DEMO-BAV99-202308', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0504, 50.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(193, 0, 'DEMO-BAV99-202309', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0481, 48.10, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(194, 0, 'DEMO-BAV99-202310', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0458, 45.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(195, 0, 'DEMO-BAV99-202311', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0435, 43.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(196, 0, 'DEMO-BAV99-202312', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0412, 41.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(197, 0, 'DEMO-BAV99-202401', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0389, 38.90, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(198, 0, 'DEMO-BAV99-202402', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0366, 36.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(199, 0, 'DEMO-BAV99-202403', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0343, 34.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(200, 0, 'DEMO-BAV99-202404', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0320, 32.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(201, 0, 'DEMO-BAV99-202405', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0297, 29.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(202, 0, 'DEMO-BAV99-202406', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0274, 27.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(203, 0, 'DEMO-BAV99-202407', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0251, 25.10, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(204, 0, 'DEMO-BAV99-202408', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0238, 23.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(205, 0, 'DEMO-BAV99-202409', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0230, 23.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(206, 0, 'DEMO-BAV99-202410', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0225, 22.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(207, 0, 'DEMO-BAV99-202411', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(208, 0, 'DEMO-BAV99-202412', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0218, 21.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(209, 0, 'DEMO-BAV99-202501', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0216, 21.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(210, 0, 'DEMO-BAV99-202502', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0215, 21.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(211, 0, 'DEMO-BAV99-202503', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0214, 21.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(212, 0, 'DEMO-BAV99-202504', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0213, 21.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(213, 0, 'DEMO-BAV99-202505', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0213, 21.30, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(214, 0, 'DEMO-BAV99-202506', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(215, 0, 'DEMO-BAV99-202507', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(216, 0, 'DEMO-BAV99-202508', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(217, 0, 'DEMO-BAV99-202509', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(218, 0, 'DEMO-BAV99-202510', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(219, 0, 'DEMO-BAV99-202511', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(220, 0, 'DEMO-BAV99-202512', 'Mock Admin', '2026-01-31 00:05:10', 'Diode', 'BAV99LT1G', NULL, 1000, 0.0212, 21.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(221, 0, 'DEMO-RC0603-202001', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0180, 18.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(222, 0, 'DEMO-RC0603-202002', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0178, 17.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(223, 0, 'DEMO-RC0603-202003', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0176, 17.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(224, 0, 'DEMO-RC0603-202004', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0174, 17.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(225, 0, 'DEMO-RC0603-202005', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0172, 17.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(226, 0, 'DEMO-RC0603-202006', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0170, 17.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(227, 0, 'DEMO-RC0603-202007', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0168, 16.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(228, 0, 'DEMO-RC0603-202008', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0166, 16.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(229, 0, 'DEMO-RC0603-202009', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0164, 16.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(230, 0, 'DEMO-RC0603-202010', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0162, 16.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(231, 0, 'DEMO-RC0603-202011', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0160, 16.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(232, 0, 'DEMO-RC0603-202012', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0158, 15.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(233, 0, 'DEMO-RC0603-202101', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0145, 14.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(234, 0, 'DEMO-RC0603-202112', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0100, 10.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(235, 0, 'DEMO-RC0603-202212', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0070, 7.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(236, 0, 'DEMO-RC0603-202312', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0050, 5.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(237, 0, 'DEMO-RC0603-202412', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0030, 3.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(238, 0, 'DEMO-RC0603-202501', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0020, 2.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(239, 0, 'DEMO-RC0603-202502', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0019, 1.90, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(240, 0, 'DEMO-RC0603-202503', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0018, 1.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(241, 0, 'DEMO-RC0603-202504', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0017, 1.70, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(242, 0, 'DEMO-RC0603-202505', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0016, 1.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(243, 0, 'DEMO-RC0603-202506', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(244, 0, 'DEMO-RC0603-202507', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(245, 0, 'DEMO-RC0603-202508', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(246, 0, 'DEMO-RC0603-202509', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(247, 0, 'DEMO-RC0603-202510', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(248, 0, 'DEMO-RC0603-202511', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(249, 0, 'DEMO-RC0603-202512', 'Mock Admin', '2026-01-31 01:24:52', 'Resistor', 'RC0603FR-0710KL', NULL, 1000, 0.0015, 1.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(250, 0, 'DEMO-GRM188-202001', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0500, 50.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(251, 0, 'DEMO-GRM188-202002', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0495, 49.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(252, 0, 'DEMO-GRM188-202003', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0490, 49.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(253, 0, 'DEMO-GRM188-202004', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0485, 48.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(254, 0, 'DEMO-GRM188-202005', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0480, 48.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(255, 0, 'DEMO-GRM188-202006', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0475, 47.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(256, 0, 'DEMO-GRM188-202007', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0470, 47.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(257, 0, 'DEMO-GRM188-202008', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0465, 46.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(258, 0, 'DEMO-GRM188-202009', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0460, 46.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(259, 0, 'DEMO-GRM188-202010', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0455, 45.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(260, 0, 'DEMO-GRM188-202011', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0450, 45.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(261, 0, 'DEMO-GRM188-202012', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0445, 44.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL);
INSERT INTO `purchase_requests` (`id`, `user_id`, `pr_number`, `requestor_name`, `request_date`, `category`, `mpn`, `manufacturer`, `quantity`, `unit_price`, `total_amount`, `currency`, `reason`, `notes`, `urgency`, `required_by`, `distributor`, `selected_distributor_text`, `status`, `buyer_status`, `rejection_reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `finance_status`, `finance_approved_by`, `finance_approved_at`) VALUES
(262, 0, 'DEMO-GRM188-202101', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0400, 40.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(263, 0, 'DEMO-GRM188-202112', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0250, 25.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(264, 0, 'DEMO-GRM188-202212', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0180, 18.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(265, 0, 'DEMO-GRM188-202312', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0120, 12.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(266, 0, 'DEMO-GRM188-202412', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0100, 10.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(267, 0, 'DEMO-GRM188-202501', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0090, 9.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(268, 0, 'DEMO-GRM188-202502', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0088, 8.80, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(269, 0, 'DEMO-GRM188-202503', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0086, 8.60, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(270, 0, 'DEMO-GRM188-202504', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0084, 8.40, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(271, 0, 'DEMO-GRM188-202505', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0082, 8.20, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(272, 0, 'DEMO-GRM188-202506', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(273, 0, 'DEMO-GRM188-202507', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(274, 0, 'DEMO-GRM188-202508', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(275, 0, 'DEMO-GRM188-202509', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(276, 0, 'DEMO-GRM188-202510', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(277, 0, 'DEMO-GRM188-202511', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(278, 0, 'DEMO-GRM188-202512', 'Mock Admin', '2026-01-31 01:25:17', 'Capacitor', 'GRM188R71H104KA93D', NULL, 1000, 0.0080, 8.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(279, 0, 'DEMO-2N7002-202001', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.1000, 100.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(280, 0, 'DEMO-2N7002-202002', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0980, 98.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(281, 0, 'DEMO-2N7002-202003', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0960, 96.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(282, 0, 'DEMO-2N7002-202004', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0940, 94.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(283, 0, 'DEMO-2N7002-202005', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0920, 92.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(284, 0, 'DEMO-2N7002-202006', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0900, 90.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(285, 0, 'DEMO-2N7002-202007', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0880, 88.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(286, 0, 'DEMO-2N7002-202008', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0860, 86.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(287, 0, 'DEMO-2N7002-202009', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0840, 84.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(288, 0, 'DEMO-2N7002-202010', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0820, 82.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(289, 0, 'DEMO-2N7002-202011', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0800, 80.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(290, 0, 'DEMO-2N7002-202012', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0780, 78.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(291, 0, 'DEMO-2N7002-202101', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0700, 70.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(292, 0, 'DEMO-2N7002-202112', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0500, 50.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(293, 0, 'DEMO-2N7002-202212', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0400, 40.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(294, 0, 'DEMO-2N7002-202312', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0350, 35.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(295, 0, 'DEMO-2N7002-202412', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0300, 30.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(296, 0, 'DEMO-2N7002-202501', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0250, 25.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(297, 0, 'DEMO-2N7002-202502', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0240, 24.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(298, 0, 'DEMO-2N7002-202503', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0235, 23.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(299, 0, 'DEMO-2N7002-202504', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0230, 23.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(300, 0, 'DEMO-2N7002-202505', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0225, 22.50, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(301, 0, 'DEMO-2N7002-202506', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(302, 0, 'DEMO-2N7002-202507', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(303, 0, 'DEMO-2N7002-202508', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(304, 0, 'DEMO-2N7002-202509', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(305, 0, 'DEMO-2N7002-202510', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(306, 0, 'DEMO-2N7002-202511', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(307, 0, 'DEMO-2N7002-202512', 'Mock Admin', '2026-01-31 01:25:29', 'Transistor', '2N7002', NULL, 1000, 0.0220, 22.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(424, 0, 'LM358-HIST-202001', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2500, 250.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(425, 0, 'LM358-HIST-202002', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2480, 248.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(426, 0, 'LM358-HIST-202003', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2460, 246.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(427, 0, 'LM358-HIST-202004', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2440, 244.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(428, 0, 'LM358-HIST-202005', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2420, 242.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(429, 0, 'LM358-HIST-202006', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2400, 240.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(430, 0, 'LM358-HIST-202007', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2380, 238.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(431, 0, 'LM358-HIST-202008', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2360, 236.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(432, 0, 'LM358-HIST-202009', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2340, 234.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(433, 0, 'LM358-HIST-202010', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2320, 232.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(434, 0, 'LM358-HIST-202011', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2300, 230.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(435, 0, 'LM358-HIST-202012', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2280, 228.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2020-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(436, 0, 'LM358-HIST-202101', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2100, 210.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(437, 0, 'LM358-HIST-202102', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2080, 208.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(438, 0, 'LM358-HIST-202103', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2060, 206.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(439, 0, 'LM358-HIST-202104', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2040, 204.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(440, 0, 'LM358-HIST-202105', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2020, 202.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(441, 0, 'LM358-HIST-202106', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.2000, 200.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(442, 0, 'LM358-HIST-202107', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1980, 198.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(443, 0, 'LM358-HIST-202108', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1960, 196.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(444, 0, 'LM358-HIST-202109', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1940, 194.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(445, 0, 'LM358-HIST-202110', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1920, 192.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(446, 0, 'LM358-HIST-202111', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1900, 190.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(447, 0, 'LM358-HIST-202112', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1880, 188.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2021-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(448, 0, 'LM358-HIST-202201', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1750, 175.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(449, 0, 'LM358-HIST-202202', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1730, 173.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(450, 0, 'LM358-HIST-202203', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1710, 171.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(451, 0, 'LM358-HIST-202204', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1690, 169.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(452, 0, 'LM358-HIST-202205', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1670, 167.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(453, 0, 'LM358-HIST-202206', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1650, 165.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(454, 0, 'LM358-HIST-202207', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1630, 163.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(455, 0, 'LM358-HIST-202208', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1610, 161.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(456, 0, 'LM358-HIST-202209', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1590, 159.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(457, 0, 'LM358-HIST-202210', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1570, 157.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(458, 0, 'LM358-HIST-202211', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1550, 155.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(459, 0, 'LM358-HIST-202212', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1530, 153.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2022-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(460, 0, 'LM358-HIST-202301', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1400, 140.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(461, 0, 'LM358-HIST-202302', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1380, 138.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(462, 0, 'LM358-HIST-202303', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1360, 136.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(463, 0, 'LM358-HIST-202304', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1340, 134.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(464, 0, 'LM358-HIST-202305', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1320, 132.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(465, 0, 'LM358-HIST-202306', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1300, 130.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(466, 0, 'LM358-HIST-202307', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1280, 128.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(467, 0, 'LM358-HIST-202308', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1260, 126.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(468, 0, 'LM358-HIST-202309', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1240, 124.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(469, 0, 'LM358-HIST-202310', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1220, 122.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(470, 0, 'LM358-HIST-202311', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1200, 120.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(471, 0, 'LM358-HIST-202312', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1180, 118.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2023-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(472, 0, 'LM358-HIST-202401', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1050, 105.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(473, 0, 'LM358-HIST-202402', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1030, 103.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(474, 0, 'LM358-HIST-202403', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.1010, 101.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(475, 0, 'LM358-HIST-202404', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0990, 99.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(476, 0, 'LM358-HIST-202405', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0970, 97.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(477, 0, 'LM358-HIST-202406', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0950, 95.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(478, 0, 'LM358-HIST-202407', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0930, 93.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(479, 0, 'LM358-HIST-202408', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0910, 91.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(480, 0, 'LM358-HIST-202409', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0890, 89.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(481, 0, 'LM358-HIST-202410', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0870, 87.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(482, 0, 'LM358-HIST-202411', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0850, 85.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(483, 0, 'LM358-HIST-202412', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0830, 83.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2024-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(484, 0, 'LM358-HIST-202501', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0750, 75.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-01-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(485, 0, 'LM358-HIST-202502', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0740, 74.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-02-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(486, 0, 'LM358-HIST-202503', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0730, 73.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-03-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(487, 0, 'LM358-HIST-202504', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0720, 72.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-04-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(488, 0, 'LM358-HIST-202505', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0710, 71.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-05-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(489, 0, 'LM358-HIST-202506', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0700, 70.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-06-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(490, 0, 'LM358-HIST-202507', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0690, 69.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-07-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(491, 0, 'LM358-HIST-202508', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0680, 68.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(492, 0, 'LM358-HIST-202509', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0670, 67.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(493, 0, 'LM358-HIST-202510', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0660, 66.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(494, 0, 'LM358-HIST-202511', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0650, 65.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(495, 0, 'LM358-HIST-202512', 'Mock Admin', '2026-01-31 02:40:26', 'Op-Amp', 'LM358DR', NULL, 1000, 0.0650, 65.00, 'USD', 'Mock historical entry', NULL, NULL, NULL, NULL, NULL, 'approved', 'pending_payment', NULL, NULL, NULL, '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(496, 14, 'PR-2026-0015', 'Robert Dagooc Dalisay', '2026-01-31 00:00:00', 'Spare Parts', 'LM358DR', 'Texas Instruments', 100, 0.0884, 8.84, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0884 @ 2500 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-30 19:02:42', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(497, 14, 'PR-2026-0016', 'Robert Dagooc Dalisay', '2026-01-31 00:00:00', 'Spare Parts', 'RC0603FR-0710KL', 'YAGEO', 100, 0.0020, 0.20, 'USD', 'Testing', '', 'High', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0020 @ 5000 units', '', 'pending_payment', NULL, NULL, NULL, '2026-01-31 12:24:52', '2026-02-03 13:34:13', 'approved', 27, '2026-01-31 22:26:00'),
(498, 30, 'PR-2026-0017', 'Kenneth Jolloso', '2026-02-03 00:00:00', 'Spare Parts', 'BAV99LT1G', 'onsemi', 5000, 0.0212, 106.00, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', '', 'pending_payment', NULL, '10', '2026-02-03 02:50:07', '2026-02-02 17:20:13', '2026-02-03 13:34:13', 'approved', 27, '2026-02-03 13:48:41'),
(508, 31, 'PR-2026-0018', 'Dane L. Dalisay', '2026-02-03 00:00:00', 'Spare Parts', 'BAR43SFILM', 'STMicroelectronics', 5000, 0.0364, 182.00, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0364 @ 3000 units', '', 'purchased', NULL, '10', '2026-02-03 13:47:49', '2026-02-03 05:47:29', '2026-02-03 13:34:13', 'approved', 27, '2026-02-03 13:48:18'),
(509, 31, 'PR-2026-0019', 'Dane L. Dalisay', '2026-02-03 00:00:00', 'Spare Parts', 'MP-2016-1100-50-70', 'Luminus Devices Inc.', 5000, 0.0248, 124.00, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0248 @ 5000 units', 'approved', 'pending_payment', NULL, '10', '2026-02-03 13:59:49', '2026-02-03 05:59:27', '2026-02-03 13:34:13', 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `mpn` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,4) DEFAULT 0.0000,
  `total_price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','APPROVER','REQUESTOR','FINANCE','BUYER') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `gender` enum('Male','Female') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `middlename`, `username`, `email`, `password`, `role`, `is_active`, `gender`, `created_at`) VALUES
(1, 'Robert', 'Solpico', 'Carillo', 'admin', 'admin@ze.com', '$2y$10$pylWl8HPnUHUftdvfboV5uLoCxKQ0BJbXTjhbu0pSes/2J0.H0D8S', 'ADMIN', 1, 'Male', '2025-12-03 16:22:52'),
(3, 'Maria', 'Garcia', 'Prades', 'maria', 'maria@ze.com', '$2y$10$3f3d9K8bF7gH2jL5mN9p/.rT6vX8cY1aZ2bC4dE6fG8hI0jK2lM3n', 'REQUESTOR', 1, 'Female', '2025-12-03 16:22:52'),
(10, 'Robert', 'Solpico', 'Dagooc', 'approver', 'approver@gmail.com', '$2y$10$cMHcXLIgMmjJ/E.5SJ.aU.t.TSsZKrhILu3kR/xt2kXKEhHRU16je', 'APPROVER', 1, 'Male', '2026-01-13 12:03:09'),
(12, 'Dane', 'Dalisay', 'Llamas', 'admin123', 'dane.rohan1112@gmail.com', '$2b$10$TIaQF8llNmvSll81QuQbA.cWscKrf2IE6l033MTMKHPMBWMwo.FAS', 'APPROVER', 1, 'Male', '2026-01-19 04:49:52'),
(14, 'Robert', 'Dalisay', 'Dagooc', 'requestor', 'requestor@gmail.com', '$2y$10$kfQboqF65.MMMZW1LLbnne2iPdRNxGNSEQ9PkgZbqNY8Hvu1I0fRG', 'REQUESTOR', 1, 'Male', '2026-01-19 05:06:58'),
(27, 'Robert', 'Dalisay', '', 'finance', 'finance@gmail.com', '$2y$10$bZnJU7CRv8z/t98tUMqBY.///rnHVaJh5l.pB/m6WZnFDJN6Orey.', 'FINANCE', 1, 'Male', '2026-01-26 14:20:46'),
(29, 'Buyer', 'Account', '', 'buyer', 'buyer@gmail.com', '$2y$10$oBbv.QmVNrkbysq75488m.RxUDeSzNou2cGYYW3yvjv/KczjqIoc2', 'BUYER', 1, 'Male', '2026-01-31 06:25:06'),
(30, 'Kenneth', 'Jolloso', '', 'kenneth', 'kennethjolloso@gmail.com', '$2y$10$7lXHWtxj0s9rlRDsUltKweZKCyquaHXq1VaywGmQGyT8IhrH.MkM6', 'REQUESTOR', 1, 'Male', '2026-02-02 16:03:54'),
(31, 'Dane', 'Dalisay', 'L.', 'dane', 'dane.rohan1111@gmail.com', '$2y$10$e2Hv5BnJZW4CkIOeu.uOHOIi8UcV16dvLX7Nx8eCa1q.Wopd5Dfdu', 'REQUESTOR', 1, 'Male', '2026-02-02 17:22:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `budget_transactions`
--
ALTER TABLE `budget_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_type` (`transaction_type`),
  ADD KEY `department` (`department`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `company_budget`
--
ALTER TABLE `company_budget`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `finance_approvals`
--
ALTER TABLE `finance_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pr_id` (`pr_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `finance_budget`
--
ALTER TABLE `finance_budget`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pr_number` (`pr_number`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `budget_transactions`
--
ALTER TABLE `budget_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `company_budget`
--
ALTER TABLE `company_budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `finance_approvals`
--
ALTER TABLE `finance_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `finance_budget`
--
ALTER TABLE `finance_budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=510;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `finance_approvals`
--
ALTER TABLE `finance_approvals`
  ADD CONSTRAINT `finance_approvals_ibfk_1` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `purchase_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
