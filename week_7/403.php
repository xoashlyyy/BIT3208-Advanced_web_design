<?php
session_start();

// Self-contained role map — no dependency on auth.php
$_role_map = [
    'super_admin' => 'Super Admin',
    'manager'     => 'Manager',
    'stock_clerk' => 'Stock Clerk',
    'viewer'      => 'Viewer / Auditor',
];
$_role_label = $_role_map[$_SESSION['user_role'] ?? ''] ?? 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; }
        .box { background:var(--bg-surface); border:1px solid var(--border); border-radius:12px;
               padding:40px; max-width:460px; width:100%; text-align:center; }
        a.btn { display:inline-block; background:var(--primary); color:#fff;
                padding:10px 24px; border-radius:8px; font-weight:500; text-decoration:none; }
        a.btn:hover { background:var(--primary-hover); opacity:1; }
    </style>
</head>
<body>
    <div class="box">
        <div style="font-size:3rem; margin-bottom:16px;">🔒</div>
        <h2 style="margin-bottom:12px;">Access Denied</h2>
        <p style="color:var(--text-muted); margin-bottom:28px; line-height:1.6;">
            Your role (<strong style="color:var(--text-main);"><?php echo htmlspecialchars($_role_label); ?></strong>)
            does not have permission to perform this action.<br>
            Contact your Super Admin if you need elevated access.
        </p>
        <a href="dashboard.php" class="btn">&larr; Back to Dashboard</a>
    </div>
</body>
</html>