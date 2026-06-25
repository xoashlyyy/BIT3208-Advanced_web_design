<?php
require 'includes/db.php';
// Public storefront window view — does not require authorization to watch catalog items
$result = $conn->query("SELECT product_name, sku, quantity, price FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Storefront Showcase</title>
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --primary: #6366f1;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-body); color: var(--text-dark); padding: 40px 5%; }

        header { text-align: center; margin-bottom: 48px; }
        header h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -1px; }
        header p { color: var(--text-muted); font-size: 1.05rem; }

        /* ── MOBILE-FIRST CSS GRID (Default: 1 Column per Row) ── */
        .showcase-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Card Container layout elements */
        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.05);
        }

        /* Image Display wrappers */
        .image-wrapper {
            background: #e2e8f0;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .showcase-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            max-width: 100%; /* Enforces responsive image scaling bounds */
        }
        .stock-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #fff;
        }

        /* Descriptive Content Details block */
        .product-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .sku-label { font-size: 0.75rem; font-family: monospace; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; }
        .product-name { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
        .product-desc { font-size: 0.875rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px; }
        
        .card-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }
        .price-tag { font-size: 1.25rem; font-weight: 800; color: var(--primary); }

        /* ── TABLET BREAKPOINT (min-width: 600px -> 2 Columns per Row) ── */
        @media (min-width: 600px) {
            .showcase-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* ── DESKTOP BREAKPOINT (min-width: 1024px -> 4 Columns per Row) ── */
        @media (min-width: 1024px) {
            .showcase-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>StockTrack Storefront Showcase</h1>
        <p>Live inventory view pulled dynamically straight from the database cluster.</p>
    </header>

    <div class="showcase-grid">
        <?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): 
            // Fallback product description string values since database schema focuses heavily on tracking logistics parameters
            $desc = "High performance model with standard enterprise deployment specs. Optimized for speed and endurance.";
            $img_url = "https://images.unsplash.com/photo-1531297484001-80022131f5a1?auto=format&fit=crop&w=400&q=80";

            // Update dummy placeholder display parameters for specific inventory matches
            if(strpos($row['sku'], 'MBP') !== false) $img_url = "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=400&q=80";
            if(strpos($row['sku'], 'KEY') !== false) $img_url = "https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=400&q=80";
            if(strpos($row['sku'], 'LOGI') !== false) $img_url = "https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=400&q=80";
        ?>
            <div class="product-card">
                <div class="image-wrapper">
                    <img src="<?php echo $img_url; ?>" alt="Product Rendering" class="showcase-img">
                    <?php if($row['quantity'] == 0): ?>
                        <div class="stock-tag" style="background: var(--accent-red);">Out of Stock</div>
                    <?php else: ?>
                        <div class="stock-tag" style="background: var(--accent-green);"><?php echo $row['quantity']; ?> Units</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="sku-label"><?php echo htmlspecialchars($row['sku']); ?></span>
                    <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                    <div class="product-desc"><?php echo $desc; ?></div>
                    <div class="card-footer">
                        <span class="price-tag">$<?php echo number_format($row['price'], 2); ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <p style="text-align:center; grid-column: 1 / -1; color: var(--text-muted);">The product inventory table is empty.</p>
        <?php endif; ?>
    </div>

</body>
</html>