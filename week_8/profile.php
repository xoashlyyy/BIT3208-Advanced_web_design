<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_login(); // Ensure user is logged in to view their profile
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | StockTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-base: #0f172a;
            --bg-surface: #1e293b;
            --primary: #6366f1;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255,255,255,0.08);
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background: var(--bg-base); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }

        /* Flexbox Layout Container */
        .profile-container {
            display: flex;
            flex-direction: column; /* Mobile first: stack items vertically */
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            max-width: 800px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* Profile Left - Media Placement Section */
        .profile-image-section {
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #312e81, #1e1b4b);
            padding: 40px;
            flex: 1;
        }
        .profile-img {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 4px solid var(--primary);
            object-fit: cover;
            max-width: 100%; /* Responsive image constraint requirement */
            display: block;
        }

        /* Profile Right - Details Section */
        .profile-details-section {
            padding: 40px;
            flex: 1.5;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .role-badge {
            align-self: flex-start;
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        h1 { font-size: 1.8rem; margin-bottom: 16px; font-weight: 800; letter-spacing: -0.5px; }
        p.about-text { color: var(--text-muted); line-height: 1.6; margin-bottom: 24px; font-size: 0.95rem; }
        
        .contact-info {
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .contact-item i { color: var(--primary); width: 16px; text-align: center; }
        .back-link { display: inline-block; margin-top: 24px; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; }
        .back-link:hover { color: #fff; }

        /* Desktop View Breakpoint Modification via Media Queries */
        @media (min-width: 768px) {
            .profile-container {
                flex-direction: row; /* Switch orientation side-by-side */
            }
            .profile-image-section {
                padding: 60px;
            }
            .profile-img {
                width: 200px;
                height: 200px;
            }
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <div class="profile-image-section">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80" alt="Profile Picture" class="profile-img">
        </div>

        <div class="profile-details-section">
            <span class="role-badge"><?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['user_role'])); ?></span>
            <h1>Ashley Joy Onyango</h1>
            
            <p class="about-text">
                Computer Science student specializing in database administration, server-side system deployments, and security architectures. Currently managing the core inventory data records and system integration access tiers for the StockTrack system portal.
            </p>

            <div class="contact-info">
                <div class="contact-item">
                    <i class="fa-solid fa-user"></i>
                    <span>Username: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>ashley.joy@example.com</span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Nairobi, Kenya</span>
                </div>
            </div>

            <a href="dashboard.php" class="back-link">&larr; Return to Workspace Dashboard</a>
        </div>
    </div>

</body>
</html>