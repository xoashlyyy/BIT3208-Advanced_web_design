<?php
session_start();
require 'includes/db.php';
$error = "";

// Process Registration Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim(htmlspecialchars($_POST['username']));
    $pass = $_POST['password'];

    // 1. Check if user already exists securely
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "An account with that email/username already exists.";
    } else {
        // 2. Hash Password securely
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        
        // 3. Insert secure record
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $user, $hashed_password);
        
        if ($insert_stmt->execute()) {
            // Auto-login after successful registration
            $_SESSION['user_id'] = $insert_stmt->insert_id;
            $_SESSION['user_name'] = $user;
            session_regenerate_id(true); // Prevent session fixation
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Registration failed due to a system error.";
        }
    }
}

// UX Handoff: Catch the email forwarded from the landing page CTA
$prefill_username = isset($_GET['email_forward']) ? htmlspecialchars($_GET['email_forward']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account | Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Create Account</h2>
            <?php if($error) echo "<div class='msg error'>$error</div>"; ?>
            
            <form id="authForm" method="POST" action="">
                <label>Email or Username</label>
                <input type="text" name="username" value="<?php echo $prefill_username; ?>" required autofocus>
                
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <div id="pwdHelper" style="font-size: 0.8rem; margin-top: -15px; margin-bottom: 15px;"></div>
                
                <button type="submit">Complete Setup</button>
            </form>
            <p style="text-align: center; margin-top: 24px; font-size: 0.875rem; color: var(--text-muted);">
                Already have an account? <a href="login.php">Sign in instead</a>
            </p>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>
</html>