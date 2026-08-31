-- ============================================================
-- Migration: Supplier Portal
-- Adds login credentials to suppliers table
-- Run once in phpMyAdmin → ze_electronic → SQL tab
-- ============================================================

-- 1. Add login columns to suppliers
ALTER TABLE `suppliers`
  ADD COLUMN IF NOT EXISTS `username` VARCHAR(80) UNIQUE DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `password` VARCHAR(255) DEFAULT NULL AFTER `username`;

-- 2. Add a status column for bid status visibility
ALTER TABLE `supplier_bids`
  ADD COLUMN IF NOT EXISTS `status` ENUM('pending','selected','rejected') NOT NULL DEFAULT 'pending' AFTER `notes`;

-- 3. Index on supplier username for fast login lookup
ALTER TABLE `suppliers`
  ADD INDEX IF NOT EXISTS `idx_supplier_username` (`username`);
