CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Authentication Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inventory Core Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(100) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert a default admin account (Password: admin123)
INSERT INTO users (username, password) 
VALUES ('admin', '$2y$10$y5X31yJ1/G9qQ5Y.A/Zz/u3bE/5O2G5t7iO9x/5m.E/V.A.B.C.D.E');

-- Insert some dummy inventory data for the dashboard
INSERT INTO products (product_name, sku, quantity, price) VALUES 
('MacBook Pro M3', 'APP-MBP-M3', 15, 1999.00),
('Dell XPS 15', 'DELL-XPS-15', 4, 1500.00),
('Keychron K2 Keyboard', 'KEY-K2-V2', 42, 79.99),
('Logitech MX Master 3', 'LOGI-MX-3', 0, 99.99);