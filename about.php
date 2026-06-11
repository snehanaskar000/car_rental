<?php
session_start();
include("includes/db.php");
include("includes/header.php");

// 1. DYNAMIC STATISTICS LOGIC
$total_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars"))['count'] ?? 0;
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'] ?? 0;
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'] ?? 0;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    :root {
        --gold: #F59E0B;
        --gold-hover: #D97706;
        --dark-bg: #020617;
        --card-bg: #1e293b;
        --text-gray: #94A3B8;
    }

    body { background: var(--dark-bg); color: white; line-height: 1.6; }

    /* Hero Section */
    .about-hero {
        padding: 120px 5% 80px;
        text-align: center;
        background: linear-gradient(rgba(2,6,23,0.85), rgba(2,6,23,0.85)), 
                    url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1500') center/cover;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .about-hero h1 { font-size: 3.5rem; margin-bottom: 15px; font-weight: 800; }
    .about-hero h1 span { color: var(--gold); }
    .about-hero p { color: var(--text-gray); max-width: 600px; margin: 0 auto; font-size: 1.1rem; }

    /* Stats Grid */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        max-width: 1100px;
        margin: -50px auto 60px;
        padding: 0 20px;
    }

    .stat-card {
        background: var(--card-bg);
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transition: 0.3s;
    }

    .stat-card:hover { transform: translateY(-5px); border-color: var(--gold); }
    .stat-card i { font-size: 30px; color: var(--gold); margin-bottom: 15px; }
    .stat-card h3 { font-size: 2.2rem; margin-bottom: 5px; }
    .stat-card p { color: var(--text-gray); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }

    /* Content Section */
    .content-section {
        max-width: 1100px;
        margin: 0 auto 100px;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .content-img img {
        width: 100%;
        border-radius: 20px;
        border: 1px solid var(--gold);
        box-shadow: 0 20px 40px rgba(245, 158, 11, 0.1);
    }

    .text-box h2 { font-size: 2.2rem; margin-bottom: 20px; }
    .text-box h2 span { color: var(--gold); }
    .text-box p { color: var(--text-gray); margin-bottom: 20px; font-size: 16px; }

    .feature-list { list-style: none; padding: 0; }
    .feature-list li { margin-bottom: 12px; display: flex; align-items: center; gap: 10px; color: #e2e8f0; }
    .feature-list i { color: var(--gold); }

    /* CTA Section */
    .cta-box {
        background: linear-gradient(rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.02));
        padding: 60px 20px;
        text-align: center;
        border-radius: 30px;
        margin: 0 5% 100px;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .btn-about {
        display: inline-block;
        padding: 15px 40px;
        background: var(--gold);
        color: black;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 700;
        margin-top: 25px;
        transition: 0.3s;
    }

    .btn-about:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3); }

    @media (max-width: 768px) {
        .content-section { grid-template-columns: 1fr; text-align: center; }
        .feature-list li { justify-content: center; }
        .about-hero h1 { font-size: 2.5rem; }
    }
</style>

<section class="about-hero">
    <h1>Our <span>Journey</span></h1>
    <p>Redefining luxury travel since 2014. We provide more than just a rental; we provide an experience.</p>
</section>

<div class="stats-container" id="statsSection">
    <div class="stat-card">
        <i class="fas fa-car"></i>
        <h3 class="counter" data-target="<?= $total_cars ?>">0</h3>
        <p>Premium Fleet</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <h3 class="counter" data-target="<?= $total_users ?>">0</h3>
        <p>Happy Clients</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-calendar-check"></i>
        <h3 class="counter" data-target="<?= $total_bookings ?>">0</h3>
        <p>Trips Completed</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-award"></i>
        <h3 class="counter" data-target="12">0</h3>
        <p>Years Excellence</p>
    </div>
</div>

<section class="content-section">
    <div class="content-img">
        <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=800" alt="About CarRental">
    </div>
    <div class="text-box">
        <h2>Why Choose <span>CarRental?</span></h2>
        <p>We started with a simple mission: to make premium car rentals accessible and transparent. Today, we are Karimpur's leading choice for business and leisure travel.</p>
        
        <ul class="feature-list">
            <li><i class="fas fa-check-circle"></i> 24/7 Roadside Assistance</li>
            <li><i class="fas fa-check-circle"></i> Fully Insured Vehicles</li>
            <li><i class="fas fa-check-circle"></i> No Hidden Charges or Fees</li>
            <li><i class="fas fa-check-circle"></i> Flexible Pick-up & Drop-off</li>
        </ul>
    </div>
</section>

<section class="content-section" style="direction: rtl;">
    <div class="content-img" style="direction: ltr;">
        <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=800" alt="Our Mission">
    </div>
    <div class="text-box" style="direction: ltr;">
        <h2>Our <span>Mission</span></h2>
        <p>To deliver seamless, safe, and sophisticated transportation solutions. We believe every journey should be as memorable as the destination.</p>
        <p>Our vision is to become the most trusted car rental brand in West Bengal by focusing on customer satisfaction and technological innovation.</p>
    </div>
</section>

<div class="cta-box">
    <h2>Ready to Drive Your <span>Dream Car?</span></h2>
    <p>Join thousands of happy travelers and book your ride in less than 2 minutes.</p>
    <a href="<?php echo $base_url; ?>users/cars.php" class="btn-about">Browse Our Fleet</a>
</div>

<script>
    // Optimized Counter Logic with Scroll Detection
    const counters = document.querySelectorAll('.counter');
    const speed = 200; 

    const startCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target + "+";
                }
            };
            updateCount();
        });
    };

    // Intersection Observer to trigger when visible
    const observer = new IntersectionObserver((entries) => {
        if(entries[0].isIntersecting) {
            startCounters();
            observer.disconnect(); // Run only once
        }
    }, { threshold: 0.5 });

    observer.observe(document.getElementById('statsSection'));
</script>

<?php include("includes/footer.php"); ?>