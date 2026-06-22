-- =========================================================
-- HADERO GOURMET COFFEE DATABASE SCHEMA
-- For local XAMPP phpMyAdmin MySQL Connection
-- =========================================================

-- 1. Create default schema if not exists
CREATE DATABASE IF NOT EXISTS `hadero_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hadero_db`;

-- 2. Create products table
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NOT NULL, -- Joined to categories.name or custom strings
    `price` VARCHAR(50) NOT NULL,
    `image_url` LONGTEXT NULL, -- Supports local paths OR active base64 snapshot strings smoothly
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create categories table for dynamic addition
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) UNIQUE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create admin_settings table for passwords
CREATE TABLE IF NOT EXISTS `admin_settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` VARCHAR(255) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Seed default starter categories
INSERT INTO `categories` (`name`) VALUES
('Coffee'),
('Bakery'),
('Light Bites')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 6. Seed initial Admin password (Default: hadero_admin)
INSERT INTO `admin_settings` (`setting_key`, `setting_value`) VALUES
('admin_password', 'hadero_admin')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);

-- 7. Populate default starting menu items
INSERT INTO `menu_items` (`id`, `name`, `description`, `category`, `price`, `image_url`) VALUES
(1, 'Ethiopian Espresso', 'A bold, syrupy shot with a bright finish and a smooth crema.', 'Coffee', '$3.50', 'images/menu/ethiopian-espresso.jpg'),
(2, 'Cappuccino', 'Espresso, steamed milk, and a cloudlike foam cap.', 'Coffee', '$4.25', 'images/menu/cappuccino.jpg'),
(3, 'Iced Latte', 'Chilled espresso and milk over ice. Clean, cool, and easygoing.', 'Coffee', '$4.75', 'images/menu/iced-latte.jpg'),
(4, 'Flat White', 'Velvety microfoam over a double shot, with a tidy little punch.', 'Coffee', '$4.50', 'images/menu/flat-white.jpg'),
(5, 'Butter Croissant', 'Flaky layers, a golden crust, and a warm buttery finish.', 'Bakery', '$2.95', 'images/menu/butter-croissant.jpg'),
(6, 'Chocolate Muffin', 'Soft crumb, rich cocoa notes, and a cozy sweet center.', 'Bakery', '$3.25', 'images/menu/chocolate-muffin.jpg'),
(7, 'Banana Bread', 'Tender banana loaf with a gently caramelized top.', 'Bakery', '$3.10', 'images/menu/banana-bread.jpg'),
(8, 'Chicken Panini', 'Toasted bread, melted cheese, and a savory fill that travels well.', 'Light Bites', '$6.95', 'images/menu/chicken-panini.jpg'),
(9, 'Veggie Sandwich', 'Fresh vegetables, herbs, and a bright, crunchy bite.', 'Light Bites', '$5.95', 'images/menu/veggie-sandwich.jpg')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
