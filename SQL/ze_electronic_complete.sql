-- ============================================================
-- ZE Electronics — Complete Database
-- Single file: all tables, data, indexes, and all features
-- Import this in phpMyAdmin: ze_electronic database → SQL tab
-- ============================================================
-- Host: 127.0.0.1
-- Server version: 10.4.32-MariaDB
-- Updated: June 2026 (includes additional features update)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- CREATE DATABASE (optional — skip if already created)
-- ============================================================
CREATE DATABASE IF NOT EXISTS `ze_electronic`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `ze_electronic`;

-- ============================================================
-- TABLE: activity_logs
-- (includes po_created, po_updated event types)
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_type` enum(
    'user_added','user_edited','user_deleted',
    'request_approved','request_rejected','request_created',
    'login','logout','purchase',
    'budget_allocated','budget_adjusted',
    'withdrawal_requested','withdrawal_approved','withdrawal_rejected',
    'po_created','po_updated'
  ) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `target_user` varchar(255) DEFAULT NULL,
  `pr_number` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=101;

INSERT INTO `activity_logs` (`id`, `activity_type`, `user_id`, `performed_by`, `target_user`, `pr_number`, `description`, `details`, `ip_address`, `created_at`) VALUES
(5,   'user_deleted',    NULL, 'Admin',                  'Dane Rohan Llamas Dalisay', NULL,           'User Dane Rohan Llamas Dalisay was removed from the system', 'Previous role: APPROVER, Email: dane.rohan1111@gmail.com', '::1', '2026-01-17 13:50:05'),
(6,   'request_created', NULL, 'Current User',           NULL, 'PR-2026-0004', 'New purchase request submitted for 42131 Spare Parts',  'MPN: BAV99LT1G, Total value: USD 893.18, Urgency: Normal', '::1', '2026-01-17 13:58:16'),
(7,   'request_created', NULL, 'Current User',           NULL, 'PR-2026-0005', 'New purchase request submitted for 100 Spare Parts',    'MPN: BAV99LT1G, Total value: USD 2.12, Urgency: Normal',  '::1', '2026-01-18 06:16:51'),
(8,   'request_created', NULL, 'Current User',           NULL, 'PR-2026-0006', 'New purchase request submitted for 200 Spare Parts',    'MPN: ESDALC5-1BM2, Manufacturer: STMicroelectronics, Total value: USD 8.40', '::1', '2026-01-18 06:19:54'),
(9,   'request_rejected',NULL, 'Approver',               NULL, 'PR-2026-0004', 'Purchase request rejected - PR-2026-0004',              'Reason: testing',  '::1', '2026-01-18 07:08:49'),
(10,  'request_rejected',NULL, 'Approver',               NULL, 'PR-2026-0005', 'Purchase request rejected - PR-2026-0005',              'Reason: Testing',  '::1', '2026-01-19 06:08:26'),
(23,  'request_approved',NULL, 'Approver',               NULL, 'PR-2026-0010', 'Purchase request approved for 32138921 Components',     'MPN: SBP100143WE5','::1', '2026-01-24 05:01:52'),
(46,  'request_approved',NULL, 'Approver',               NULL, 'PR-2026-0013', 'Purchase request approved for 100 Spare Parts',         'MPN: NOZZLE-GREEN','::1', '2026-01-29 14:10:51'),
(47,  'request_approved',NULL, 'Approver',               NULL, 'PR-2026-0008', 'Purchase request approved for 200 Components',          'MPN: BAV99-7-F',   '::1', '2026-01-29 14:10:56'),
(48,  'request_approved',NULL, 'Approver',               NULL, 'PR-2026-0006', 'Purchase request approved for 200 Spare Parts',         'MPN: ESDALC5-1BM2','::1', '2026-01-29 15:18:44'),
(53,  'user_added',      NULL, 'admin',                  'Buyer  Account',            NULL,           'New user Buyer Account added as BUYER',                  'Username: buyer, Email: buyer@gmail.com',              '::1', '2026-01-31 06:25:06'),
(57,  'request_approved',NULL, 'Approver',               NULL, 'PR-2026-0016', 'Purchase request approved for 100 Spare Parts',         'MPN: RC0603FR-0710KL','::1','2026-01-31 14:25:38'),
(59,  'user_added',      NULL, 'admin',                  'Kenneth  Jolloso',          NULL,           'New user Kenneth Jolloso added as REQUESTOR',            'Username: kenneth, Email: kennethjolloso@gmail.com',   '::1', '2026-02-02 16:03:54'),
(63,  'user_added',      NULL, 'admin',                  'Dane L. Dalisay',           NULL,           'New user Dane L. Dalisay added as REQUESTOR',            'Username: dane, Email: dane.rohan1111@gmail.com',      '::1', '2026-02-02 17:22:31'),
(65,  'request_approved',NULL, 'Approver',               NULL, 'PR-2026-0018', 'Purchase request approved for 50000 Spare Parts',       'MPN: BAV99LT1G',   '::1', '2026-02-02 17:24:38'),
(72,  'request_approved',NULL, 'Robert Dagooc Solpico',  '10', NULL,           'Purchase request approved for ID 505',                  'Status changed to approved','::1','2026-02-02 18:45:35'),
(79,  'request_approved',NULL, 'Robert Dagooc Solpico',  '10', NULL,           'Purchase request approved for ID 508',                  'Status changed to approved','::1','2026-02-03 05:47:49'),
(81,  'request_approved',NULL, 'Robert Dagooc Solpico',  '10', NULL,           'Purchase request approved for ID 509',                  'Status changed to approved','::1','2026-02-03 05:59:49'),
(100, 'purchase',        NULL, 'buyer',                  NULL, NULL,           'Purchased request ID 508 - PR-2026-0018',               NULL, NULL, '2026-02-03 13:29:52');

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','APPROVER','REQUESTOR','FINANCE','BUYER') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `gender` enum('Male','Female') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=32;

INSERT INTO `users` (`id`, `firstname`, `lastname`, `middlename`, `username`, `email`, `password`, `role`, `is_active`, `gender`, `created_at`) VALUES
(1,  'Robert',  'Solpico',  'Carillo', 'admin',     'admin@ze.com',             '$2y$10$pylWl8HPnUHUftdvfboV5uLoCxKQ0BJbXTjhbu0pSes/2J0.H0D8S', 'ADMIN',     1, 'Male',   '2025-12-03 16:22:52'),
(3,  'Maria',   'Garcia',   'Prades',  'maria',     'maria@ze.com',             '$2y$10$3f3d9K8bF7gH2jL5mN9p/.rT6vX8cY1aZ2bC4dE6fG8hI0jK2lM3n', 'REQUESTOR', 1, 'Female', '2025-12-03 16:22:52'),
(10, 'Robert',  'Solpico',  'Dagooc',  'approver',  'approver@gmail.com',       '$2y$10$cMHcXLIgMmjJ/E.5SJ.aU.t.TSsZKrhILu3kR/xt2kXKEhHRU16je', 'APPROVER',  1, 'Male',   '2026-01-13 12:03:09'),
(12, 'Dane',    'Dalisay',  'Llamas',  'admin123',  'dane.rohan1112@gmail.com', '$2y$10$Zou4P5ZgMKRwrL73raT65eQAQ7cxNxM0f4pyqhMKrSU38NxXfNNr6', 'APPROVER',  1, 'Male',   '2026-01-19 04:49:52'),
(14, 'Robert',  'Dalisay',  'Dagooc',  'requestor', 'requestor@gmail.com',      '$2y$10$kfQboqF65.MMMZW1LLbnne2iPdRNxGNSEQ9PkgZbqNY8Hvu1I0fRG', 'REQUESTOR', 1, 'Male',   '2026-01-19 05:06:58'),
(27, 'Robert',  'Dalisay',  '',        'finance',   'finance@gmail.com',        '$2y$10$bZnJU7CRv8z/t98tUMqBY.///rnHVaJh5l.pB/m6WZnFDJN6Orey.', 'FINANCE',   1, 'Male',   '2026-01-26 14:20:46'),
(29, 'Buyer',   'Account',  '',        'buyer',     'buyer@gmail.com',          '$2y$10$oBbv.QmVNrkbysq75488m.RxUDeSzNou2cGYYW3yvjv/KczjqIoc2',  'BUYER',     1, 'Male',   '2026-01-31 06:25:06'),
(30, 'Kenneth', 'Jolloso',  '',        'kenneth',   'kennethjolloso@gmail.com', '$2y$10$7lXHWtxj0s9rlRDsUltKweZKCyquaHXq1VaywGmQGyT8IhrH.MkM6', 'REQUESTOR', 1, 'Male',   '2026-02-02 16:03:54'),
(31, 'Dane',    'Dalisay',  'L.',      'dane',      'dane.rohan1111@gmail.com', '$2y$10$e2Hv5BnJZW4CkIOeu.uOHOIi8UcV16dvLX7Nx8eCa1q.Wopd5Dfdu', 'REQUESTOR', 1, 'Male',   '2026-02-02 17:22:31');

-- ============================================================
-- TABLE: company_budget
-- ============================================================
CREATE TABLE IF NOT EXISTS `company_budget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_available` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=2;

INSERT INTO `company_budget` VALUES (1, 5000000.00, '2026-01-26 14:56:11', 1);

-- ============================================================
-- TABLE: finance_budget
-- ============================================================
CREATE TABLE IF NOT EXISTS `finance_budget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `allocated_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `spent_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=2;

INSERT INTO `finance_budget` VALUES (1, 15000.00, 0.00, 1737.49, 13262.51, '2026-02-03 05:48:41', 27);

-- ============================================================
-- TABLE: budget_transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS `budget_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` enum('add','deduct','allocate','spend','adjust','refund','withdrawal') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `department` (`department`),
  KEY `performed_by` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=17;

INSERT INTO `budget_transactions` (`id`, `transaction_type`, `amount`, `department`, `description`, `performed_by`, `created_at`) VALUES
(1,  'add',    10000.00, NULL, 'for Spare Parts',           1,  '2026-01-26 13:08:43'),
(2,  'deduct',  5000.00, NULL, 'Misinput',                  1,  '2026-01-26 13:28:07'),
(3,  'spend',    478.00, NULL, 'Approved PR #43',           27, '2026-01-29 15:06:15'),
(4,  'spend',      3.84, NULL, 'Approved PR #21',           27, '2026-01-29 15:12:06'),
(5,  'spend',      3.84, NULL, 'Approved PR #21',           27, '2026-01-29 15:13:57'),
(6,  'spend',      3.84, NULL, 'Approved PR #21',           27, '2026-01-29 15:14:18'),
(7,  'spend',      8.40, NULL, 'Approved PR #19',           27, '2026-01-29 15:20:06'),
(8,  'spend',      0.20, NULL, 'Approved PR #497',          27, '2026-01-31 14:26:00'),
(9,  'add',     5000.00, NULL, 'Test',                      1,  '2026-01-31 18:55:34'),
(10, 'add',     5000.00, NULL, 'Test',                      1,  '2026-01-31 18:59:45'),
(11, 'spend',      4.00, NULL, 'Approved PR #51',           27, '2026-02-01 19:48:42'),
(12, 'spend',    231.00, NULL, 'Approved PR #505',          27, '2026-02-02 18:46:55'),
(13, 'spend',    140.00, NULL, 'Approved PR #506',          27, '2026-02-03 04:38:08'),
(14, 'spend',     98.37, NULL, 'Approved PR #507',          27, '2026-02-03 05:08:18'),
(15, 'spend',    182.00, NULL, 'Approved PR #508',          27, '2026-02-03 05:48:18'),
(16, 'spend',    106.00, NULL, 'Approved PR #498',          27, '2026-02-03 05:48:41');

-- ============================================================
-- TABLE: department_budgets  (MRO — Maintenance, Repair, Operations)
-- ============================================================
CREATE TABLE IF NOT EXISTS `department_budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `spent_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_name` (`department_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=4;

INSERT IGNORE INTO `department_budgets` (`department_name`) VALUES
  ('Maintenance'), ('Repair'), ('Operations');

-- ============================================================
-- TABLE: purchase_requests
-- ============================================================
CREATE TABLE IF NOT EXISTS `purchase_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `pr_number` varchar(20) NOT NULL DEFAULT '',
  `requestor_name` varchar(120) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `dept_budget_id` int(11) DEFAULT NULL,
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
  `withdrawal_status` enum('none','requested','approved','rejected') DEFAULT 'none',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` varchar(120) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `finance_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `finance_approved_by` int(11) DEFAULT NULL,
  `finance_approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pr_number` (`pr_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=510;

INSERT INTO `purchase_requests` (`id`, `user_id`, `pr_number`, `requestor_name`, `request_date`, `category`, `mpn`, `manufacturer`, `quantity`, `unit_price`, `total_amount`, `currency`, `reason`, `notes`, `urgency`, `required_by`, `distributor`, `selected_distributor_text`, `status`, `buyer_status`, `rejection_reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `finance_status`, `finance_approved_by`, `finance_approved_at`) VALUES
(14, 0, 'PR-2026-0001', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99LT1G', NULL, 2213213, 0.0212, 46920.12, 'USD', 'HEAGA', 'HAHAWHAW', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-17 12:30:51', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(15, 0, 'PR-2026-0002', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99-7-F', NULL, 321321, 0.0192, 6169.36, 'USD', 'DASDSADS', 'DSADSA', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0192 @ 3000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-17 12:40:20', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(16, 0, 'PR-2026-0003', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAT54SWFILM', NULL, 2024, 0.0525, 106.26, 'USD', 'Test History', 'Hello', 'Normal', '2026-02-24', 'Digi-Key', 'Digi-Key — $0.0525 @ 3000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-17 13:50:53', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(17, 0, 'PR-2026-0004', 'Current User', '2026-01-17 00:00:00', 'Spare Parts', 'BAV99LT1G', NULL, 42131, 0.0212, 893.18, 'USD', 'Test', 'TEst', 'Normal', '2026-02-22', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-17 13:58:16', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(18, 0, 'PR-2026-0005', 'Current User', '2026-01-18 00:00:00', 'Spare Parts', 'BAV99LT1G', NULL, 100, 0.0212, 2.12, 'USD', 'TEst', 'TEst', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-18 06:16:51', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(19, 0, 'PR-2026-0006', 'Current User', '2026-01-18 00:00:00', 'Spare Parts', 'ESDALC5-1BM2', 'STMicroelectronics', 200, 0.0420, 8.40, 'USD', 'SPare', 'Spare', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0420 @ 12000 units', 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-18 06:19:54', '2026-02-03 13:34:13', 'approved', 27, '2026-01-29 23:20:06'),
(20, 0, 'PR-2026-0007', 'Current User', '2026-01-19 00:00:00', 'Spare Parts', 'BAV99-13-F', 'Diodes Incorporated', 200, 0.0162, 3.24, 'USD', 'Testing', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0162 @ 10000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-19 11:33:30', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(21, 0, 'PR-2026-0008', 'Current User', '2026-01-22 00:00:00', 'Components', 'BAV99-7-F', 'Diodes Incorporated', 200, 0.0192, 3.84, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0192 @ 3000 units', 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-22 04:46:05', '2026-02-03 13:34:13', 'approved', 27, '2026-01-29 23:14:18'),
(22, 0, 'PR-2026-0009', 'Current User', '2026-01-23 00:00:00', 'Components', 'AL-06-18-0-C', 'Advanced Cable Ties, Inc.', 2000, 0.0355, 71.00, 'USD', 'Test', '', 'Normal', NULL, 'Digi-Key', 'Digi-Key — $0.0355 @ 100 units', 'rejected', 'pending_payment', NULL, NULL, NULL, '2026-01-23 11:11:05', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(40, 14, 'PR-2026-0010', 'Robert Dagooc Dalisay', '2026-01-23 00:00:00', 'Components', 'SBP100143WE5', 'TE Connectivity Raychem Cable Protection', 32138921, 4.2288, 135909069.12, 'PHP', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0745 @ 5000 units', 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-23 15:04:54', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(41, 14, 'PR-2026-0011', 'Robert Dagooc Dalisay', '2026-01-24 00:00:00', 'Spare Parts', 'AL-07-50-0-M', 'Advanced Cable Ties, Inc.', 3213213, 1.7858, 5738155.78, 'PHP', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0314 @ 1000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-24 14:57:55', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(42, 14, 'PR-2026-0012', 'Robert Dagooc Dalisay', '2026-01-27 00:00:00', 'Components', 'BAV99W,135', 'Nexperia USA Inc.', 123123, 0.0209, 2573.27, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0209 @ 10000 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-26 23:49:34', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(43, 14, 'PR-2026-0013', 'Robert Dagooc Dalisay', '2026-01-29 00:00:00', 'Spare Parts', 'NOZZLE-GREEN', '3M', 100, 4.7800, 478.00, 'USD', 'Spare parts', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $4.7800 @ 1 units', 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-28 16:45:57', '2026-02-03 13:34:13', 'approved', 27, '2026-01-29 23:06:14'),
(76, 14, 'PR-2026-0014', 'Robert Dagooc Dalisay', '2026-01-30 00:00:00', 'Spare Parts', '1N5819', 'STMicroelectronics', 1000, 0.1300, 130.00, 'USD', 'Testing', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.1300 @ 1 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-29 17:32:12', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(496, 14, 'PR-2026-0015', 'Robert Dagooc Dalisay', '2026-01-31 00:00:00', 'Spare Parts', 'LM358DR', 'Texas Instruments', 100, 0.0884, 8.84, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0884 @ 2500 units', 'PENDING', 'pending_payment', NULL, NULL, NULL, '2026-01-30 19:02:42', '2026-02-03 13:34:13', 'pending', NULL, NULL),
(497, 14, 'PR-2026-0016', 'Robert Dagooc Dalisay', '2026-01-31 00:00:00', 'Spare Parts', 'RC0603FR-0710KL', 'YAGEO', 100, 0.0020, 0.20, 'USD', 'Testing', '', 'High', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0020 @ 5000 units', 'approved', 'pending_payment', NULL, NULL, NULL, '2026-01-31 12:24:52', '2026-02-03 13:34:13', 'approved', 27, '2026-01-31 22:26:00'),
(498, 30, 'PR-2026-0017', 'Kenneth Jolloso', '2026-02-03 00:00:00', 'Spare Parts', 'BAV99LT1G', 'onsemi', 5000, 0.0212, 106.00, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0212 @ 3000 units', 'approved', 'pending_payment', NULL, '10', '2026-02-03 02:50:07', '2026-02-02 17:20:13', '2026-02-03 13:34:13', 'approved', 27, '2026-02-03 13:48:41'),
(508, 31, 'PR-2026-0018', 'Dane L. Dalisay', '2026-02-03 00:00:00', 'Spare Parts', 'BAR43SFILM', 'STMicroelectronics', 5000, 0.0364, 182.00, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0364 @ 3000 units', 'approved', 'purchased', NULL, '10', '2026-02-03 13:47:49', '2026-02-03 05:47:29', '2026-02-03 13:34:13', 'approved', 27, '2026-02-03 13:48:18'),
(509, 31, 'PR-2026-0019', 'Dane L. Dalisay', '2026-02-03 00:00:00', 'Spare Parts', 'MP-2016-1100-50-70', 'Luminus Devices Inc.', 5000, 0.0248, 124.00, 'USD', 'Test', '', 'Normal', '2026-02-14', 'Digi-Key', 'Digi-Key — $0.0248 @ 5000 units', 'approved', 'pending_payment', NULL, '10', '2026-02-03 13:59:49', '2026-02-03 05:59:27', '2026-02-03 13:34:13', 'pending', NULL, NULL);

-- Historical price prediction demo data
INSERT INTO `purchase_requests` (`id`, `user_id`, `pr_number`, `requestor_name`, `request_date`, `category`, `mpn`, `quantity`, `unit_price`, `total_amount`, `currency`, `reason`, `status`, `buyer_status`, `created_at`, `updated_at`, `finance_status`) VALUES
(46, 1, 'DEMO-RES-202508-001', 'Historical User', '2025-08-15 12:00:00', 'Components', 'RESISTOR-10K', 100, 0.0500, 5.00, 'USD', 'Historical data', 'approved', 'pending_payment', '2025-08-15 04:00:00', '2026-02-03 13:34:13', 'pending'),
(47, 1, 'DEMO-RES-202509-001', 'Historical User', '2025-09-15 12:00:00', 'Components', 'RESISTOR-10K', 100, 0.0480, 4.80, 'USD', 'Historical data', 'approved', 'pending_payment', '2025-09-15 04:00:00', '2026-02-03 13:34:13', 'pending'),
(48, 1, 'DEMO-RES-202510-001', 'Historical User', '2025-10-15 12:00:00', 'Components', 'RESISTOR-10K', 100, 0.0460, 4.60, 'USD', 'Historical data', 'approved', 'pending_payment', '2025-10-15 04:00:00', '2026-02-03 13:34:13', 'pending'),
(49, 1, 'DEMO-RES-202511-001', 'Historical User', '2025-11-15 12:00:00', 'Components', 'RESISTOR-10K', 100, 0.0440, 4.40, 'USD', 'Historical data', 'approved', 'pending_payment', '2025-11-15 04:00:00', '2026-02-03 13:34:13', 'pending'),
(50, 1, 'DEMO-RES-202512-001', 'Historical User', '2025-12-15 12:00:00', 'Components', 'RESISTOR-10K', 100, 0.0420, 4.20, 'USD', 'Historical data', 'approved', 'pending_payment', '2025-12-15 04:00:00', '2026-02-03 13:34:13', 'pending'),
(51, 1, 'DEMO-RES-202601-001', 'Historical User', '2026-01-15 12:00:00', 'Components', 'RESISTOR-10K', 100, 0.0400, 4.00, 'USD', 'Historical data', 'approved', 'pending_payment', '2026-01-15 04:00:00', '2026-02-03 13:34:13', 'approved');

-- ============================================================
-- TABLE: purchase_request_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `purchase_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `mpn` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,4) DEFAULT 0.0000,
  `total_price` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  CONSTRAINT `purchase_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLE: finance_approvals
-- ============================================================
CREATE TABLE IF NOT EXISTS `finance_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_id` int(11) NOT NULL,
  `pr_number` varchar(50) NOT NULL,
  `requestor_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL DEFAULT '',
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `finance_approved_by` int(11) DEFAULT NULL,
  `finance_approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pr_id` (`pr_id`),
  KEY `status` (`status`),
  CONSTRAINT `finance_approvals_ibfk_1` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=9;

INSERT INTO `finance_approvals` (`id`, `pr_id`, `pr_number`, `requestor_name`, `department`, `total_amount`, `status`, `finance_approved_by`, `finance_approved_at`, `created_at`) VALUES
(1, 19,  'PR-2026-0006', 'Current User',         '', 8.40,   'approved', 27, '2026-01-29 15:20:06', '2026-01-29 15:20:06'),
(2, 497, 'PR-2026-0016', 'Robert Dagooc Dalisay', '', 0.20,  'approved', 27, '2026-01-31 14:26:00', '2026-01-31 14:26:00'),
(3, 51,  'DEMO-RES-202601-001', 'Historical User','', 4.00,  'approved', 27, '2026-02-01 19:48:42', '2026-02-01 19:48:42'),
(7, 508, 'PR-2026-0018', 'Dane L. Dalisay',       '', 182.00,'approved', 27, '2026-02-03 05:48:18', '2026-02-03 05:48:18'),
(8, 498, 'PR-2026-0017', 'Kenneth Jolloso',       '', 106.00,'approved', 27, '2026-02-03 05:48:41', '2026-02-03 05:48:41');

-- ============================================================
-- TABLE: suppliers
-- ============================================================
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `contact` varchar(120) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLE: supplier_bids
-- ============================================================
CREATE TABLE IF NOT EXISTS `supplier_bids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `unit_price` decimal(12,4) NOT NULL,
  `delivery_date` date NOT NULL,
  `alloc_qty` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pr_supplier` (`pr_id`, `supplier_id`),
  KEY `fk_bid_pr` (`pr_id`),
  KEY `fk_bid_sup` (`supplier_id`),
  CONSTRAINT `fk_bid_pr` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bid_sup` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLE: purchase_orders
-- ============================================================
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(20) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `pr_number` varchar(20) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,4) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `delivery_date` date NOT NULL,
  `status` enum('Issued','Received','Cancelled') DEFAULT 'Issued',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_po_number` (`po_number`),
  KEY `fk_po_pr` (`pr_id`),
  KEY `fk_po_sup` (`supplier_id`),
  CONSTRAINT `fk_po_pr` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_po_sup` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLE: pr_withdrawals
-- (withdrawal_type: pre_po = cancel before purchase,
--                   post_purchase = refund after purchase)
-- ============================================================
CREATE TABLE IF NOT EXISTS `pr_withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_id` int(11) NOT NULL,
  `pr_number` varchar(20) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `requested_by_name` varchar(120) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `reason` text NOT NULL,
  `withdrawal_type` enum('pre_po','post_purchase') NOT NULL DEFAULT 'post_purchase',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_by_name` varchar(120) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_wd_pr` (`pr_id`),
  CONSTRAINT `fk_wd_pr` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Done
-- ============================================================
COMMIT;

-- ============================================================
-- LOGIN CREDENTIALS (for reference)
-- ============================================================
-- Username: admin       Password: admin123     Role: ADMIN
-- Username: approver    Password: approver123  Role: APPROVER
-- Username: requestor   Password: requestor123 Role: REQUESTOR
-- Username: finance     Password: finance123   Role: FINANCE
-- Username: buyer       Password: buyer123     Role: BUYER
-- Username: kenneth     Password: kenneth123   Role: REQUESTOR
-- Username: dane        Password: dane123      Role: REQUESTOR
-- ============================================================
