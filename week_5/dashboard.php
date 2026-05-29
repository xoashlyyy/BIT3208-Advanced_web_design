<?php
session_start();
require 'includes/db.php';

// Strict Session Authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// 1. BACKEND CRUD LOGIC (Post-Redirect-Get Pattern)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // CREATE: Add Product
    if ($action === 'add') {
        $name = trim(htmlspecialchars($_POST['product_name']));
        $sku = trim(htmlspecialchars(strtoupper($_POST['sku'])));
        $qty = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        $stmt = $conn->prepare("INSERT INTO products (product_name, sku, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssid", $name, $sku, $qty, $price);
        
        if ($stmt->execute()) $_SESSION['flash_success'] = "Product added successfully.";
        else $_SESSION['flash_error'] = "Failed to add product. SKU might already exist.";
    } 
    // UPDATE: Edit Product
    elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = trim(htmlspecialchars($_POST['product_name']));
        $sku = trim(htmlspecialchars(strtoupper($_POST['sku'])));
        $qty = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        $stmt = $conn->prepare("UPDATE products SET product_name=?, sku=?, quantity=?, price=? WHERE id=?");
        $stmt->bind_param("ssidi", $name, $sku, $qty, $price, $id);
        
        if ($stmt->execute()) $_SESSION['flash_success'] = "Product updated successfully.";
        else $_SESSION['flash_error'] = "Failed to update. Check for duplicate SKUs.";
    }
    // DELETE: Remove Product
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) $_SESSION['flash_success'] = "Product deleted.";
        else $_SESSION['flash_error'] = "Failed to delete product.";
    }

    // Redirect to prevent "Confirm Form Resubmission" popups on refresh
    header("Location: dashboard.php");
    exit();
}

// ---------------------------------------------------------
// 2. FETCH DATA FOR UI
// ---------------------------------------------------------
$result = $conn->query("SELECT id, product_name, sku, quantity, price, last_updated FROM products ORDER BY id DESC");

$total_products = $result->num_rows;
$total_value = 0;
$low_stock_count = 0;
$products = [];

while($row = $result->fetch_assoc()) {
    $products[] = $row;
    $total_value += ($row['quantity'] * $row['price']);
    if ($row['quantity'] < 5) $low_stock_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: var(--bg-surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border); }
        .stat-card span { color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; display: block; margin-bottom: 8px; }
        .stat-card strong { font-size: 2rem; color: var(--text-main); }
        
        .badge { padding: 4px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge.in-stock { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge.low-stock { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge.out-stock { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

        /* Modal Styles */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: var(--bg-surface); padding: 32px; border-radius: 12px; width: 100%; max-width: 450px; border: 1px solid var(--border); position: relative; }
        .close-btn { position: absolute; top: 16px; right: 16px; background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; width: auto; padding: 0; }
        .close-btn:hover { color: var(--text-main); }
        
        /* Action buttons in table */
        .btn-text { background: none; border: none; padding: 0; color: var(--primary); font-size: 0.875rem; font-weight: 500; cursor: pointer; margin-right: 12px; display: inline; width: auto; }
        .btn-text:hover { background: none; text-decoration: underline; }
        .btn-text.delete { color: var(--danger); }
    </style>
</head>
<body class="flex-row-layout">
    
    <div class="sidebar">
        <h2 style="margin-bottom: 40px; font-weight: 700; color: #fff;">StockTrack.</h2>
        <h3>Warehouse</h3>
        <a href="dashboard.php" class="active">Inventory Overview</a>
        <a href="#" onclick="openModal('addModal')">Add New Product</a>
        
        <h3 style="margin-top: 24px;">Account</h3>
        <a href="login.php?logout=1" class="logout">Sign Out</a>
    </div>

    <div class="main-content">
        <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.8rem; margin-bottom: 4px;">Warehouse Overview</h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <button onclick="openModal('addModal')" style="width: auto; padding: 10px 20px;">+ Add Product</button>
        </div>

        <?php 
        if (isset($_SESSION['flash_success'])) {
            echo "<div class='msg' style='background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2);'>{$_SESSION['flash_success']}</div>";
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            echo "<div class='msg error'>{$_SESSION['flash_error']}</div>";
            unset($_SESSION['flash_error']);
        }
        ?>

        <div class="stat-grid">
            <div class="stat-card">
                <span>Unique Items</span>
                <strong><?php echo $total_products; ?></strong>
            </div>
            <div class="stat-card">
                <span>Low Stock Alerts</span>
                <strong style="color: <?php echo $low_stock_count > 0 ? '#ef4444' : 'inherit'; ?>;"><?php echo $low_stock_count; ?></strong>
            </div>
            <div class="stat-card">
                <span>Total Asset Value</span>
                <strong>$<?php echo number_format($total_value, 2); ?></strong>
            </div>
        </div>

        <div class="data-card">
            <table>
                <tr>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th>Status</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
                <?php foreach($products as $item): ?>
                <tr>
                    <td style="font-family: monospace; color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($item['sku']); ?></td>
                    <td style="font-weight: 500;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>
                        <?php if($item['quantity'] == 0): ?> <span class="badge out-stock">Out of Stock</span>
                        <?php elseif($item['quantity'] < 5): ?> <span class="badge low-stock">Low Stock</span>
                        <?php else: ?> <span class="badge in-stock">In Stock</span> <?php endif; ?>
                    </td>
                    <td style="font-weight: 600;"><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td class="action-links">
                        <button class="btn-text" onclick="openEditModal(<?php echo $item['id']; ?>, '<?php echo addslashes($item['product_name']); ?>', '<?php echo addslashes($item['sku']); ?>', <?php echo $item['quantity']; ?>, <?php echo $item['price']; ?>)">Edit</button>
                        
                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Permanently delete this product?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-text delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
            <h2 style="margin-bottom: 20px;">Add New Product</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <label>Product Name</label>
                <input type="text" name="product_name" required>
                
                <label>SKU</label>
                <input type="text" name="sku" required>
                
                <div style="display: flex; gap: 16px;">
                    <div style="flex: 1;"><label>Quantity</label><input type="number" name="quantity" min="0" required></div>
                    <div style="flex: 1;"><label>Price ($)</label><input type="number" name="price" step="0.01" min="0" required></div>
                </div>
                <button type="submit">Save Product</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
            <h2 style="margin-bottom: 20px;">Edit Product</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <label>Product Name</label>
                <input type="text" name="product_name" id="edit_name" required>
                
                <label>SKU</label>
                <input type="text" name="sku" id="edit_sku" required>
                
                <div style="display: flex; gap: 16px;">
                    <div style="flex: 1;"><label>Quantity</label><input type="number" name="quantity" id="edit_qty" min="0" required></div>
                    <div style="flex: 1;"><label>Price ($)</label><input type="number" name="price" id="edit_price" step="0.01" min="0" required></div>
                </div>
                <button type="submit">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        function openEditModal(id, name, sku, qty, price) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_sku').value = sku;
            document.getElementById('edit_qty').value = qty;
            document.getElementById('edit_price').value = price;
            openModal('editModal');
        }
    </script>
</body>
</html>