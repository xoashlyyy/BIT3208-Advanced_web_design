<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockTrack | Modern Inventory OS</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: block; background: #0f172a; overflow-x: hidden; }
        
        /* Modern Navbar */
        .saas-nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 5%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .saas-nav .brand { font-size: 1.5rem; font-weight: 700; color: #fff; letter-spacing: -0.5px; }
        .saas-nav .nav-right { display: flex; align-items: center; gap: 24px; }
        .saas-nav .nav-right .login-link { color: #94a3b8; font-size: 0.9rem; font-weight: 500; transition: color 0.2s; }
        .saas-nav .nav-right .login-link:hover { color: #fff; }
        .saas-nav .nav-right .btn-signup { background: #fff; color: #0f172a; padding: 10px 20px; border-radius: 999px; font-weight: 600; font-size: 0.9rem; transition: transform 0.2s; }
        .saas-nav .nav-right .btn-signup:hover { transform: translateY(-1px); background: #f8fafc; opacity: 1; }

        /* Hero Section */
        .hero { padding: 120px 5% 80px; text-align: center; max-width: 900px; margin: 0 auto; position: relative; }
        .hero::before { content: ''; position: absolute; top: -50%; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(15, 23, 42, 0) 70%); z-index: -1; }
        
        .hero h1 { font-size: clamp(3rem, 8vw, 4.5rem); font-weight: 800; line-height: 1.1; letter-spacing: -2px; margin-bottom: 24px; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 1.25rem; color: #94a3b8; margin-bottom: 40px; line-height: 1.6; max-width: 650px; margin-left: auto; margin-right: auto; }

        /* Conversion CTA Form */
        .cta-form { display: flex; gap: 12px; justify-content: center; max-width: 480px; margin: 0 auto; position: relative; }
        .cta-form input { margin: 0; padding: 16px 24px; border-radius: 999px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 1rem; flex: 1; transition: border 0.2s, background 0.2s; }
        .cta-form input:focus { border-color: #6366f1; background: rgba(255,255,255,0.05); }
        .cta-form button { width: auto; padding: 16px 32px; border-radius: 999px; background: #6366f1; font-weight: 600; margin: 0; white-space: nowrap; }
        .cta-form button:hover { background: #4f46e5; }
        .microcopy { margin-top: 16px; font-size: 0.8rem; color: #64748b; }
    </style>
</head>
<body>

    <nav class="saas-nav">
        <div class="brand">StockTrack.</div>
        <div class="nav-right">
            <a href="login.php" class="login-link">Sign in</a>
            <a href="register.php" class="btn-signup">Get Started</a>
        </div>
    </nav>

    <header class="hero">
        <h1>Inventory control, simplified.</h1>
        <p>Stop tracking stock in messy spreadsheets. Monitor your products, manage SKUs, and prevent stockouts in real-time with an elegant, lightning-fast dashboard.</p>
        
        <form action="register.php" method="GET" class="cta-form">
            <input type="text" name="email_forward" placeholder="Enter your email address..." required>
            <button type="submit">Start Tracking</button>
        </form>
        <p class="microcopy">Deploy your warehouse backend in seconds.</p>
    </header>

</body>
</html>