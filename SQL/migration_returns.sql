-- ============================================================
-- Migration: Item Returns / Defective Items
-- Run once in phpMyAdmin → ze_electronic → SQL tab
-- ============================================================

CREATE TABLE IF NOT EXISTS `po_returns` (
  `id`               int(11)       NOT NULL AUTO_INCREMENT,
  `po_id`            int(11)       NOT NULL,
  `po_number`        varchar(20)   NOT NULL,
  `pr_number`        varchar(20)   NOT NULL,
  `supplier_id`      int(11)       NOT NULL,
  `supplier_name`    varchar(150)  NOT NULL,
  `quantity_returned` int(11)      NOT NULL DEFAULT 1,
  `reason`           enum('defective','wrong_item','damaged_shipping','overdelivery','other') NOT NULL DEFAULT 'defective',
  `description`      text          NOT NULL,
  `status`           enum('pending','approved','rejected','returned') NOT NULL DEFAULT 'pending',
  `requested_by`     int(11)       NOT NULL,
  `requested_by_name` varchar(120) NOT NULL,
  `reviewed_by`      int(11)       DEFAULT NULL,
  `reviewed_by_name` varchar(120)  DEFAULT NULL,
  `reviewed_at`      datetime      DEFAULT NULL,
  `admin_notes`      text          DEFAULT NULL,
  `created_at`       timestamp     NOT NULL DEFAULT current_timestamp(),
  `updated_at`       timestamp     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_return_po` (`po_id`),
  CONSTRAINT `fk_return_po` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add return_status column to purchase_orders for quick visibility
ALTER TABLE `purchase_orders`
  ADD COLUMN IF NOT EXISTS `return_status` enum('none','requested','approved','rejected','returned') NOT NULL DEFAULT 'none' AFTER `status`;

-- Extend activity_logs for return events
ALTER TABLE `activity_logs`
  MODIFY COLUMN `activity_type` enum(
    'user_added','user_edited','user_deleted',
    'request_approved','request_rejected','request_created',
    'login','logout','purchase',
    'budget_allocated','budget_adjusted','budget_insufficient',
    'withdrawal_requested','withdrawal_approved','withdrawal_rejected',
    'request_finance_approved','request_finance_rejected',
    'po_created','po_updated',
    'return_requested','return_approved','return_rejected','return_completed'
  ) NOT NULL;
