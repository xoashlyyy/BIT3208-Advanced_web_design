<?php
// Relative path to match your week_7 placement rule
require '../week_7/includes/db.php'; 
$result = $conn->query("SELECT product_name, sku, quantity, price FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Catalog Showcase | StockTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #0f172a;     /* Matching your clean index.php home style look */
            --bg-card: #1e293b;
            --text-main: #fff;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --border: rgba(255,255,255,0.05);
            --ok: #10b981;
            --err: #ef4444;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); padding: 50px 5%; }

        header { text-align: center; max-width: 700px; margin: 0 auto 48px; }
        header h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -1px; }
        header p { color: var(--text-muted); font-size: 1.05rem; line-height: 1.5; }

        /* ── MOBILE-FIRST CSS GRID (Default: 1 Column per row) ── */
        .showcase-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }

        .image-placeholder {
            height: 180px;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: rgba(255,255,255,0.05);
            border-bottom: 1px solid var(--border);
        }
        
        /* Enforces responsive item image behavior if asset tokens are present */
        .showcase-img { width: 100%; height: 100%; object-fit: cover; max-width: 100%; }

        .badge-tag {
            position: absolute; top: 12px; right: 12px;
            padding: 4px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; color: #fff;
        }

        .info-block { padding: 20px; display: flex; flex-direction: column; flex: 1; }
        .sku { font-family: monospace; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
        .title { font-size: 1.1rem; font-weight: 700; margin-bottom: 14px; color: #fff; }
        
        .footer-row {
            margin-top: auto; padding-top: 14px;
            border-top: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .price { font-size: 1.3rem; font-weight: 800; color: var(--primary); }

        /* ── BREAKPOINT 1: TABLET VIEW (min-width: 600px -> 2 Columns) ── */
        @media (min-width: 600px) {
            .showcase-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* ── BREAKPOINT 2: DESKTOP VIEW (min-width: 1024px -> 4 Columns) ── */
        @media (min-width: 1024px) {
            .showcase-grid { grid-template-columns: repeat(4, 1fr); }
        }
    </style>
</head>
<body>

    <header>
        <h1>StockTrack Public Catalog</h1>
        <p>Live, responsive inventory window executing real-time grid rendering directly from your operational warehouse table arrays.</p>
    </header>

    <div class="showcase-grid">
        <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): 
            // Dynamic display iconography selection based on your SQL seeded product names
            $icon = "fa-laptop";
            if(strpos($row['sku'], 'KEY') !== false) $icon = "fa-keyboard";
            if(strpos($row['sku'], 'LOGI') !== false) $icon = "fa-computer-mouse";
        ?>
            <div class="product-card">
                <div class="image-placeholder">
                    <i class="fa-solid <?php echo $icon; ?>"></i>
                </div>
                
                <?php if($row['quantity'] == 0): ?>
                    <div class="badge-tag" style="background: var(--err);">Out of Stock</div>
                <?php elseif($row['quantity'] < 5): ?>
                    <div class="badge-tag" style="background: #f59e0b;">Low Stock (<?php echo $row['quantity']; ?>)</div>
                <?php else: ?>
                    <div class="badge-tag" style="background: var(--ok);"><?php echo $row['quantity']; ?> Available</div>
                <?php endif; ?>

                <div class="info-block">
                    <span class="sku"><?php echo htmlspecialchars($row['sku']); ?></span>
                    <div class="title"><?php echo htmlspecialchars($row['product_name']); ?></div>
                    
                    <div class="footer-row">
                        <span class="price">$<?php echo number_format($row['price'], 2); ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <p style="grid-column: 1 / -1; text-align: center; color: var(--text-muted);">No items returned from database initialization.</p>
        <?php endif; ?>
    </div>

</body>
</html>