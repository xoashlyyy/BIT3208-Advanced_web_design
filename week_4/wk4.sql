CREATE DATABASE IF NOT EXISTS week4db;
USE week4db;
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);
INSERT INTO users (username, password) VALUES ('testuser', 'password');