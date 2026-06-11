<?php
// Get current year
$current_year = date('Y');

// Get some stats (optional - only if database connection exists)
if(isset($conn)){
    $total_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars"))['count'] ?? 0;
    $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'] ?? 0;
} else {
    $total_cars = 0;
    $total_users = 0;
}
?>

<style>
    /* ========== FOOTER STYLES ========== */
    .footer {
        background: #0f172a;
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 80px 5% 0;
        margin-top: 100px;
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Footer Grid */
    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 50px;
        padding-bottom: 50px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    /* Footer Brand */
    .footer-brand h3 {
        font-size: 26px;
        font-weight: 800;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
    }

    .footer-brand h3 i { color: #F59E0B; font-size: 28px; }
    .footer-brand h3 span { color: #F59E0B; }

    .footer-brand p {
        color: #94A3B8;
        font-size: 15px;
        line-height: 1.8;
        margin-bottom: 25px;
    }

    /* Newsletter */
    .newsletter {
        margin-top: 25px;
    }

    .newsletter h4 {
        font-size: 16px;
        margin-bottom: 15px;
        color: #fff;
    }

    .newsletter-form {
        display: flex;
        gap: 10px;
    }

    .newsletter-form input {
        flex: 1;
        padding: 12px 15px;
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
    }

    .newsletter-form input:focus {
        outline: none;
        border-color: #F59E0B;
    }

    .newsletter-form button {
        padding: 12px 20px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border: none;
        border-radius: 8px;
        color: #000;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .newsletter-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(245,158,11,0.4);
    }

    /* Footer Column */
    .footer-column h4 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 25px;
        color: #fff;
    }

    .footer-column ul {
        list-style: none;
    }

    .footer-column li {
        margin-bottom: 14px;
    }

    .footer-column a {
        color: #94A3B8;
        text-decoration: none;
        font-size: 15px;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .footer-column a:hover {
        color: #F59E0B;
        padding-left: 5px;
    }

    .footer-column a i {
        font-size: 12px;
        color: #F59E0B;
    }

    /* Contact Info */
    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
    }

    .contact-icon {
        width: 40px;
        height: 40px;
        background: rgba(245,158,11,0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #F59E0B;
        flex-shrink: 0;
    }

    .contact-info h5 {
        font-size: 13px;
        color: #94A3B8;
        margin-bottom: 3px;
        font-weight: 500;
    }

    .contact-info p {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
    }

    .contact-info a {
        color: #fff;
        text-decoration: none;
        transition: 0.3s;
    }

    .contact-info a:hover { color: #F59E0B; }

    /* Social Media */
    .footer-social {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .footer-social a {
        width: 45px;
        height: 45px;
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94A3B8;
        font-size: 18px;
        text-decoration: none;
        transition: 0.3s;
    }

    .footer-social a:hover {
        background: #F59E0B;
        border-color: #F59E0B;
        color: #000;
        transform: translateY(-3px);
    }

    /* Stats Bar */
    .footer-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        padding: 40px 0;
        margin-bottom: 40px;
    }

    .stat-box {
        text-align: center;
        padding: 25px;
        background: #1e293b;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .stat-box i {
        font-size: 28px;
        color: #F59E0B;
        margin-bottom: 12px;
    }

    .stat-box h3 {
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 5px;
    }

    .stat-box p {
        color: #94A3B8;
        font-size: 14px;
    }

    /* Footer Bottom */
    .footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30px 0;
        color: #94A3B8;
        font-size: 14px;
    }

    .footer-bottom span { color: #F59E0B; font-weight: 600; }

    .footer-links {
        display: flex;
        gap: 25px;
    }

    .footer-links a {
        color: #94A3B8;
        text-decoration: none;
        transition: 0.3s;
    }

    .footer-links a:hover { color: #F59E0B; }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: 0.3s;
        z-index: 999;
        box-shadow: 0 5px 20px rgba(245,158,11,0.3);
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(245,158,11,0.5);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .footer-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
        .footer-stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .footer { padding: 60px 5% 0; }
        .footer-grid { grid-template-columns: 1fr; gap: 30px; text-align: center; }
        .footer-brand h3 { justify-content: center; }
        .footer-column a { justify-content: center; }
        .footer-social { justify-content: center; }
        .contact-item { justify-content: center; }
        .footer-stats { grid-template-columns: 1fr; }
        .footer-bottom { flex-direction: column; gap: 15px; text-align: center; }
        .footer-links { flex-direction: column; gap: 10px; }
        .newsletter-form { flex-direction: column; }
        .back-to-top { bottom: 20px; right: 20px; width: 45px; height: 45px; }
    }
</style>

<!-- ========== FOOTER ========== -->
<footer class="footer">
    <div class="footer-container">

        <!-- Stats Bar -->
        <div class="footer-stats">
            <div class="stat-box">
                <i class="fas fa-car"></i>
                <h3><?php echo $total_cars; ?>+</h3>
                <p>Premium Cars</p>
            </div>
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_users; ?>+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat-box">
                <i class="fas fa-map-marker-alt"></i>
                <h3>10+</h3>
                <p>City Locations</p>
            </div>
            <div class="stat-box">
                <i class="fas fa-headset"></i>
                <h3>24/7</h3>
                <p>Support Available</p>
            </div>
        </div>

        <!-- Main Footer Content -->
        <div class="footer-grid">
            
            <!-- Brand Column -->
            <div class="footer-brand">
                <h3><i class="fas fa-car-side"></i> CAR<span>RENTAL</span></h3>
                <p>Your trusted partner for premium car rental services. Quality vehicles, competitive prices, and exceptional customer service since 2014.</p>
                
                <div class="footer-social">
                    <a href="https://facebook.com" target="_blank" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>

                <!-- Newsletter -->
                <div class="newsletter">
                    <h4>📧 Subscribe to Newsletter</h4>
                    <form class="newsletter-form" onsubmit="return subscribeNewsletter(event)">
                        <input type="email" placeholder="Enter your email" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                    <li><a href="about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    <li><a href="users/cars.php"><i class="fas fa-chevron-right"></i> Browse Cars</a></li>
                    <?php if(isset($_SESSION['user'])): ?>
                        <li><a href="users/my_bookings.php"><i class="fas fa-chevron-right"></i> My Bookings</a></li>
                    <?php endif; ?>
                    <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                </ul>
            </div>

            <!-- Car Categories -->
            <div class="footer-column">
                <h4>Car Types</h4>
                <ul>
                    <li><a href="users/cars.php?type=SUV"><i class="fas fa-chevron-right"></i> SUV</a></li>
                    <li><a href="users/cars.php?type=Sedan"><i class="fas fa-chevron-right"></i> Sedan</a></li>
                    <li><a href="users/cars.php?type=Hatchback"><i class="fas fa-chevron-right"></i> Hatchback</a></li>
                    <li><a href="users/cars.php?type=Luxury"><i class="fas fa-chevron-right"></i> Luxury</a></li>
                    <li><a href="users/cars.php?type=Sports"><i class="fas fa-chevron-right"></i> Sports</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-column">
                <h4>Contact Us</h4>
                
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-info">
                        <h5>Address</h5>
                        <p>123 Main Street, Kolkata<br>West Bengal, India 700001</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-phone"></i></div>
                    <div class="contact-info">
                        <h5>Phone</h5>
                        <p><a href="tel:+919876543210">+91 98765 43210</a></p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-info">
                        <h5>Email</h5>
                        <p><a href="mailto:info@carrental.com">info@carrental.com</a></p>
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo $current_year; ?> <span>CarRental</span>. All Rights Reserved. Made with ❤️ in India</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#cookies">Cookie Policy</a>
            </div>
        </div>

    </div>
</footer>

<!-- Back to Top Button -->
<div class="back-to-top" id="backToTop" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</div>

<script>
    // Back to Top Button
    window.addEventListener('scroll', function() {
        const backToTop = document.getElementById('backToTop');
        if(window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Newsletter Subscription
    function subscribeNewsletter(e) {
        e.preventDefault();
        const email = e.target.querySelector('input').value;
        
        // Simple alert (you can replace with actual API call)
        alert('✅ Thanks for subscribing!\n\nEmail: ' + email);
        e.target.reset();
        return false;
    }

    // Smooth scroll for footer links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if(href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
</script>