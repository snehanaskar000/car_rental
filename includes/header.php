<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_url = "/car_rental/";
$current_page = basename($_SERVER['PHP_SELF']);

// Helper to check active link
function is_active($page) {
    global $current_page;
    return strpos($current_page, $page) !== false ? 'active' : '';
}
?>

<style>
    :root {
        --gold: #F59E0B;
        --gold-hover: #D97706;
        --dark-bg: #020617;
        --nav-bg: rgba(15, 23, 42, 0.85);
        --text-main: #E5E7EB;
        --text-gray: #94A3B8;
        --border-subtle: rgba(255, 255, 255, 0.08);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* THE FIX: Add padding to body so content starts below the fixed nav */
    body {
        padding-top: 80px; 
        margin: 0;
    }

    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 5%;
        height: 80px; /* Consistent height */
        background: var(--nav-bg);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--border-subtle);
        
        /* THE FIX: Fixed positioning */
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 9999; 
        transition: var(--transition);
        box-sizing: border-box;
    }

    /* Optional: Shorter nav when scrolling */
    .navbar.scrolled {
        height: 70px;
        background: rgba(15, 23, 42, 0.95);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .logo a { font-size: 1.5rem; font-weight: 800; color: var(--gold); text-decoration: none; display: flex; align-items: center; gap: 8px; }

    .nav-links { display: flex; gap: 28px; list-style: none; margin: 0; padding: 0; }
    .nav-links a { color: var(--text-main); text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: var(--transition); position: relative; padding: 5px 0; }
    .nav-links a:hover, .nav-links a.active { color: var(--gold); }
    
    .nav-links a.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--gold);
    }

    .nav-right { display: flex; align-items: center; gap: 14px; }
    .user-tag { color: var(--text-gray); font-size: 0.85rem; background: rgba(255,255,255,0.05); padding: 5px 12px; border-radius: 20px; border: 1px solid var(--border-subtle); }
    .btn-nav { padding: 8px 20px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn-gold { background: var(--gold); color: #000; }
    .btn-gold:hover { background: var(--gold-hover); }
    .btn-outline { background: transparent; border: 1px solid var(--gold); color: var(--gold); }
    .btn-outline:hover { background: var(--gold); color: #000; }
    .btn-logout { color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 600; }
</style>

<nav class="navbar" id="mainNav">
    <div class="logo">
        <a href="<?php echo $base_url; ?>index.php">🚗 CAR<span style="color:#E5E7EB">RENTAL</span></a>
    </div>

    <ul class="nav-links">
        <?php if(isset($_SESSION['user'])): ?>
            <li><a href="<?php echo $base_url; ?>index.php" class="<?= is_active('index') ?>">Home</a></li>
            <li><a href="<?php echo $base_url; ?>about.php" class="<?= is_active('about') ?>">About</a></li>
            <li><a href="<?php echo $base_url; ?>users/cars.php" class="<?= is_active('cars') ?>">Find Cars</a></li>
            <li><a href="<?php echo $base_url; ?>users/my_bookings.php" class="<?= is_active('my_bookings') ?>">My Bookings</a></li>
            <li><a href="<?php echo $base_url; ?>contact.php" class="<?= is_active('contact') ?>">Contact</a></li>
        
        <?php elseif(isset($_SESSION['admin'])): ?>
            <li><a href="<?php echo $base_url; ?>admin/dashboard.php" class="<?= is_active('dashboard') ?>">Dashboard</a></li>
            <li><a href="<?php echo $base_url; ?>admin/manage_cars.php" class="<?= is_active('manage_cars') ?>">Manage Cars</a></li>
            <li><a href="<?php echo $base_url; ?>admin/bookings.php" class="<?= is_active('bookings') ?>">Bookings</a></li>
        
        <?php else: ?>
            <li><a href="<?php echo $base_url; ?>contact.php" class="<?= is_active('contact') ?>">Contact Us</a></li>
        <?php endif; ?>
    </ul>

    <div class="nav-right">
        <?php if(isset($_SESSION['user'])): ?>
            <div class="user-tag">👤 <?= htmlspecialchars(explode('@', $_SESSION['user'])[0]) ?></div>
            <a href="<?php echo $base_url; ?>logout.php" class="btn-logout">Logout</a>

        <?php elseif(isset($_SESSION['admin'])): ?>
            <div class="user-tag" style="border-color: var(--gold); color: var(--gold);">🛡️ Admin</div>
            <a href="<?php echo $base_url; ?>logout.php" class="btn-logout">Logout</a>

        <?php else: ?>
            <a href="<?php echo $base_url; ?>login.php" class="btn-nav btn-outline">Login</a>
            <a href="<?php echo $base_url; ?>register.php" class="btn-nav btn-gold">Register</a>
        <?php endif; ?>
    </div>
</nav>

<script>
    // Handle scroll effect
    window.addEventListener('scroll', () => {
        const nav = document.getElementById('mainNav');
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
</script>