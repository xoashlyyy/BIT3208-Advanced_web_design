<?php
// Strict session initialization
session_set_cookie_params(['lifetime' => 3600, 'path' => '/', 'httponly' => true]);
session_start();
require 'includes/db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim(htmlspecialchars($_POST['username']));
    $pass = $_POST['password'];
    
    // Validate against the database
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Assuming plain text for your test user 'testuser'[cite: 9], but password_verify() is standard
        if ($pass === $row['password'] || password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $row['id'];
            session_regenerate_id(true); 
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Week 4 - Login</title>
    <style>body{background:#121212; color:#fff; font-family:sans-serif;} .err{color:#cf6679;}</style>
</head>
<body>
    <h2>System Login</h2>
    <?php if($error) echo "<p class='err'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>