<?php
session_start();

// Already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require 'includes/db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Check for duplicate username
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "That username is already taken. Please choose another.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Self-registered accounts always start as viewer; Super Admin promotes them
            $stmt   = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'viewer')");
            $stmt->bind_param("ss", $username, $hashed);

            if ($stmt->execute()) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $stmt->insert_id;
                $_SESSION['user_name'] = $username;
                $_SESSION['user_role'] = 'viewer';
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed due to a system error. Please try again.";
            }
        }
    }
}

$prefill = htmlspecialchars($_GET['email_forward'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p style="text-align:center; color:var(--text-muted); margin-top:-10px; margin-bottom:20px; font-size:0.875rem;">
                New accounts start as <strong style="color:var(--text-main);">Viewer</strong>.
                A Super Admin can promote your role after you sign in.
            </p>

            <?php if ($error): ?>
                <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="authForm" method="POST" action="">
                <label for="reg_username">Username</label>
                <input type="text" id="reg_username" name="username"
                       value="<?php echo $prefill; ?>" required autofocus>

                <label for="reg_password">Password</label>
                <input type="password" id="reg_password" name="password" id="password" required>
                <div id="pwdHelper" style="font-size:0.8rem; margin-top:-15px; margin-bottom:15px;"></div>

                <button type="submit">Complete Setup</button>
            </form>

            <p style="text-align:center; margin-top:24px; font-size:0.875rem; color:var(--text-muted);">
                Already have an account? <a href="login.php">Sign in instead</a>
            </p>
        </div>
    </div>
    <script src="js/app.js"></script>
</body>
</html>