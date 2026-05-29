<?php
// Author: Ashley Joy
// Secure environment testing with graceful error handling [cite: 326-335]
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$status_html = "";
try {
    $conn = new mysqli("localhost", "root", "", "week1db");
    $conn->set_charset("utf8mb4");
    $status_html = "<div style='color: #03dac6; padding: 15px; border: 1px solid #03dac6; border-radius: 5px;'>Database Connection: Online and Secure.</div>";
} catch (Exception $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    $status_html = "<div style='color: #cf6679; padding: 15px; border: 1px solid #cf6679; border-radius: 5px;'>System Offline: Could not establish a secure database connection.</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Week 1 - Environment Test</title>
    <style>body { background: #121212; color: #fff; font-family: sans-serif; padding: 40px; }</style>
</head>
<body>
    <h2>System Initialization Test</h2>
    <?php echo $status_html; ?>
</body>
</html>