<?php
session_start();
require 'includes/db.php';

// Strict Session Authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and format inputs
    $product_name = trim(htmlspecialchars($_POST['product_name']));
    $sku = trim(htmlspecialchars(strtoupper($_POST['sku']))); // Force SKUs to uppercase
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    // Basic Validation
    if ($quantity < 0 || $price < 0) {
        $error = "Quantity and Price cannot be negative numbers.";
    } else {
        // Check for duplicate SKU to prevent SQL errors
        $check_stmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $check_stmt->bind_param("s", $sku);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "A product with SKU '$sku' already exists in the system.";
        } else {
            // Insert the new product securely
            $insert_stmt = $conn->prepare("INSERT INTO products (product_name, sku, quantity, price) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssid", $product_name, $sku, $quantity, $price);
            
            if ($insert_stmt->execute()) {
                $success = "Product successfully added to inventory.";
                // Clear POST data so refreshing doesn't resubmit
                $_POST = array(); 
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
    <title>Add Product | StockTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 500px;">
            <h2>Add New Product</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 24px; margin-top: -10px;">
                Register a new SKU into the warehouse.
            </p>

            <?php 
                if($error) echo "<div class='msg error'>$error</div>"; 
                if($success) echo "<div class='msg' style='background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);'>$success</div>"; 
            ?>
            
            <form method="POST" action="">
                <label>Product Name</label>
                <input type="text" name="product_name" placeholder="e.g., Dell XPS 15" required 
                       value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">
                
                <label>SKU (Stock Keeping Unit)</label>
                <input type="text" name="sku" placeholder="e.g., DELL-XPS-15" required
                       value="<?php echo isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : ''; ?>">
                
                <div style="display: flex; gap: 16px;">
                    <div style="flex: 1;">
                        <label>Initial Quantity</label>
                        <input type="number" name="quantity" min="0" placeholder="0" required
                               value="<?php echo isset($_POST['quantity']) ? $_POST['quantity'] : ''; ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>Unit Price ($)</label>
                        <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required
                               value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" style="margin-top: 10px;">Add to Inventory</button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; font-size: 0.875rem;">
                <a href="dashboard.php" style="color: var(--text-muted);">&larr; Back to Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>