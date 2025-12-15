-- SQL dump for attendance_2 database
-- Stored/created: 2025-12-15

CREATE DATABASE IF NOT EXISTS `attendance_2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `attendance_2`;

-- Users table (covers columns used across the app)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(150) DEFAULT NULL,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL UNIQUE,
  `role` ENUM('admin','employee') NOT NULL DEFAULT 'employee',
  `phone` VARCHAR(30) DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attendance table (uses check_in/check_out datetimes)
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `check_in` DATETIME NOT NULL,
  `check_out` DATETIME DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'present',
  `notes` TEXT DEFAULT NULL,
  `is_manual` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reports table (admin notes about users)
CREATE TABLE IF NOT EXISTS `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `admin_id` INT NOT NULL,
  `note` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee notes table
CREATE TABLE IF NOT EXISTS `employee_notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `note` TEXT NOT NULL,
  `note_date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: sample admin user (password hashed for 'admin123')
-- You can uncomment and adjust the password hash as needed.
-- INSERT INTO `users` (`username`,`password`,`full_name`,`name`,`email`,`role`) VALUES
-- ('admin','$2y$10$abcdefghijklmnopqrstuv', 'System Admin','System Admin','admin@example.com','admin');

-- End of dump
