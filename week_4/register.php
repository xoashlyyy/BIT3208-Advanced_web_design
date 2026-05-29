<?php
session_start();
require 'includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim(htmlspecialchars($_POST['username']));
    $pass = $_POST['password'];
    
    if (strlen($pass) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        // Hash the password for security
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $user, $hashed_pass);
        
        if ($stmt->execute()) {
            $message = "Registration successful! <a href='login.php'>Login here</a>.";
        } else {
            $message = "Error: Username might already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Week 4 - Register</title>
    <link rel="stylesheet" href="css/style.css">
    <style>body{background:#121212; color:#fff; font-family:sans-serif;} .msg{color:#4CAF50;}</style>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        <?php if($message) echo "<p class='msg'>$message</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password (Min 8 chars)" required><br><br>
            <button type="submit">Register</button>
        </form>
        <p><a href="index.php" style="color:var(--primary);">Back to Home</a></p>
    </div>
</body>
</html>