-- init_db.sql
-- Run this script in phpMyAdmin or MySQL CLI to create the database and table.

CREATE DATABASE IF NOT EXISTS `employee_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `employee_db`;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL
) ENGINE=InnoDB;

-- Departments table
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `manager_id` INT DEFAULT NULL,
  `budget` DECIMAL(12,2) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Positions table
CREATE TABLE IF NOT EXISTS `positions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `department_id` INT DEFAULT NULL,
  `level` ENUM('entry', 'junior', 'senior', 'lead', 'manager', 'director', 'executive') DEFAULT 'entry',
  `min_salary` DECIMAL(10,2) DEFAULT NULL,
  `max_salary` DECIMAL(10,2) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Employees table (enhanced)
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `employee_id` VARCHAR(20) NOT NULL UNIQUE,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(50) DEFAULT NULL,
  `mobile` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
  `department_id` INT DEFAULT NULL,
  `position_id` INT DEFAULT NULL,
  `salary` DECIMAL(10,2) DEFAULT NULL,
  `hire_date` DATE DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Attendance table
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `check_in` TIME DEFAULT NULL,
  `check_out` TIME DEFAULT NULL,
  `status` ENUM('present', 'absent', 'late', 'half_day', 'leave') DEFAULT 'present',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_attendance` (`employee_id`, `date`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Leave requests table
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `leave_type` ENUM('annual', 'sick', 'maternity', 'paternity', 'emergency', 'unpaid') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `days_requested` DECIMAL(4,1) NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
  `approved_by` INT DEFAULT NULL,
  `approved_at` TIMESTAMP NULL,
  `comments` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Performance reviews table
CREATE TABLE IF NOT EXISTS `performance_reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `reviewer_id` INT NOT NULL,
  `review_period` VARCHAR(50) NOT NULL,
  `rating` DECIMAL(3,1) CHECK (`rating` >= 1 AND `rating` <= 5),
  `goals` TEXT DEFAULT NULL,
  `achievements` TEXT DEFAULT NULL,
  `areas_for_improvement` TEXT DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `status` ENUM('draft', 'submitted', 'reviewed') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Salary history table
CREATE TABLE IF NOT EXISTS `salary_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `old_salary` DECIMAL(10,2) DEFAULT NULL,
  `new_salary` DECIMAL(10,2) NOT NULL,
  `effective_date` DATE NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Audit logs table
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(50) NOT NULL,
  `record_id` INT DEFAULT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default admin user
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- Insert sample departments
INSERT INTO `departments` (`name`, `description`) VALUES
('Human Resources', 'Manages employee relations and organizational development'),
('Information Technology', 'Handles technology infrastructure and software development'),
('Sales', 'Manages sales operations and customer relationships'),
('Marketing', 'Handles marketing campaigns and brand management'),
('Finance', 'Manages financial operations and accounting'),
('Operations', 'Oversees day-to-day business operations')
ON DUPLICATE KEY UPDATE `name` = `name`;

-- Insert sample positions
INSERT INTO `positions` (`title`, `description`, `level`, `min_salary`, `max_salary`) VALUES
('Software Developer', 'Develops and maintains software applications', 'junior', 50000, 80000),
('Senior Developer', 'Leads development projects and mentors junior developers', 'senior', 80000, 120000),
('Project Manager', 'Manages project timelines and team coordination', 'manager', 70000, 100000),
('HR Manager', 'Oversees human resources operations', 'manager', 60000, 90000),
('Sales Representative', 'Handles sales and customer acquisition', 'junior', 40000, 70000),
('Marketing Specialist', 'Creates and executes marketing campaigns', 'junior', 45000, 75000),
('Accountant', 'Manages financial records and reporting', 'junior', 50000, 80000),
('System Administrator', 'Maintains IT infrastructure and systems', 'senior', 60000, 95000)
ON DUPLICATE KEY UPDATE `title` = `title`;

-- Insert sample employees
INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `position_id`, `salary`, `hire_date`, `status`) VALUES
('EMP001', 'John', 'Doe', 'john.doe@company.com', '+1-555-0101', 2, 1, 65000, '2023-01-15', 'active'),
('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+1-555-0102', 2, 2, 95000, '2022-03-20', 'active'),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '+1-555-0103', 3, 5, 55000, '2023-06-10', 'active'),
('EMP004', 'Sarah', 'Williams', 'sarah.williams@company.com', '+1-555-0104', 1, 4, 75000, '2021-11-05', 'active'),
('EMP005', 'David', 'Brown', 'david.brown@company.com', '+1-555-0105', 5, 7, 60000, '2023-02-28', 'active')
ON DUPLICATE KEY UPDATE `employee_id` = `employee_id`;
