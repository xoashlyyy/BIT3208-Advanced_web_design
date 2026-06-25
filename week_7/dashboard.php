<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$role = $_SESSION['user_role'] ?? 'viewer';

// ── CRUD ─────────────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        require_role('stock_clerk');
        $name  = clean('product_name');
        $sku   = strtoupper(clean('sku'));
        $qty   = max(0, intval($_POST['quantity'] ?? 0));
        $price = has_role('manager') ? max(0, floatval($_POST['price'] ?? 0)) : 0.00;
        $stmt  = $conn->prepare("INSERT INTO products (product_name, sku, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssid", $name, $sku, $qty, $price);
        if ($stmt->execute()) $_SESSION['flash_success'] = "Product added successfully.";
        else                   $_SESSION['flash_error']   = "Failed to add product. SKU may already exist.";

    } elseif ($action === 'edit') {
        if (!has_role('stock_clerk')) { $_SESSION['flash_error'] = "Permission denied."; header("Location: dashboard.php"); exit(); }
        $id  = intval($_POST['id'] ?? 0);
        $qty = max(0, intval($_POST['quantity'] ?? 0));
        if (has_role('manager')) {
            $name  = clean('product_name');
            $sku   = strtoupper(clean('sku'));
            $price = max(0, floatval($_POST['price'] ?? 0));
            $stmt  = $conn->prepare("UPDATE products SET product_name=?, sku=?, quantity=?, price=? WHERE id=?");
            $stmt->bind_param("ssidi", $name, $sku, $qty, $price, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET quantity=? WHERE id=?");
            $stmt->bind_param("ii", $qty, $id);
        }
        if ($stmt->execute()) $_SESSION['flash_success'] = "Product updated.";
        else                   $_SESSION['flash_error']   = "Failed to update.";

    } elseif ($action === 'delete') {
        require_role('manager');
        $id   = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) $_SESSION['flash_success'] = "Product deleted.";
        else                   $_SESSION['flash_error']   = "Failed to delete.";
    }
    header("Location: dashboard.php"); exit();
}

// ── DATA ──────────────────────────────────────────────────────────────────────
$result         = $conn->query("SELECT id, product_name, sku, quantity, price FROM products ORDER BY id DESC");
$total_products = $result->num_rows;
$total_value    = 0; $low_stock = 0; $out_stock = 0; $products = [];
while ($row = $result->fetch_assoc()) {
    $products[]  = $row;
    $total_value += $row['quantity'] * $row['price'];
    if ($row['quantity'] == 0) $out_stock++;
    elseif ($row['quantity'] < 5) $low_stock++;
}
$in_stock = $total_products - $low_stock - $out_stock;

// ── FLASH HELPERS ─────────────────────────────────────────────────────────────
function flash() {
    $out = '';
    if (isset($_SESSION['flash_success'])) {
        $out .= "<div class='flash flash-ok'>".htmlspecialchars($_SESSION['flash_success'])."</div>";
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        $out .= "<div class='flash flash-err'>".htmlspecialchars($_SESSION['flash_error'])."</div>";
        unset($_SESSION['flash_error']);
    }
    return $out;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php if ($role === 'super_admin'): ?>
<!-- ═══════════════════════════════════════════════════════════════════════════
     SUPER ADMIN — Command Centre  (dark purple, top navbar, wide grid)
════════════════════════════════════════════════════════════════════════════ -->
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:#08050f; color:#e2d9f3; min-height:100vh; }

/* Top navbar */
.sa-nav {
    position:sticky; top:0; z-index:100;
    background:rgba(18,10,35,0.95); backdrop-filter:blur(12px);
    border-bottom:1px solid #2a1d50;
    display:flex; align-items:center; justify-content:space-between;
    padding:0 32px; height:64px;
}
.sa-nav .brand { font-size:1.3rem; font-weight:800; color:#fff; letter-spacing:-0.5px; }
.sa-nav .brand span { color:#a78bfa; }
.sa-nav .nav-links { display:flex; gap:4px; }
.sa-nav .nav-links a {
    padding:8px 16px; border-radius:8px; color:#94a3b8; font-size:0.875rem;
    font-weight:500; text-decoration:none; transition:all 0.2s; display:flex; align-items:center; gap:6px;
}
.sa-nav .nav-links a:hover, .sa-nav .nav-links a.active { background:#2a1d50; color:#a78bfa; }
.sa-nav .nav-right { display:flex; align-items:center; gap:12px; }
.sa-nav .user-pill {
    display:flex; align-items:center; gap:8px; background:#1e1340;
    border:1px solid #3b2a6e; border-radius:999px; padding:6px 14px 6px 8px;
}
.sa-nav .user-pill .avatar {
    width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg,#7c3aed,#a78bfa);
    display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:#fff;
}
.sa-nav .user-pill .uname { font-size:0.82rem; font-weight:600; color:#e2d9f3; }
.sa-nav .user-pill .urole { font-size:0.7rem; color:#7c3aed; }
.sa-nav .btn-logout {
    background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2);
    color:#f87171; padding:7px 14px; border-radius:8px; font-size:0.82rem;
    font-weight:500; text-decoration:none; transition:all 0.2s;
}
.sa-nav .btn-logout:hover { background:rgba(239,68,68,0.2); }

/* Page body */
.sa-body { max-width:1400px; margin:0 auto; padding:32px; }
.sa-page-title { font-size:1.6rem; font-weight:800; color:#fff; margin-bottom:4px; }
.sa-page-sub { color:#6b5fa0; font-size:0.875rem; margin-bottom:28px; }

/* KPI row */
.sa-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
.sa-kpi {
    background:linear-gradient(135deg,#130d2a,#1e1340);
    border:1px solid #2a1d50; border-radius:14px; padding:22px 24px;
    position:relative; overflow:hidden; cursor:default;
    transition:transform 0.2s, border-color 0.2s;
}
.sa-kpi:hover { transform:translateY(-2px); border-color:#7c3aed; }
.sa-kpi .kpi-icon {
    width:44px; height:44px; border-radius:10px; margin-bottom:14px;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.sa-kpi .kpi-val { font-size:2rem; font-weight:800; color:#fff; line-height:1; margin-bottom:4px; }
.sa-kpi .kpi-label { font-size:0.78rem; color:#6b5fa0; text-transform:uppercase; letter-spacing:0.8px; font-weight:600; }
.sa-kpi .kpi-glow {
    position:absolute; top:-30px; right:-30px; width:100px; height:100px;
    border-radius:50%; opacity:0.15; filter:blur(24px);
}

/* Main grid: table left, panel right */
.sa-grid { display:grid; grid-template-columns:1fr 300px; gap:20px; }

/* Table card */
.sa-card {
    background:#0f0a20; border:1px solid #2a1d50; border-radius:14px; overflow:hidden;
}
.sa-card-head {
    padding:20px 24px; border-bottom:1px solid #2a1d50;
    display:flex; justify-content:space-between; align-items:center;
}
.sa-card-head h3 { font-size:0.95rem; font-weight:700; color:#e2d9f3; }
.sa-card-head .count { background:#2a1d50; color:#a78bfa; font-size:0.75rem; font-weight:600; padding:3px 10px; border-radius:999px; }

table.sa-table { width:100%; border-collapse:collapse; }
table.sa-table th {
    padding:12px 20px; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px;
    color:#6b5fa0; font-weight:600; background:#09061a; border-bottom:1px solid #2a1d50; text-align:left;
}
table.sa-table td { padding:14px 20px; border-bottom:1px solid #160f2e; font-size:0.88rem; }
table.sa-table tr:last-child td { border-bottom:none; }
table.sa-table tr:hover td { background:#140e28; }
.sa-sku { font-family:monospace; color:#6b5fa0; font-size:0.8rem; }
.sa-qty { font-weight:700; color:#fff; }
.sa-price { color:#a78bfa; font-weight:600; }
.sa-act { display:flex; gap:8px; }
.sa-act button, .sa-act-del {
    padding:5px 12px; border-radius:6px; font-size:0.78rem; font-weight:600;
    cursor:pointer; border:none; transition:all 0.2s;
}
.sa-act .btn-edit { background:#2a1d50; color:#a78bfa; }
.sa-act .btn-edit:hover { background:#3b2a6e; }
.sa-act .btn-del { background:rgba(239,68,68,0.1); color:#f87171; }
.sa-act .btn-del:hover { background:rgba(239,68,68,0.2); }

/* Right panel */
.sa-panel { display:flex; flex-direction:column; gap:16px; }
.sa-panel-card {
    background:#0f0a20; border:1px solid #2a1d50; border-radius:14px; padding:20px;
}
.sa-panel-card h4 { font-size:0.82rem; text-transform:uppercase; letter-spacing:0.8px;
                    color:#6b5fa0; font-weight:600; margin-bottom:16px; }
.sa-stock-bar { margin-bottom:12px; }
.sa-stock-bar .bar-label { display:flex; justify-content:space-between; font-size:0.78rem; margin-bottom:4px; }
.sa-stock-bar .bar-track { height:6px; background:#2a1d50; border-radius:999px; overflow:hidden; }
.sa-stock-bar .bar-fill { height:100%; border-radius:999px; }
.sa-add-btn {
    width:100%; padding:12px; border-radius:10px; border:none; cursor:pointer;
    background:linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff;
    font-weight:700; font-size:0.9rem; transition:all 0.2s; letter-spacing:0.3px;
}
.sa-add-btn:hover { opacity:0.9; transform:translateY(-1px); box-shadow:0 4px 20px rgba(124,58,237,0.4); }
.sa-user-btn {
    width:100%; padding:12px; border-radius:10px; cursor:pointer; margin-top:8px;
    background:transparent; border:1px solid #2a1d50; color:#a78bfa;
    font-weight:600; font-size:0.875rem; transition:all 0.2s; text-decoration:none;
    display:block; text-align:center;
}
.sa-user-btn:hover { background:#1e1340; border-color:#7c3aed; }

/* Badges */
.badge { padding:3px 9px; border-radius:999px; font-size:0.72rem; font-weight:700; }
.badge.in-stock  { background:rgba(16,185,129,.12); color:#10b981; }
.badge.low-stock { background:rgba(245,158,11,.12);  color:#f59e0b; }
.badge.out-stock { background:rgba(239,68,68,.12);   color:#ef4444; }

/* Flash */
.flash { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:0.875rem; font-weight:500; }
.flash-ok  { background:rgba(16,185,129,.1); color:#10b981; border:1px solid rgba(16,185,129,.2); }
.flash-err { background:rgba(239,68,68,.1);  color:#f87171; border:1px solid rgba(239,68,68,.2); }

/* Modal */
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.8); backdrop-filter:blur(8px); z-index:500; align-items:center; justify-content:center; }
.modal.active { display:flex; }
.modal-box {
    background:#130d2a; border:1px solid #3b2a6e; border-radius:16px;
    padding:32px; width:100%; max-width:460px; position:relative;
    box-shadow:0 0 60px rgba(124,58,237,0.2);
}
.modal-box h2 { color:#a78bfa; margin-bottom:22px; font-size:1.2rem; }
.modal-close { position:absolute; top:16px; right:16px; background:#2a1d50; border:none; color:#a78bfa; width:30px; height:30px; border-radius:50%; cursor:pointer; font-size:1rem; display:flex; align-items:center; justify-content:center; }
.modal-close:hover { background:#3b2a6e; }
.modal-box label { display:block; font-size:0.8rem; color:#6b5fa0; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.modal-box input {
    width:100%; padding:10px 14px; background:#08050f; border:1px solid #2a1d50;
    border-radius:8px; color:#e2d9f3; font-size:0.9rem; margin-bottom:16px;
}
.modal-box input:focus { outline:none; border-color:#7c3aed; }
.modal-box .row2 { display:flex; gap:12px; }
.modal-box .row2 > div { flex:1; }
.modal-submit {
    width:100%; padding:12px; border:none; border-radius:10px; cursor:pointer;
    background:linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff;
    font-weight:700; font-size:0.95rem;
}
.modal-submit:hover { opacity:0.9; }
.field-locked { opacity:0.4 !important; cursor:not-allowed !important; }
</style>

<?php elseif ($role === 'manager'): ?>
<!-- ═══════════════════════════════════════════════════════════════════════════
     MANAGER — Split Panel  (navy blue, sidebar + card grid)
════════════════════════════════════════════════════════════════════════════ -->
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:#020b14; color:#cbd5e1; min-height:100vh; display:flex; }

/* Sidebar */
.mg-side {
    width:240px; min-height:100vh; background:#040f1c;
    border-right:1px solid #0f2a3e; padding:28px 20px;
    display:flex; flex-direction:column; position:sticky; top:0; height:100vh; overflow-y:auto;
}
.mg-brand { font-size:1.2rem; font-weight:800; color:#fff; margin-bottom:36px; letter-spacing:-0.5px; }
.mg-brand span { color:#0ea5e9; }
.mg-section { font-size:0.68rem; text-transform:uppercase; letter-spacing:1px; color:#1e4060; font-weight:700; margin-bottom:8px; margin-top:24px; }
.mg-side a {
    display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px;
    color:#64748b; font-size:0.875rem; font-weight:500; text-decoration:none;
    transition:all 0.2s; margin-bottom:2px;
}
.mg-side a:hover, .mg-side a.active { background:#0d2236; color:#38bdf8; }
.mg-side a i { width:16px; text-align:center; font-size:0.85rem; }
.mg-logout { margin-top:auto !important; color:#ef4444 !important; }
.mg-logout:hover { background:rgba(239,68,68,0.08) !important; color:#f87171 !important; }
.mg-user-block {
    background:#0d2236; border:1px solid #0f2a3e; border-radius:10px;
    padding:14px; margin-top:20px;
}
.mg-user-block .ub-name { font-weight:700; color:#fff; font-size:0.875rem; }
.mg-user-block .ub-role { font-size:0.72rem; color:#0ea5e9; margin-top:2px; }

/* Main */
.mg-main { flex:1; padding:32px; overflow-y:auto; }
.mg-topbar { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.mg-topbar h1 { font-size:1.5rem; font-weight:800; color:#fff; }
.mg-topbar .sub { color:#1e4060; font-size:0.85rem; margin-top:2px; }
.mg-add-btn {
    background:#0ea5e9; color:#fff; border:none; padding:10px 20px;
    border-radius:10px; font-weight:700; font-size:0.875rem; cursor:pointer;
    display:flex; align-items:center; gap:8px; transition:all 0.2s;
    box-shadow:0 0 20px rgba(14,165,233,0.3);
}
.mg-add-btn:hover { background:#0284c7; box-shadow:0 0 30px rgba(14,165,233,0.5); }

/* Stats */
.mg-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:28px; }
.mg-stat {
    background:#040f1c; border:1px solid #0f2a3e; border-radius:12px;
    padding:18px 20px; display:flex; align-items:center; gap:14px;
    transition:border-color 0.2s;
}
.mg-stat:hover { border-color:#0ea5e9; }
.mg-stat .st-ico {
    width:42px; height:42px; border-radius:10px; display:flex;
    align-items:center; justify-content:center; font-size:1rem; flex-shrink:0;
}
.mg-stat .st-val { font-size:1.6rem; font-weight:800; color:#fff; line-height:1; }
.mg-stat .st-lbl { font-size:0.72rem; color:#1e4060; text-transform:uppercase; letter-spacing:0.6px; font-weight:600; margin-top:2px; }

/* Product cards grid */
.mg-cards-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.mg-cards-head h3 { font-size:1rem; font-weight:700; color:#fff; }
.mg-product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
.mg-product-card {
    background:#040f1c; border:1px solid #0f2a3e; border-radius:12px;
    padding:18px 20px; transition:all 0.2s; position:relative; overflow:hidden;
}
.mg-product-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#0ea5e9,#38bdf8);
}
.mg-product-card:hover { border-color:#0ea5e9; transform:translateY(-2px); box-shadow:0 8px 24px rgba(14,165,233,0.1); }
.mg-product-card .pc-sku { font-size:0.72rem; font-family:monospace; color:#1e4060; margin-bottom:6px; text-transform:uppercase; }
.mg-product-card .pc-name { font-size:0.95rem; font-weight:700; color:#fff; margin-bottom:12px; }
.mg-product-card .pc-meta { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
.mg-product-card .pc-qty { font-size:1.3rem; font-weight:800; color:#fff; }
.mg-product-card .pc-qty-lbl { font-size:0.7rem; color:#1e4060; text-transform:uppercase; letter-spacing:0.5px; }
.mg-product-card .pc-price { font-size:1rem; font-weight:700; color:#0ea5e9; }
.mg-product-card .pc-actions { display:flex; gap:8px; }
.pc-btn-edit, .pc-btn-del {
    flex:1; padding:7px; border-radius:7px; border:none; cursor:pointer;
    font-size:0.78rem; font-weight:600; transition:all 0.2s;
}
.pc-btn-edit { background:#0d2236; color:#38bdf8; }
.pc-btn-edit:hover { background:#0f2a3e; }
.pc-btn-del { background:rgba(239,68,68,0.08); color:#f87171; }
.pc-btn-del:hover { background:rgba(239,68,68,0.15); }

/* Badge */
.badge { padding:3px 9px; border-radius:999px; font-size:0.72rem; font-weight:700; }
.badge.in-stock  { background:rgba(16,185,129,.12); color:#10b981; }
.badge.low-stock { background:rgba(245,158,11,.12);  color:#f59e0b; }
.badge.out-stock { background:rgba(239,68,68,.12);   color:#ef4444; }

/* Flash */
.flash { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:0.875rem; font-weight:500; }
.flash-ok  { background:rgba(16,185,129,.1); color:#10b981; border:1px solid rgba(16,185,129,.2); }
.flash-err { background:rgba(239,68,68,.1);  color:#f87171; border:1px solid rgba(239,68,68,.2); }

/* Modal */
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.8); backdrop-filter:blur(8px); z-index:500; align-items:center; justify-content:center; }
.modal.active { display:flex; }
.modal-box { background:#040f1c; border:1px solid #0f2a3e; border-radius:14px; padding:30px; width:100%; max-width:460px; position:relative; box-shadow:0 0 50px rgba(14,165,233,0.15); }
.modal-box h2 { color:#38bdf8; margin-bottom:20px; font-size:1.1rem; }
.modal-close { position:absolute; top:14px; right:14px; background:#0d2236; border:none; color:#38bdf8; width:28px; height:28px; border-radius:50%; cursor:pointer; font-size:0.9rem; display:flex; align-items:center; justify-content:center; }
.modal-close:hover { background:#0f2a3e; }
.modal-box label { display:block; font-size:0.78rem; color:#1e4060; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.modal-box input { width:100%; padding:10px 14px; background:#020b14; border:1px solid #0f2a3e; border-radius:8px; color:#cbd5e1; font-size:0.9rem; margin-bottom:14px; }
.modal-box input:focus { outline:none; border-color:#0ea5e9; }
.modal-box .row2 { display:flex; gap:12px; }
.modal-box .row2 > div { flex:1; }
.modal-submit { width:100%; padding:11px; border:none; border-radius:9px; cursor:pointer; background:#0ea5e9; color:#fff; font-weight:700; font-size:0.9rem; transition:all 0.2s; }
.modal-submit:hover { background:#0284c7; }
.field-locked { opacity:0.4 !important; cursor:not-allowed !important; }
</style>

<?php elseif ($role === 'stock_clerk'): ?>
<!-- ═══════════════════════════════════════════════════════════════════════════
     STOCK CLERK — Action-First  (green, big action strip, compact table)
════════════════════════════════════════════════════════════════════════════ -->
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:#020f08; color:#d1fae5; min-height:100vh; }

/* Top action bar */
.sc-topbar {
    background:linear-gradient(135deg,#052e1a,#0a3d24);
    border-bottom:1px solid #0d4a2c; padding:20px 32px;
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;
}
.sc-brand { font-size:1.2rem; font-weight:800; color:#fff; letter-spacing:-0.5px; }
.sc-brand span { color:#34d399; }
.sc-user { display:flex; align-items:center; gap:10px; }
.sc-user .u-name { font-size:0.85rem; font-weight:600; color:#34d399; }
.sc-user .u-role { font-size:0.7rem; color:#064e32; }
.sc-logout { color:#ef4444; font-size:0.8rem; text-decoration:none; }
.sc-logout:hover { color:#f87171; }

/* Action strip */
.sc-action-strip {
    background:#031a0f; border-bottom:1px solid #0d4a2c; padding:16px 32px;
    display:flex; gap:12px; flex-wrap:wrap;
}
.sc-action-btn {
    display:flex; align-items:center; gap:8px; padding:10px 20px;
    border-radius:10px; border:none; cursor:pointer; font-size:0.875rem;
    font-weight:700; transition:all 0.2s; text-decoration:none;
}
.sc-action-btn.primary { background:linear-gradient(135deg,#10b981,#059669); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,0.3); }
.sc-action-btn.primary:hover { box-shadow:0 6px 20px rgba(16,185,129,0.5); transform:translateY(-1px); }
.sc-action-btn.secondary { background:#052e1a; border:1px solid #0d4a2c; color:#34d399; }
.sc-action-btn.secondary:hover { background:#064e32; }

/* Body */
.sc-body { max-width:1200px; margin:0 auto; padding:28px 32px; }

/* Mini stats */
.sc-mini-stats { display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.sc-mini-stat {
    background:#031a0f; border:1px solid #0d4a2c; border-radius:10px;
    padding:14px 20px; display:flex; align-items:center; gap:12px; flex:1; min-width:160px;
}
.sc-mini-stat .ms-ico { font-size:1.4rem; }
.sc-mini-stat .ms-val { font-size:1.5rem; font-weight:800; color:#fff; line-height:1; }
.sc-mini-stat .ms-lbl { font-size:0.7rem; color:#064e32; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-top:2px; }

/* Table */
.sc-table-wrap { background:#031a0f; border:1px solid #0d4a2c; border-radius:12px; overflow:hidden; }
.sc-table-head { padding:16px 20px; border-bottom:1px solid #0d4a2c; display:flex; justify-content:space-between; align-items:center; }
.sc-table-head h3 { font-size:0.9rem; font-weight:700; color:#fff; }
.sc-search { background:#020f08; border:1px solid #0d4a2c; border-radius:7px; padding:7px 12px; color:#d1fae5; font-size:0.82rem; width:220px; }
.sc-search:focus { outline:none; border-color:#10b981; }
table.sc-tbl { width:100%; border-collapse:collapse; }
table.sc-tbl th { padding:11px 18px; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.8px; color:#064e32; font-weight:700; background:#020f08; border-bottom:1px solid #0d4a2c; text-align:left; }
table.sc-tbl td { padding:13px 18px; border-bottom:1px solid #041c10; font-size:0.875rem; }
table.sc-tbl tr:last-child td { border-bottom:none; }
table.sc-tbl tr:hover td { background:#041c10; }
.sc-sku { font-family:monospace; color:#064e32; font-size:0.8rem; }
.sc-qty-big { font-size:1.1rem; font-weight:800; color:#fff; }
.sc-price { color:#34d399; font-weight:600; }
.sc-upd-btn { background:#052e1a; border:1px solid #0d4a2c; color:#34d399; padding:5px 12px; border-radius:6px; font-size:0.78rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
.sc-upd-btn:hover { background:#064e32; }

/* Badge */
.badge { padding:3px 9px; border-radius:999px; font-size:0.72rem; font-weight:700; }
.badge.in-stock  { background:rgba(16,185,129,.12); color:#10b981; }
.badge.low-stock { background:rgba(245,158,11,.12);  color:#f59e0b; }
.badge.out-stock { background:rgba(239,68,68,.12);   color:#ef4444; }

/* Flash */
.flash { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:0.875rem; font-weight:500; }
.flash-ok  { background:rgba(16,185,129,.1); color:#10b981; border:1px solid rgba(16,185,129,.2); }
.flash-err { background:rgba(239,68,68,.1);  color:#f87171; border:1px solid rgba(239,68,68,.2); }

/* Modal */
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.8); backdrop-filter:blur(8px); z-index:500; align-items:center; justify-content:center; }
.modal.active { display:flex; }
.modal-box { background:#031a0f; border:1px solid #0d4a2c; border-radius:14px; padding:30px; width:100%; max-width:460px; position:relative; box-shadow:0 0 50px rgba(16,185,129,0.1); }
.modal-box h2 { color:#34d399; margin-bottom:20px; font-size:1.1rem; }
.modal-close { position:absolute; top:14px; right:14px; background:#052e1a; border:none; color:#34d399; width:28px; height:28px; border-radius:50%; cursor:pointer; font-size:0.9rem; display:flex; align-items:center; justify-content:center; }
.modal-close:hover { background:#064e32; }
.modal-box label { display:block; font-size:0.78rem; color:#064e32; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.modal-box input { width:100%; padding:10px 14px; background:#020f08; border:1px solid #0d4a2c; border-radius:8px; color:#d1fae5; font-size:0.9rem; margin-bottom:14px; }
.modal-box input:focus { outline:none; border-color:#10b981; }
.modal-box .row2 { display:flex; gap:12px; }
.modal-box .row2 > div { flex:1; }
.modal-submit { width:100%; padding:11px; border:none; border-radius:9px; cursor:pointer; background:linear-gradient(135deg,#10b981,#059669); color:#fff; font-weight:700; font-size:0.9rem; }
.modal-submit:hover { opacity:0.9; }
.field-locked { opacity:0.35 !important; cursor:not-allowed !important; }
</style>

<?php else: /* viewer */ ?>
<!-- ═══════════════════════════════════════════════════════════════════════════
     VIEWER — Clean Report  (light mode, read-only, no action elements)
════════════════════════════════════════════════════════════════════════════ -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
body { background:#f1f5f9; color:#1e293b; min-height:100vh; }

/* Top bar */
.vw-topbar {
    background:#fff; border-bottom:1px solid #e2e8f0;
    padding:0 40px; height:60px;
    display:flex; align-items:center; justify-content:space-between;
    position:sticky; top:0; z-index:100; box-shadow:0 1px 4px rgba(0,0,0,0.05);
}
.vw-brand { font-size:1.1rem; font-weight:800; color:#0f172a; letter-spacing:-0.5px; }
.vw-brand span { color:#64748b; }
.vw-user { display:flex; align-items:center; gap:10px; }
.vw-badge { background:#f1f5f9; border:1px solid #e2e8f0; color:#64748b; padding:4px 10px; border-radius:999px; font-size:0.75rem; font-weight:600; }
.vw-logout { color:#94a3b8; font-size:0.8rem; text-decoration:none; }
.vw-logout:hover { color:#64748b; }

/* Body */
.vw-body { max-width:1100px; margin:0 auto; padding:36px 40px; }
.vw-heading { margin-bottom:28px; }
.vw-heading h1 { font-size:1.5rem; font-weight:800; color:#0f172a; margin-bottom:4px; }
.vw-heading p { color:#94a3b8; font-size:0.875rem; }

/* Read-only notice */
.vw-notice {
    background:#fefce8; border:1px solid #fde68a; border-radius:10px;
    padding:12px 16px; margin-bottom:24px; font-size:0.85rem; color:#92400e;
    display:flex; align-items:center; gap:8px;
}

/* KPI */
.vw-kpi { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:28px; }
.vw-kpi-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    padding:20px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,0.04);
}
.vw-kpi-card .kv { font-size:2rem; font-weight:800; color:#0f172a; margin-bottom:4px; }
.vw-kpi-card .kl { font-size:0.75rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.6px; font-weight:600; }

/* Table */
.vw-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
.vw-table-head { padding:18px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
.vw-table-head h3 { font-size:0.95rem; font-weight:700; color:#0f172a; }
.vw-table-head .count { background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:600; }
table.vw-tbl { width:100%; border-collapse:collapse; }
table.vw-tbl th { padding:12px 24px; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8; font-weight:600; background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; }
table.vw-tbl td { padding:14px 24px; border-bottom:1px solid #f1f5f9; font-size:0.875rem; color:#334155; }
table.vw-tbl tr:last-child td { border-bottom:none; }
table.vw-tbl tr:hover td { background:#f8fafc; }
.vw-sku { font-family:monospace; color:#94a3b8; font-size:0.8rem; }
.vw-name { font-weight:600; color:#0f172a; }
.vw-price { font-weight:600; color:#0f172a; }

/* Badge */
.badge { padding:3px 9px; border-radius:999px; font-size:0.72rem; font-weight:700; }
.badge.in-stock  { background:#dcfce7; color:#16a34a; }
.badge.low-stock { background:#fef9c3; color:#ca8a04; }
.badge.out-stock { background:#fee2e2; color:#dc2626; }

/* Flash */
.flash { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:0.875rem; font-weight:500; }
.flash-ok  { background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; }
.flash-err { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
</style>
<?php endif; ?>
</head>
<body>

<?php
/* ════════════════════════════════════════════════════════════════════
   RENDER: SUPER ADMIN
═══════════════════════════════════════════════════════════════════ */
if ($role === 'super_admin'):
    $initials = strtoupper(substr($_SESSION['user_name'], 0, 2));
?>
<nav class="sa-nav">
    <div class="brand">Stock<span>Track.</span></div>
    <div class="nav-links">
        <a href="dashboard.php" class="active"><i class="fa fa-boxes-stacked"></i> Inventory</a>
        <a href="users.php"><i class="fa fa-users"></i> Users</a>
    </div>
    <div class="nav-right">
        <div class="user-pill">
            <div class="avatar"><?php echo $initials; ?></div>
            <div>
                <div class="uname"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="urole">Super Admin</div>
            </div>
        </div>
        <a href="login.php?logout=1" class="btn-logout"><i class="fa fa-right-from-bracket"></i> Sign Out</a>
    </div>
</nav>

<div class="sa-body">
    <div class="sa-page-title">Command Centre</div>
    <div class="sa-page-sub">Full system overview — <?php echo date('l, F j, Y'); ?></div>

    <?php echo flash(); ?>

    <!-- KPIs -->
    <div class="sa-kpi-row">
        <div class="sa-kpi">
            <div class="kpi-icon" style="background:rgba(124,58,237,0.15); color:#a78bfa;"><i class="fa fa-cubes"></i></div>
            <div class="kpi-val"><?php echo $total_products; ?></div>
            <div class="kpi-label">Total SKUs</div>
            <div class="kpi-glow" style="background:#7c3aed;"></div>
        </div>
        <div class="sa-kpi">
            <div class="kpi-icon" style="background:rgba(16,185,129,0.15); color:#10b981;"><i class="fa fa-circle-check"></i></div>
            <div class="kpi-val"><?php echo $in_stock; ?></div>
            <div class="kpi-label">In Stock</div>
            <div class="kpi-glow" style="background:#10b981;"></div>
        </div>
        <div class="sa-kpi">
            <div class="kpi-icon" style="background:rgba(245,158,11,0.15); color:#f59e0b;"><i class="fa fa-triangle-exclamation"></i></div>
            <div class="kpi-val" style="color:<?php echo ($low_stock+$out_stock)>0?'#f59e0b':'#fff'; ?>"><?php echo $low_stock + $out_stock; ?></div>
            <div class="kpi-label">Needs Attention</div>
            <div class="kpi-glow" style="background:#f59e0b;"></div>
        </div>
        <div class="sa-kpi">
            <div class="kpi-icon" style="background:rgba(124,58,237,0.15); color:#a78bfa;"><i class="fa fa-dollar-sign"></i></div>
            <div class="kpi-val" style="font-size:1.5rem;">$<?php echo number_format($total_value,0); ?></div>
            <div class="kpi-label">Asset Value</div>
            <div class="kpi-glow" style="background:#7c3aed;"></div>
        </div>
    </div>

    <div class="sa-grid">
        <!-- Table -->
        <div class="sa-card">
            <div class="sa-card-head">
                <h3><i class="fa fa-table-list" style="color:#7c3aed; margin-right:8px;"></i>Inventory</h3>
                <span class="count"><?php echo $total_products; ?> items</span>
            </div>
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>SKU</th><th>Product</th><th>Status</th><th>Qty</th><th>Price</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($products as $p): ?>
                <tr>
                    <td class="sa-sku"><?php echo htmlspecialchars($p['sku']); ?></td>
                    <td style="font-weight:600; color:#e2d9f3;"><?php echo htmlspecialchars($p['product_name']); ?></td>
                    <td>
                        <?php if($p['quantity']==0): ?><span class="badge out-stock">Out of Stock</span>
                        <?php elseif($p['quantity']<5): ?><span class="badge low-stock">Low Stock</span>
                        <?php else: ?><span class="badge in-stock">In Stock</span><?php endif; ?>
                    </td>
                    <td class="sa-qty"><?php echo $p['quantity']; ?></td>
                    <td class="sa-price">$<?php echo number_format($p['price'],2); ?></td>
                    <td>
                        <div class="sa-act">
                            <button class="btn-edit" onclick="openEdit(<?php echo (int)$p['id']; ?>,<?php echo json_encode($p['product_name']); ?>,<?php echo json_encode($p['sku']); ?>,<?php echo (int)$p['quantity']; ?>,<?php echo number_format($p['price'],2,'.',''); ?>)">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete <?php echo htmlspecialchars(addslashes($p['product_name'])); ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                <button type="submit" class="btn-del">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Right panel -->
        <div class="sa-panel">
            <div class="sa-panel-card">
                <h4>Quick Actions</h4>
                <button class="sa-add-btn" onclick="openModal('addModal')"><i class="fa fa-plus"></i> Add New Product</button>
                <a href="users.php" class="sa-user-btn"><i class="fa fa-users-gear"></i> Manage Users</a>
            </div>
            <div class="sa-panel-card">
                <h4>Stock Health</h4>
                <?php $tp = max($total_products,1); ?>
                <div class="sa-stock-bar">
                    <div class="bar-label"><span style="color:#10b981;">In Stock</span><span style="color:#10b981;"><?php echo $in_stock; ?></span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:<?php echo round($in_stock/$tp*100); ?>%; background:#10b981;"></div></div>
                </div>
                <div class="sa-stock-bar">
                    <div class="bar-label"><span style="color:#f59e0b;">Low Stock</span><span style="color:#f59e0b;"><?php echo $low_stock; ?></span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:<?php echo round($low_stock/$tp*100); ?>%; background:#f59e0b;"></div></div>
                </div>
                <div class="sa-stock-bar">
                    <div class="bar-label"><span style="color:#ef4444;">Out of Stock</span><span style="color:#ef4444;"><?php echo $out_stock; ?></span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:<?php echo round($out_stock/$tp*100); ?>%; background:#ef4444;"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('addModal')">×</button>
        <h2>Add New Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <label>Product Name</label><input type="text" name="product_name" required>
            <label>SKU</label><input type="text" name="sku" placeholder="e.g. DELL-XPS-15" required>
            <div class="row2">
                <div><label>Quantity</label><input type="number" name="quantity" min="0" value="0" required></div>
                <div><label>Price ($)</label><input type="number" name="price" step="0.01" min="0" value="0.00" required></div>
            </div>
            <button type="submit" class="modal-submit">Save Product</button>
        </form>
    </div>
</div>
<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('editModal')">×</button>
        <h2>Edit Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="e_id">
            <label>Product Name</label><input type="text" name="product_name" id="e_name" required>
            <label>SKU</label><input type="text" name="sku" id="e_sku" required>
            <div class="row2">
                <div><label>Quantity</label><input type="number" name="quantity" id="e_qty" min="0" required></div>
                <div><label>Price ($)</label><input type="number" name="price" id="e_price" step="0.01" min="0" required></div>
            </div>
            <button type="submit" class="modal-submit">Update Product</button>
        </form>
    </div>
</div>

<?php
/* ════════════════════════════════════════════════════════════════════
   RENDER: MANAGER
═══════════════════════════════════════════════════════════════════ */
elseif ($role === 'manager'):
?>
<div class="mg-side">
    <div class="mg-brand">Stock<span>Track.</span></div>
    <div class="mg-section">Warehouse</div>
    <a href="dashboard.php" class="active"><i class="fa fa-house"></i> Overview</a>
    <a href="#" onclick="openModal('addModal'); return false;"><i class="fa fa-plus"></i> Add Product</a>
    <div class="mg-section">Account</div>
    <a href="login.php?logout=1" class="mg-logout"><i class="fa fa-right-from-bracket"></i> Sign Out</a>
    <div class="mg-user-block">
        <div class="ub-name">🛠️ <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
        <div class="ub-role">Manager</div>
    </div>
</div>

<div class="mg-main">
    <div class="mg-topbar">
        <div>
            <h1>Inventory Dashboard</h1>
            <div class="sub"><?php echo date('l, F j, Y'); ?></div>
        </div>
        <button class="mg-add-btn" onclick="openModal('addModal')"><i class="fa fa-plus"></i> Add Product</button>
    </div>

    <?php echo flash(); ?>

    <div class="mg-stats">
        <div class="mg-stat">
            <div class="st-ico" style="background:rgba(14,165,233,0.12); color:#0ea5e9;"><i class="fa fa-cubes"></i></div>
            <div><div class="st-val"><?php echo $total_products; ?></div><div class="st-lbl">Total SKUs</div></div>
        </div>
        <div class="mg-stat">
            <div class="st-ico" style="background:rgba(16,185,129,0.12); color:#10b981;"><i class="fa fa-circle-check"></i></div>
            <div><div class="st-val"><?php echo $in_stock; ?></div><div class="st-lbl">In Stock</div></div>
        </div>
        <div class="mg-stat">
            <div class="st-ico" style="background:rgba(245,158,11,0.12); color:#f59e0b;"><i class="fa fa-triangle-exclamation"></i></div>
            <div><div class="st-val" style="color:<?php echo ($low_stock+$out_stock)>0?'#f59e0b':'#fff'; ?>"><?php echo $low_stock+$out_stock; ?></div><div class="st-lbl">Alerts</div></div>
        </div>
        <div class="mg-stat">
            <div class="st-ico" style="background:rgba(14,165,233,0.12); color:#0ea5e9;"><i class="fa fa-dollar-sign"></i></div>
            <div><div class="st-val" style="font-size:1.2rem;">$<?php echo number_format($total_value,0); ?></div><div class="st-lbl">Asset Value</div></div>
        </div>
    </div>

    <div class="mg-cards-head">
        <h3>Products</h3>
        <span style="color:#1e4060; font-size:0.82rem;"><?php echo $total_products; ?> items</span>
    </div>
    <div class="mg-product-grid">
    <?php foreach($products as $p): ?>
        <div class="mg-product-card">
            <div class="pc-sku"><?php echo htmlspecialchars($p['sku']); ?></div>
            <div class="pc-name"><?php echo htmlspecialchars($p['product_name']); ?></div>
            <div class="pc-meta">
                <div>
                    <div class="pc-qty"><?php echo $p['quantity']; ?></div>
                    <div class="pc-qty-lbl">units</div>
                </div>
                <div style="text-align:right;">
                    <div class="pc-price">$<?php echo number_format($p['price'],2); ?></div>
                    <?php if($p['quantity']==0): ?><span class="badge out-stock">Out of Stock</span>
                    <?php elseif($p['quantity']<5): ?><span class="badge low-stock">Low Stock</span>
                    <?php else: ?><span class="badge in-stock">In Stock</span><?php endif; ?>
                </div>
            </div>
            <div class="pc-actions">
                <button class="pc-btn-edit" onclick="openEdit(<?php echo (int)$p['id']; ?>,<?php echo json_encode($p['product_name']); ?>,<?php echo json_encode($p['sku']); ?>,<?php echo (int)$p['quantity']; ?>,<?php echo number_format($p['price'],2,'.',''); ?>)"><i class="fa fa-pen"></i> Edit</button>
                <form method="POST" style="flex:1;" onsubmit="return confirm('Delete <?php echo htmlspecialchars(addslashes($p['product_name'])); ?>?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                    <button type="submit" class="pc-btn-del" style="width:100%;"><i class="fa fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('addModal')">×</button>
        <h2>Add New Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <label>Product Name</label><input type="text" name="product_name" required>
            <label>SKU</label><input type="text" name="sku" required>
            <div class="row2">
                <div><label>Quantity</label><input type="number" name="quantity" min="0" value="0" required></div>
                <div><label>Price ($)</label><input type="number" name="price" step="0.01" min="0" value="0.00" required></div>
            </div>
            <button type="submit" class="modal-submit">Save Product</button>
        </form>
    </div>
</div>
<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('editModal')">×</button>
        <h2>Edit Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="e_id">
            <label>Product Name</label><input type="text" name="product_name" id="e_name" required>
            <label>SKU</label><input type="text" name="sku" id="e_sku" required>
            <div class="row2">
                <div><label>Quantity</label><input type="number" name="quantity" id="e_qty" min="0" required></div>
                <div><label>Price ($)</label><input type="number" name="price" id="e_price" step="0.01" min="0" required></div>
            </div>
            <button type="submit" class="modal-submit">Update Product</button>
        </form>
    </div>
</div>

<?php
/* ════════════════════════════════════════════════════════════════════
   RENDER: STOCK CLERK
═══════════════════════════════════════════════════════════════════ */
elseif ($role === 'stock_clerk'):
?>
<div class="sc-topbar">
    <div class="sc-brand">Stock<span>Track.</span></div>
    <div class="sc-user">
        <div>
            <div class="u-name">📦 <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="u-role">Stock Clerk</div>
        </div>
        <a href="login.php?logout=1" class="sc-logout"><i class="fa fa-right-from-bracket"></i></a>
    </div>
</div>

<div class="sc-action-strip">
    <button class="sc-action-btn primary" onclick="openModal('addModal')"><i class="fa fa-plus"></i> Add Product</button>
    <button class="sc-action-btn secondary" onclick="document.getElementById('sc-search').focus()"><i class="fa fa-magnifying-glass"></i> Search</button>
</div>

<div class="sc-body">
    <?php echo flash(); ?>

    <div class="sc-mini-stats">
        <div class="sc-mini-stat"><div class="ms-ico">📦</div><div><div class="ms-val"><?php echo $total_products; ?></div><div class="ms-lbl">Total Items</div></div></div>
        <div class="sc-mini-stat"><div class="ms-ico">✅</div><div><div class="ms-val"><?php echo $in_stock; ?></div><div class="ms-lbl">In Stock</div></div></div>
        <div class="sc-mini-stat"><div class="ms-ico">⚠️</div><div><div class="ms-val" style="color:<?php echo $low_stock>0?'#f59e0b':'#fff'; ?>"><?php echo $low_stock; ?></div><div class="ms-lbl">Low Stock</div></div></div>
        <div class="sc-mini-stat"><div class="ms-ico">❌</div><div><div class="ms-val" style="color:<?php echo $out_stock>0?'#ef4444':'#fff'; ?>"><?php echo $out_stock; ?></div><div class="ms-lbl">Out of Stock</div></div></div>
    </div>

    <div class="sc-table-wrap">
        <div class="sc-table-head">
            <h3>All Products</h3>
            <input class="sc-search" id="sc-search" type="text" placeholder="🔍  Search products..." oninput="filterTable(this.value)">
        </div>
        <table class="sc-tbl" id="sc-table">
            <thead><tr><th>SKU</th><th>Product Name</th><th>Status</th><th>Qty</th><th>Price</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td class="sc-sku"><?php echo htmlspecialchars($p['sku']); ?></td>
                <td style="font-weight:600; color:#d1fae5;"><?php echo htmlspecialchars($p['product_name']); ?></td>
                <td>
                    <?php if($p['quantity']==0): ?><span class="badge out-stock">Out of Stock</span>
                    <?php elseif($p['quantity']<5): ?><span class="badge low-stock">Low Stock</span>
                    <?php else: ?><span class="badge in-stock">In Stock</span><?php endif; ?>
                </td>
                <td class="sc-qty-big"><?php echo $p['quantity']; ?></td>
                <td class="sc-price">$<?php echo number_format($p['price'],2); ?></td>
                <td><button class="sc-upd-btn" onclick="openEdit(<?php echo (int)$p['id']; ?>,<?php echo json_encode($p['product_name']); ?>,<?php echo json_encode($p['sku']); ?>,<?php echo (int)$p['quantity']; ?>,<?php echo number_format($p['price'],2,'.',''); ?>)">Update Qty</button></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('addModal')">×</button>
        <h2>Add New Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <label>Product Name</label><input type="text" name="product_name" required>
            <label>SKU</label><input type="text" name="sku" required>
            <div class="row2">
                <div><label>Quantity</label><input type="number" name="quantity" min="0" value="0" required></div>
                <div><label>Price ($)</label><input type="number" name="price" value="0" class="field-locked" readonly title="Set by Manager"></div>
            </div>
            <button type="submit" class="modal-submit">Add to Inventory</button>
        </form>
    </div>
</div>
<!-- EDIT MODAL (qty only) -->
<div id="editModal" class="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('editModal')">×</button>
        <h2>Update Stock Quantity</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="e_id">
            <input type="hidden" name="product_name" id="e_name">
            <input type="hidden" name="sku" id="e_sku">
            <input type="hidden" name="price" id="e_price">
            <label>Product</label>
            <input type="text" id="e_display_name" readonly class="field-locked">
            <label>New Quantity</label>
            <input type="number" name="quantity" id="e_qty" min="0" required autofocus>
            <button type="submit" class="modal-submit">Save Quantity</button>
        </form>
    </div>
</div>

<?php
/* ════════════════════════════════════════════════════════════════════
   RENDER: VIEWER
═══════════════════════════════════════════════════════════════════ */
else:
?>
<div class="vw-topbar">
    <div class="vw-brand">Stock<span>Track.</span></div>
    <div class="vw-user">
        <span class="vw-badge">👁️ Viewer / Auditor</span>
        <span style="color:#cbd5e1; font-size:0.85rem; font-weight:500;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="login.php?logout=1" class="vw-logout"><i class="fa fa-right-from-bracket"></i> Sign Out</a>
    </div>
</div>

<div class="vw-body">
    <div class="vw-heading">
        <h1>Inventory Report</h1>
        <p>Read-only snapshot — <?php echo date('F j, Y \a\t g:i A'); ?></p>
    </div>

    <div class="vw-notice">
        ⚠️ You have <strong>read-only</strong> access. Contact your Manager or Super Admin to make changes.
    </div>

    <?php echo flash(); ?>

    <div class="vw-kpi">
        <div class="vw-kpi-card"><div class="kv"><?php echo $total_products; ?></div><div class="kl">Total SKUs</div></div>
        <div class="vw-kpi-card"><div class="kv" style="color:#16a34a;"><?php echo $in_stock; ?></div><div class="kl">In Stock</div></div>
        <div class="vw-kpi-card"><div class="kv" style="color:<?php echo $low_stock>0?'#ca8a04':'#0f172a'; ?>"><?php echo $low_stock; ?></div><div class="kl">Low Stock</div></div>
        <div class="vw-kpi-card"><div class="kv" style="color:<?php echo $out_stock>0?'#dc2626':'#0f172a'; ?>"><?php echo $out_stock; ?></div><div class="kl">Out of Stock</div></div>
    </div>

    <div class="vw-table-wrap">
        <div class="vw-table-head">
            <h3>Product Inventory</h3>
            <span class="count"><?php echo $total_products; ?> items</span>
        </div>
        <table class="vw-tbl">
            <thead><tr><th>SKU</th><th>Product Name</th><th>Status</th><th>Qty</th><th>Unit Price</th><th>Total Value</th></tr></thead>
            <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td class="vw-sku"><?php echo htmlspecialchars($p['sku']); ?></td>
                <td class="vw-name"><?php echo htmlspecialchars($p['product_name']); ?></td>
                <td>
                    <?php if($p['quantity']==0): ?><span class="badge out-stock">Out of Stock</span>
                    <?php elseif($p['quantity']<5): ?><span class="badge low-stock">Low Stock</span>
                    <?php else: ?><span class="badge in-stock">In Stock</span><?php endif; ?>
                </td>
                <td style="font-weight:700;"><?php echo $p['quantity']; ?></td>
                <td class="vw-price">$<?php echo number_format($p['price'],2); ?></td>
                <td style="font-weight:600; color:#0f172a;">$<?php echo number_format($p['quantity']*$p['price'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal').forEach(function(m){
    m.addEventListener('click', function(e){ if(e.target===m) closeModal(m.id); });
});
function openEdit(id, name, sku, qty, price) {
    var ei = document.getElementById('e_id');
    var en = document.getElementById('e_name');
    var es = document.getElementById('e_sku');
    var eq = document.getElementById('e_qty');
    var ep = document.getElementById('e_price');
    var ed = document.getElementById('e_display_name');
    if(ei) ei.value = id;
    if(en) en.value = name;
    if(es) es.value = sku;
    if(eq) eq.value = qty;
    if(ep) ep.value = price;
    if(ed) ed.value = name;
    openModal('editModal');
}
function filterTable(val) {
    var rows = document.querySelectorAll('#sc-table tbody tr');
    val = val.toLowerCase();
    rows.forEach(function(r){
        r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
}
</script>
</body>
</html>