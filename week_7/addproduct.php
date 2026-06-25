<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

require_role('stock_clerk');

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name = clean('product_name');
    $sku          = strtoupper(clean('sku'));
    $quantity     = max(0, intval($_POST['quantity'] ?? 0));
    $price        = has_role('manager') ? max(0, floatval($_POST['price'] ?? 0)) : 0.00;

    if ($product_name === '' || $sku === '') {
        $error = "Product name and SKU are required.";
    } else {
        $check = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $check->bind_param("s", $sku);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "A product with SKU '{$sku}' already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (product_name, sku, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssid", $product_name, $sku, $quantity, $price);

            if ($stmt->execute()) {
                $success = "Product successfully added to inventory.";
                $_POST   = [];
            } else {
                $error = "Failed to add product due to a system error.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width:500px;">
            <h2>Add New Product</h2>
            <p style="text-align:center; color:var(--text-muted); margin-bottom:24px; margin-top:-10px;">
                Register a new SKU into the warehouse.
            </p>

            <?php if ($error): ?>
                <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="msg" style="background:rgba(16,185,129,.1); color:#10b981; border:1px solid rgba(16,185,129,.2);">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" placeholder="e.g., Dell XPS 15" required
                       value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">

                <label for="sku">SKU</label>
                <input type="text" id="sku" name="sku" placeholder="e.g., DELL-XPS-15" required
                       value="<?php echo isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : ''; ?>">

                <div style="display:flex; gap:16px;">
                    <div style="flex:1;">
                        <label for="quantity">Initial Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="0" placeholder="0" required
                               value="<?php echo isset($_POST['quantity']) ? (int)$_POST['quantity'] : '0'; ?>">
                    </div>
                    <div style="flex:1;">
                        <label for="price">Unit Price ($)</label>
                        <?php if (has_role('manager')): ?>
                            <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00" required
                                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                        <?php else: ?>
                            <input type="number" id="price" name="price" value="0" readonly
                                   style="background:rgba(255,255,255,0.03); color:var(--text-muted); cursor:not-allowed;"
                                   title="Price can only be set by a Manager or above">
                            <small style="color:var(--text-muted); display:block; margin-top:-14px; margin-bottom:14px; font-size:0.75rem;">
                                Locked — Manager required
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" style="margin-top:10px;">Add to Inventory</button>
            </form>

            <p style="text-align:center; margin-top:24px; font-size:0.875rem;">
                <a href="dashboard.php" style="color:var(--text-muted);">&larr; Back to Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>