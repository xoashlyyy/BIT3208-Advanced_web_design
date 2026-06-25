CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Users table with role column
CREATE TABLE IF NOT EXISTS users (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('super_admin','manager','stock_clerk','viewer') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inventory table
CREATE TABLE IF NOT EXISTS products (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(100)   NOT NULL,
    sku          VARCHAR(50)    NOT NULL UNIQUE,
    quantity     INT            NOT NULL DEFAULT 0,
    price        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------------
-- Default accounts  |  Password for ALL accounts: password123
-- Hash generated with: password_hash('password123', PASSWORD_DEFAULT)
-- ----------------------------------------------------------------
INSERT INTO users (username, password, role) VALUES
('admin',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'),
('manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager'),
('clerk1',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stock_clerk'),
('viewer1',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer');

-- Sample inventory
INSERT INTO products (product_name, sku, quantity, price) VALUES
('MacBook Pro M3',       'APP-MBP-M3',  15, 1999.00),
('Dell XPS 15',          'DELL-XPS-15',  4, 1500.00),
('Keychron K2 Keyboard', 'KEY-K2-V2',   42,   79.99),
('Logitech MX Master 3', 'LOGI-MX-3',    0,   99.99);