<?php
session_start();
require 'week_7/includes/db.php';
require 'week_7/includes/auth.php';
require_login(); // Security boundary check

// Simple mapping to match the clean labels from your 403 error handler
$role_map = [
    'super_admin' => 'Super Admin Master',
    'manager'     => 'Warehouse Manager',
    'stock_clerk' => 'Operational Stock Clerk',
    'viewer'      => 'Viewer / Auditor',
];
$display_role = $role_map[$_SESSION['user_role'] ?? ''] ?? 'System User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | StockTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-base: #08050f;     /* Matching your sleek Super Admin dark look */
            --bg-surface: #0f0a20;
            --border: #2a1d50;
            --primary: #7c3aed;
            --text-main: #e2d9f3;
            --text-muted: #6b5fa0;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background: var(--bg-base); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }

        /* ── FLEXBOX CONTAINER (Mobile First: Columns Stacked) ── */
        .profile-card {
            display: flex;
            flex-direction: column; 
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            max-width: 750px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        /* Flexbox Child 1: Left/Top Section */
        .profile-avatar-pane {
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #130d2a, #1e1340);
            padding: 40px;
            flex: 1;
            border-bottom: 1px solid var(--border);
        }
        .avatar-circle {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #fff;
            font-weight: 700;
            border: 3px solid var(--primary);
            max-width: 100%; /* Responsive layout constraint rule */
        }

        /* Flexbox Child 2: Right/Bottom Section */
        .profile-details-pane {
            padding: 40px;
            flex: 1.5;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .badge {
            align-self: flex-start;
            background: rgba(124, 58, 237, 0.15);
            color: #a78bfa;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 14px;
            border: 1px solid rgba(124, 58, 237, 0.3);
        }
        h1 { font-size: 1.8rem; color: #fff; margin-bottom: 12px; font-weight: 800; }
        .bio { color: var(--text-muted); line-height: 1.6; margin-bottom: 24px; font-size: 0.95rem; }
        
        .meta-list { border-top: 1px solid var(--border); padding-top: 20px; }
        .meta-item { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: var(--text-main); font-size: 0.9rem; }
        .meta-item i { color: #a78bfa; width: 16px; text-align: center; }

        .btn-back { display: inline-block; margin-top: 20px; color: #a78bfa; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
        .btn-back:hover { text-decoration: underline; }

        /* ── DESKTOP BREAKPOINT (Flexbox layout switch to Row) ── */
        @media (min-width: 768px) {
            .profile-card { flex-direction: row; }
            .profile-avatar-pane { border-bottom: none; border-right: 1px solid var(--border); padding: 60px; }
            .avatar-circle { width: 160px; height: 160px; font-size: 4rem; }
        }
    </style>
</head>
<body>

    <div class="profile-card">
        <div class="profile-avatar-pane">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
            </div>
        </div>

        <div class="profile-details-pane">
            <span class="badge"><?php echo htmlspecialchars($display_role); ?></span>
            <h1>Account Credentials Overview</h1>
            <p class="bio">
                Active internal operations node for the StockTrack Management ecosystem. Authorized to execute system directives matching assigned clearance parameters.
            </p>

            <div class="meta-list">
                <div class="meta-item">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>System Identifier Name: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                </div>
                <div class="meta-item">
                    <i class="fa-solid fa-database"></i>
                    <span>Database Target: <strong>inventory_db</strong></span>
                </div>
            </div>

            <a href="dashboard.php" class="btn-back">&larr; Return to Workspace Dashboard</a>
        </div>
    </div>

</body>
</html>