<?php
/**
 * RBAC Helper — StockTrack
 * Hierarchy: super_admin(4) > manager(3) > stock_clerk(2) > viewer(1)
 */

$ROLE_WEIGHTS = [
    'super_admin' => 4,
    'manager'     => 3,
    'stock_clerk' => 2,
    'viewer'      => 1,
];

$ROLE_LABELS = [
    'super_admin' => 'Super Admin',
    'manager'     => 'Manager',
    'stock_clerk' => 'Stock Clerk',
    'viewer'      => 'Viewer / Auditor',
];

$ROLE_COLORS = [
    'super_admin' => '#6366f1',
    'manager'     => '#0ea5e9',
    'stock_clerk' => '#10b981',
    'viewer'      => '#94a3b8',
];

/** Redirect to login if not authenticated. */
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/** Return true if the current user's role meets or exceeds $minimum_role. */
function has_role(string $minimum_role): bool {
    global $ROLE_WEIGHTS;
    $user_role = $_SESSION['user_role'] ?? 'viewer';
    return ($ROLE_WEIGHTS[$user_role] ?? 0) >= ($ROLE_WEIGHTS[$minimum_role] ?? 0);
}

/** Redirect to 403.php if the user's role is insufficient. */
function require_role(string $minimum_role): void {
    require_login();
    if (!has_role($minimum_role)) {
        header("Location: 403.php");
        exit();
    }
}

/** Render a coloured role badge as an HTML string. */
function role_badge(string $role): string {
    global $ROLE_LABELS, $ROLE_COLORS;
    $label = htmlspecialchars($ROLE_LABELS[$role] ?? $role);
    $color = $ROLE_COLORS[$role] ?? '#94a3b8';
    return "<span style=\"display:inline-block; background:{$color}22; color:{$color};
        border:1px solid {$color}44; padding:3px 10px; border-radius:999px;
        font-size:0.75rem; font-weight:600; line-height:1.6;\">{$label}</span>";
}

/** Sanitise a string from POST — trim + htmlspecialchars. */
function clean(string $key, string $default = ''): string {
    return trim(htmlspecialchars($_POST[$key] ?? $default));
}
?>