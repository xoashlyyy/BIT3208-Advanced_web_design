<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("localhost", "root", "", "week3db");
} catch (Exception $e) {
    die("Database connection failed.");
}
?>