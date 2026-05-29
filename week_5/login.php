<?php
session_start();
require 'includes/db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['username'];
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
    <title>Login | Student Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Sign in to Portal</h2>
            <?php if($error) echo "<div class='msg error'>$error</div>"; ?>
            
            <form method="POST" action="">
                <label>Username</label>
                <input type="text" name="username" required>
                
                <label>Password</label>
                <input type="password" name="password" required>
                
                <button type="submit">Sign In</button>
            </form>
            <p style="text-align: center; margin-top: 24px; font-size: 0.875rem; color: var(--text-muted);">
                Need access? <a href="register.php">Create an account</a>
            </p>
        </div>
    </div>
</body>
</html>