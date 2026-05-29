<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Optional safeguard: Prevent the logged-in admin from deleting themselves
    if ($id === $_SESSION['user_id']) {
        die("You cannot delete your own active account. <a href='dashboard.php'>Go back</a>");
    }
    
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();
}
header("Location: dashboard.php");
exit();
?>