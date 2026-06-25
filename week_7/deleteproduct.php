<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

require_role('manager');

// Only accept POST to prevent deletion via crafted URL (CSRF protection)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Product deleted.";
        } else {
            $_SESSION['flash_error'] = "Failed to delete product.";
        }
    }
}

header("Location: dashboard.php");
exit();
?>