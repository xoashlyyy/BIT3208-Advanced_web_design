<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("localhost", "root", "", "inventory_db");
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection failed. Please ensure MySQL is running.");
}
?>