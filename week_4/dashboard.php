<?php
session_start();
if (!isset($_SESSION['user'])) {
    die("Unauthorized Access.");
}
echo "<h1 style='color:white; background:#121212;'>Welcome to your Dashboard, " . htmlspecialchars($_SESSION['user']) . "</h1>";
?>