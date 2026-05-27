CREATE DATABASE `inventory_db`;
USE `inventory_db`;

CREATE TABLE IF NOT EXISTS `items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `items` (`id`, `user_id`, `item_name`, `description`, `created_at`)
VALUES (1, 1, 'Milk', 'Brookside', '2025-05-21 20:11:51');