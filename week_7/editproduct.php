<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

require_role('stock_clerk');

$error   = "";
$product = null;

// ── HANDLE POST ───────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id  = intval($_POST['id'] ?? 0);
    $qty = max(0, intval($_POST['quantity'] ?? 0));

    if ($id <= 0) {
        header("Location: dashboard.php");
        exit();
    }

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

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Failed to update. Check for a duplicate SKU.";
        // Re-fetch so the form still renders
        $id = intval($_POST['id']);
    }
}

// ── FETCH PRODUCT ─────────────────────────────────────────────────────────────
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id > 0) {
    $fetch = $conn->prepare("SELECT id, product_name, sku, quantity, price FROM products WHERE id=?");
    $fetch->bind_param("i", $id);
    $fetch->execute();
    $product = $fetch->get_result()->fetch_assoc();
}

if (!$product) {
    // Product not found — go back
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width:500px;">
            <h2><?php echo has_role('manager') ? 'Edit Product' : 'Update Stock Quantity'; ?></h2>

            <?php if ($error): ?>
                <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">

                <label>Product Name</label>
                <input type="text" name="product_name"
                       value="<?php echo htmlspecialchars($product['product_name']); ?>"
                       <?php if (!has_role('manager')) echo 'readonly style="background:rgba(255,255,255,0.03); color:var(--text-muted); cursor:not-allowed;"'; ?>
                       required>

                <label>SKU</label>
                <input type="text" name="sku"
                       value="<?php echo htmlspecialchars($product['sku']); ?>"
                       <?php if (!has_role('manager')) echo 'readonly style="background:rgba(255,255,255,0.03); color:var(--text-muted); cursor:not-allowed;"'; ?>
                       required>

                <div style="display:flex; gap:16px;">
                    <div style="flex:1;">
                        <label>Quantity</label>
                        <input type="number" name="quantity" min="0"
                               value="<?php echo (int)$product['quantity']; ?>" required>
                    </div>
                    <div style="flex:1;">
                        <label>Price ($)</label>
                        <input type="number" name="price" step="0.01" min="0"
                               value="<?php echo number_format($product['price'], 2, '.', ''); ?>"
                               <?php if (!has_role('manager')) echo 'readonly style="background:rgba(255,255,255,0.03); color:var(--text-muted); cursor:not-allowed;"'; ?>
                               required>
                    </div>
                </div>

                <button type="submit">Save Changes</button>
            </form>

            <p style="text-align:center; margin-top:24px; font-size:0.875rem;">
                <a href="dashboard.php" style="color:var(--text-muted);">&larr; Cancel</a>
            </p>
        </div>
    </div>
</body>
</html>