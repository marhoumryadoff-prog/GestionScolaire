-- migrations_sql_fixed.sql
-- Safe, non-procedural migration for MariaDB/MySQL.
-- 1) Change the DB name below if your DB is different
CREATE DATABASE IF NOT EXISTS `base_etudiants_tp2_2025` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `base_etudiants_tp2_2025`;

-- 2) Create bulletin_email_history table (audit of sends)
CREATE TABLE IF NOT EXISTS `bulletin_email_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero_etudiant` VARCHAR(50) NULL,
  `email` VARCHAR(255) NOT NULL,
  `sent_by` INT NULL,
  `status` ENUM('queued','sent','failed') NOT NULL,
  `message` TEXT NULL,
  `attachment_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Create email_queue table (simple queue for worker)
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `to_email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` LONGTEXT NOT NULL,
  `attachments` JSON NULL,
  `attempts` INT DEFAULT 0,
  `last_error` TEXT NULL,
  `status` ENUM('pending','processing','done','failed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Add email and email_verified columns to etudiants if they don't exist
-- Note: MariaDB supports "ADD COLUMN IF NOT EXISTS" in many versions. If your server rejects
-- "IF NOT EXISTS", see the fallback instructions below.
ALTER TABLE `etudiants`
  ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `email_verified` TINYINT(1) DEFAULT 0;