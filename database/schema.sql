CREATE DATABASE IF NOT EXISTS `leave_rms_db`;
USE `leave_rms_db`;

-- Drop existing tables (order matters due to FKs)
DROP TABLE IF EXISTS `dining_menu`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `holidays_days_off`;
DROP TABLE IF EXISTS `platforms`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

-- Users
CREATE TABLE `users` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(30) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(50),
    `role` ENUM('instructor','student') DEFAULT 'instructor',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins
CREATE TABLE `admins` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(30) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(50),
    `role` ENUM('super_admin', 'admin') DEFAULT 'admin',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Platforms
CREATE TABLE `platforms` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `url` VARCHAR(255) NOT NULL,
    `notifications_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Announcements
CREATE TABLE `announcements` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `author_id` INT(6) UNSIGNED NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_announcements_author` FOREIGN KEY (`author_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dining Menu
CREATE TABLE `dining_menu` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL UNIQUE,
    `day_of_week` VARCHAR(20) NOT NULL,
    `breakfast_menu` TEXT,
    `breakfast_start_time` TIME,
    `breakfast_end_time` TIME,
    `lunch_menu` TEXT,
    `lunch_start_time` TIME,
    `lunch_end_time` TIME,
    `is_recurring` BOOLEAN DEFAULT FALSE,
    `created_by` INT(6) UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_dining_menu_created_by` FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    INDEX `idx_day_of_week` (`day_of_week`),
    INDEX `idx_is_recurring` (`is_recurring`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Holidays / Days Off
CREATE TABLE `holidays_days_off` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL UNIQUE,
    `year` INT(4) NOT NULL,
    `day_of_week` VARCHAR(20) NOT NULL,
    `holiday_name` VARCHAR(255) NOT NULL,
    `type` ENUM('holiday', 'weekend', 'closure', 'custom') NOT NULL DEFAULT 'holiday',
    `description` TEXT,
    `is_recurring` BOOLEAN DEFAULT FALSE,
    `created_by` INT(6) UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_holidays_created_by` FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL,
    INDEX `idx_year` (`year`),
    INDEX `idx_date` (`date`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




