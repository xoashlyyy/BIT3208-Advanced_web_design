<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $new_username = trim(htmlspecialchars($_POST['username']));
    
    $update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_username, $id);
    
    if ($update_stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Failed to update. Username may be taken.";
    }
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User | Student Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Edit User Record</h2>
            <?php if($error) echo "<div class='msg error'>$error</div>"; ?>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $user_data['id']; ?>">
                
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                
                <button type="submit">Update Record</button>
            </form>
            <p style="text-align: center; margin-top: 24px; font-size: 0.875rem;">
                <a href="dashboard.php" style="color: var(--text-muted);">Cancel and Return</a>
            </p>
        </div>
    </div>
</body>
</html>