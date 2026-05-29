<?php
$status = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    // In Week 4, simply echoing or confirming the form submission is sufficient
    $status = "Thank you, $name. Your message has been received.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Support</title>
    <style>body{background:#121212; color:#fff; font-family:sans-serif;} .success{color:#03dac6;}</style>
</head>
<body>
    <h2>Contact Us</h2>
    <?php if($status) echo "<p class='success'>$status</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Your Name" required><br><br>
        <input type="email" name="email" placeholder="Your Email" required><br><br>
        <textarea name="message" placeholder="How can we help?" required></textarea><br><br>
        <button type="submit">Send Message</button>
    </form>
</body>
</html>