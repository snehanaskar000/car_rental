<?php 
session_start();
include("includes/db.php"); 
include("includes/header.php"); 
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
/* ==========================================
   CSS VARIABLES
   ========================================== */
:root {
    --primary: #F59E0B;
    --primary-dark: #D97706;
    --primary-light: #FCD34D;
    --secondary: #3B82F6;
    --dark: #0f172a;
    --dark-light: #1e293b;
    --dark-lighter: #334155;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --success: #10b981;
    --danger: #ef4444;
    --gradient: linear-gradient(135deg, var(--primary), var(--primary-dark));
    --shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--dark);
    color: var(--text-primary);
    overflow-x: hidden;
}

a {
    text-decoration: none;
    color: inherit;
}

/* ==========================================
   HERO SECTION
   ========================================== */
.hero {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.95)),
                url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-4.0.3') center/cover fixed;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(245, 158, 11, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 900px;
    padding: 20px;
}

.hero-badge {
    display: inline-block;
    background: rgba(245, 158, 11, 0.15);
    color: var(--primary);
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
    border: 1px solid rgba(245, 158, 11, 0.3);
    animation: fadeInDown 0.8s ease;
}

.hero-badge i {
    margin-right: 8px;
}

.hero h1 {
    font-size: 56px;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 20px;
    background: linear-gradient(135deg, #ffffff, #94a3b8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: fadeInUp 0.8s ease 0.2s both;
}

.hero h1 span {
    background: var(--gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 18px;
    color: var(--text-secondary);
    margin-bottom: 40px;
    animation: fadeInUp 0.8s ease 0.4s both;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Search Box */
.search-box {
    background: rgba(30, 41, 59, 0.9);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: var(--shadow);
    animation: fadeInUp 0.8s ease 0.6s both;
}

.search-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-end;
}

.form-group {
    flex: 1;
    min-width: 180px;
    text-align: left;
}

.form-group label {
    display: block;
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.form-group label i {
    margin-right: 5px;
    color: var(--primary);
}

.search-box input,
.search-box select {
    width: 100%;
    padding: 14px 16px;
    background: var(--dark-lighter);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: var(--text-primary);
    font-size: 15px;
    transition: var(--transition);
}

.search-box input:focus,
.search-box select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.search-box select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

.search-box select option {
    background: var(--dark-light);
    color: var(--text-primary);
}

.search-btn {
    padding: 14px 35px;
    background: var(--gradient);
    border: none;
    border-radius: 12px;
    color: var(--dark);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 150px;
    justify-content: center;
}

.search-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
}

/* Hero Stats */
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin-top: 50px;
    animation: fadeInUp 0.8s ease 0.8s both;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: var(--primary);
    display: block;
}

.stat-label {
    font-size: 14px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Scroll Indicator */
.scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    animation: bounce 2s infinite;
}

.scroll-indicator a {
    color: var(--text-secondary);
    font-size: 28px;
    transition: var(--transition);
    display: block;
    padding: 10px;
}

.scroll-indicator a:hover {
    color: var(--primary);
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

/* ==========================================
   FEATURES SECTION
   ========================================== */
.features {
    padding: 80px 60px;
    background: var(--dark);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    background: var(--dark-light);
    padding: 30px 25px;
    border-radius: 16px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: var(--transition);
}

.feature-card:hover {
    transform: translateY(-8px);
    border-color: rgba(245, 158, 11, 0.3);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

.feature-icon {
    width: 70px;
    height: 70px;
    background: rgba(245, 158, 11, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 26px;
    color: var(--primary);
}

.feature-card h3 {
    font-size: 17px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.feature-card p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
}

/* ==========================================
   SECTION COMMON STYLES
   ========================================== */
.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-badge {
    display: inline-block;
    background: rgba(245, 158, 11, 0.15);
    color: var(--primary);
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.section-header h2 {
    font-size: 40px;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--text-primary);
}

.section-header p {
    font-size: 16px;
    color: var(--text-secondary);
    max-width: 550px;
    margin: 0 auto;
}

/* ==========================================
   CARS SECTION
   ========================================== */
.cars {
    padding: 100px 60px;
    background: linear-gradient(180deg, var(--dark) 0%, var(--dark-light) 100%);
}

/* Car Filter Tabs */
.car-filters {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 24px;
    background: var(--dark-lighter);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    color: var(--text-secondary);
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
}

.filter-btn:hover {
    background: rgba(245, 158, 11, 0.2);
    color: var(--primary);
    border-color: rgba(245, 158, 11, 0.3);
}

.filter-btn.active {
    background: var(--primary);
    color: var(--dark);
    border-color: var(--primary);
}

/* Car Container */
.car-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Car Card */
.car-card {
    background: var(--dark-light);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: var(--transition);
}

.car-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    border-color: rgba(245, 158, 11, 0.3);
}

.car-image-wrapper {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.car-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.car-card:hover img {
    transform: scale(1.1);
}

.car-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.car-badge.available {
    background: var(--success);
    color: white;
}

.car-badge.booked {
    background: var(--danger);
    color: white;
}

.car-favorite {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.car-favorite:hover {
    transform: scale(1.1);
}

.car-favorite.liked {
    background: var(--danger);
}

.car-info {
    padding: 22px;
}

.car-info h3 {
    font-size: 19px;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.car-type-badge {
    display: inline-block;
    background: rgba(59, 130, 246, 0.15);
    color: var(--secondary);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    margin-bottom: 15px;
}

.car-features {
    display: flex;
    gap: 12px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.car-feature {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--text-secondary);
    background: rgba(255, 255, 255, 0.05);
    padding: 5px 10px;
    border-radius: 6px;
}

.car-feature i {
    color: var(--primary);
    font-size: 12px;
}

.car-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.car-price .price-amount {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary);
}

.car-price .price-period {
    font-size: 13px;
    color: var(--text-muted);
}

.book-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--gradient);
    padding: 11px 22px;
    text-decoration: none;
    color: var(--dark);
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
}

.book-btn.disabled {
    background: var(--dark-lighter);
    color: var(--text-muted);
    cursor: not-allowed;
    pointer-events: none;
}

/* No Cars Message */
.no-cars-message {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
    display: none;
    grid-column: 1 / -1;
}

.no-cars-message.show {
    display: block;
}

.no-cars-message i {
    font-size: 50px;
    margin-bottom: 15px;
    opacity: 0.5;
    color: var(--primary);
}

.no-cars-message h3 {
    margin-bottom: 10px;
    color: var(--text-primary);
}

/* View All Button */
.view-all-container {
    text-align: center;
    margin-top: 50px;
}

.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    padding: 14px 40px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}

.view-all-btn:hover {
    background: var(--primary);
    color: var(--dark);
}

/* ==========================================
   WHY CHOOSE US SECTION
   ========================================== */
.why-us {
    padding: 100px 60px;
    background: var(--dark-light);
    position: relative;
    overflow: hidden;
}

.why-us::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.05) 0%, transparent 70%);
    pointer-events: none;
}

.why-us-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    max-width: 1200px;
    margin: 0 auto;
    align-items: center;
}

.why-us-content h2 {
    font-size: 38px;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--text-primary);
    line-height: 1.2;
}

.why-us-content h2 span {
    color: var(--primary);
}

.why-us-content > p {
    color: var(--text-secondary);
    font-size: 15px;
    line-height: 1.8;
    margin-bottom: 30px;
}

.why-us-list {
    list-style: none;
}

.why-us-list li {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
}

.why-us-list .icon {
    width: 50px;
    height: 50px;
    background: rgba(245, 158, 11, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 20px;
    flex-shrink: 0;
}

.why-us-list .text h4 {
    font-size: 17px;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.why-us-list .text p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
}

.why-us-image {
    position: relative;
}

.why-us-image img {
    width: 100%;
    border-radius: 20px;
    box-shadow: var(--shadow);
}

.why-us-image::before {
    content: '';
    position: absolute;
    top: -15px;
    right: -15px;
    width: 100%;
    height: 100%;
    border: 3px solid var(--primary);
    border-radius: 20px;
    z-index: -1;
}

/* ==========================================
   HOW IT WORKS SECTION
   ========================================== */
.how-it-works {
    padding: 100px 60px;
    background: var(--dark);
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
}

.steps-container::before {
    content: '';
    position: absolute;
    top: 50px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--primary), var(--primary), transparent);
    z-index: 0;
}

.step-card {
    text-align: center;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 60px;
    height: 60px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    color: var(--dark);
    margin: 0 auto 20px;
    border: 4px solid var(--dark);
}

.step-card h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.step-card p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
}

/* ==========================================
   TESTIMONIALS SECTION
   ========================================== */
.testimonials {
    padding: 100px 60px;
    background: var(--dark-light);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.testimonial-card {
    background: var(--dark);
    padding: 35px 28px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: var(--transition);
    position: relative;
}

.testimonial-card:hover {
    border-color: rgba(245, 158, 11, 0.3);
    transform: translateY(-5px);
}

.testimonial-card::before {
    content: '"';
    font-size: 70px;
    font-family: Georgia, serif;
    color: var(--primary);
    opacity: 0.2;
    position: absolute;
    top: 15px;
    left: 25px;
    line-height: 1;
}

.testimonial-text {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 25px;
    position: relative;
    z-index: 1;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 15px;
}

.testimonial-author img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
}

.author-info h4 {
    font-size: 15px;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.author-info p {
    font-size: 12px;
    color: var(--text-muted);
}

.testimonial-rating {
    margin-left: auto;
    color: var(--primary);
    font-size: 12px;
}

/* ==========================================
   CTA SECTION
   ========================================== */
.cta {
    padding: 100px 60px;
    background: var(--gradient);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cta::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('https://images.unsplash.com/photo-1449965408869-ebd3fee76fd8?w=1920') center/cover;
    opacity: 0.1;
}

.cta-content {
    position: relative;
    z-index: 1;
    max-width: 650px;
    margin: 0 auto;
}

.cta h2 {
    font-size: 40px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 15px;
}

.cta p {
    font-size: 17px;
    color: rgba(0, 0, 0, 0.7);
    margin-bottom: 30px;
}

.cta-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-btn {
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: none;
    cursor: pointer;
}

.cta-btn.primary {
    background: var(--dark);
    color: var(--text-primary);
}

.cta-btn.primary:hover {
    background: var(--dark-light);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.cta-btn.secondary {
    background: transparent;
    color: var(--dark);
    border: 2px solid var(--dark);
}

.cta-btn.secondary:hover {
    background: var(--dark);
    color: var(--text-primary);
}

/* ==========================================
   TOAST NOTIFICATIONS
   ========================================== */
.toast-container {
    position: fixed;
    top: 100px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toast {
    background: var(--dark-light);
    color: var(--text-primary);
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    min-width: 280px;
    border-left: 4px solid var(--primary);
}

.toast.success {
    border-left-color: var(--success);
}

.toast.success i {
    color: var(--success);
}

.toast.error {
    border-left-color: var(--danger);
}

.toast.error i {
    color: var(--danger);
}

.toast.info {
    border-left-color: var(--secondary);
}

.toast.info i {
    color: var(--secondary);
}

.toast i {
    font-size: 18px;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ==========================================
   RESPONSIVE DESIGN
   ========================================== */
@media (max-width: 1200px) {
    .features-grid,
    .steps-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .steps-container::before {
        display: none;
    }
    
    .testimonials-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .hero h1 {
        font-size: 42px;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .form-group {
        width: 100%;
    }
    
    .search-btn {
        width: 100%;
    }
    
    .hero-stats {
        gap: 30px;
    }
    
    .why-us-container {
        grid-template-columns: 1fr;
    }
    
    .why-us-image {
        order: -1;
        max-width: 500px;
        margin: 0 auto;
    }
    
    .cars, .features, .why-us, .testimonials, .cta, .how-it-works {
        padding: 70px 30px;
    }
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 32px;
    }
    
    .hero-subtitle {
        font-size: 15px;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .features-grid,
    .steps-container {
        grid-template-columns: 1fr;
    }
    
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header h2 {
        font-size: 30px;
    }
    
    .car-container {
        grid-template-columns: 1fr;
    }
    
    .cta h2 {
        font-size: 28px;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .cta-btn {
        width: 100%;
        justify-content: center;
    }
    
    .car-filters {
        gap: 8px;
    }
    
    .filter-btn {
        padding: 8px 16px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .hero h1 {
        font-size: 26px;
    }
    
    .search-box {
        padding: 20px 15px;
    }
    
    .stat-number {
        font-size: 28px;
    }
    
    .car-footer {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .book-btn {
        justify-content: center;
    }
    
    .why-us-content h2 {
        font-size: 26px;
    }
    
    .feature-card {
        padding: 25px 20px;
    }
}
</style>

<div class="toast-container" id="toastContainer"></div>

<section class="hero">
    <div class="hero-content">
        <span class="hero-badge">
            <i class="fas fa-star"></i> Premium Car Rental Service
        </span>
        
        <h1>Find Your Perfect <span>Drive</span> Today</h1>
        
        <p class="hero-subtitle">
            Experience luxury and comfort with our premium fleet of vehicles. 
            Book your dream car in just a few clicks.
        </p>

        <div class="search-box">
            <form action="users/cars.php" method="GET" class="search-form" id="searchForm">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Pick-up Date</label>
                    <input type="date" name="start_date" id="startDate" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-check"></i> Return Date</label>
                    <input type="date" name="end_date" id="endDate" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-car"></i> Car Type</label>
                    <select name="type" id="carType">
                        <option value="">All Types</option>
                        <?php
                        // Fetch car types from database
                        $typeQuery = mysqli_query($conn, "SELECT DISTINCT type FROM cars ORDER BY type");
                        while($type = mysqli_fetch_assoc($typeQuery)) {
                            echo '<option value="'.htmlspecialchars($type['type']).'">'.htmlspecialchars($type['type']).'</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <div class="hero-stats">
            <?php
            // Get stats from database
            $totalCars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars"))['count'];
            $totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
            $totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
            ?>
            <div class="stat-item">
                <span class="stat-number"><?php echo $totalCars; ?>+</span>
                <span class="stat-label">Cars Available</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $totalUsers; ?>+</span>
                <span class="stat-label">Happy Customers</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $totalBookings; ?>+</span>
                <span class="stat-label">Completed Rides</span>
            </div>
        </div>
    </div>

    <div class="scroll-indicator">
        <a href="#features"><i class="fas fa-chevron-down"></i></a>
    </div>
</section>

<section class="features" id="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Fully Insured</h3>
            <p>All vehicles come with comprehensive insurance for your peace of mind.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <h3>Best Prices</h3>
            <p>Competitive rates with no hidden fees. Best value guaranteed.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-headset"></i>
            </div>
            <h3>24/7 Support</h3>
            <p>Our support team is available around the clock to assist you.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h3>Easy Pickup</h3>
            <p>Convenient pickup and drop-off locations across the city.</p>
        </div>
    </div>
</section>

<section class="cars" id="cars">
    <div class="section-header">
        <span class="section-badge">Our Fleet</span>
        <h2>Featured Cars</h2>
        <p>Choose from our wide range of premium vehicles for any occasion</p>
    </div>

    <div class="car-filters">
        <button class="filter-btn active" data-filter="all">All Cars</button>
        <?php
        // Fetch unique car types for filters
        $filterQuery = mysqli_query($conn, "SELECT DISTINCT type FROM cars ORDER BY type");
        while($filterType = mysqli_fetch_assoc($filterQuery)) {
            echo '<button class="filter-btn" data-filter="'.htmlspecialchars($filterType['type']).'">'.htmlspecialchars($filterType['type']).'</button>';
        }
        ?>
    </div>

    <div class="car-container" id="carContainer">
        <?php
        $carsQuery = mysqli_query($conn, "SELECT * FROM cars WHERE status = 'available' ORDER BY id DESC LIMIT 6");

        if(mysqli_num_rows($carsQuery) > 0) {
            while($car = mysqli_fetch_assoc($carsQuery)) {
                $carId = $car['id'];
                $carName = htmlspecialchars($car['name']);
                $carType = htmlspecialchars($car['type']);
                $carImage = htmlspecialchars($car['image']);
                $carPrice = number_format($car['price_per_day']);
                $carSeats = isset($car['seats']) ? $car['seats'] : 5;
                $carFuel = isset($car['fuel_type']) ? htmlspecialchars($car['fuel_type']) : 'Petrol';
                $carTransmission = isset($car['transmission']) ? htmlspecialchars($car['transmission']) : 'Manual';
        ?>
        <div class="car-card" data-type="<?php echo $carType; ?>">
            <div class="car-image-wrapper">
                <img src="uploads/car_images/<?php echo $carImage; ?>" alt="<?php echo $carName; ?>" 
                     onerror="this.src='https://via.placeholder.com/400x200?text=Car+Image'">
                <span class="car-badge available">Available</span>
                <button class="car-favorite" data-id="<?php echo $carId; ?>" title="Add to Favorites">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            
            <div class="car-info">
                <h3><?php echo $carName; ?></h3>
                <span class="car-type-badge"><?php echo $carType; ?></span>
                
                <div class="car-features">
                    <span class="car-feature">
                        <i class="fas fa-users"></i> <?php echo $carSeats; ?> Seats
                    </span>
                    <span class="car-feature">
                        <i class="fas fa-cog"></i> <?php echo $carTransmission; ?>
                    </span>
                    <span class="car-feature">
                        <i class="fas fa-gas-pump"></i> <?php echo $carFuel; ?>
                    </span>
                    <span class="car-feature">
                        <i class="fas fa-snowflake"></i> AC
                    </span>
                </div>
                
                <div class="car-footer">
                    <div class="car-price">
                        <span class="price-amount">₹<?php echo $carPrice; ?></span>
                        <span class="price-period">per day</span>
                    </div>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="users/car_details.php?id=<?php echo $carId; ?>" class="book-btn">
                            Book Now <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php?redirect=users/car_details.php?id=<?php echo $carId; ?>" class="book-btn">
                            Book Now <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
        ?>
        <div class="no-cars-message show">
            <i class="fas fa-car"></i>
            <h3>No Cars Available</h3>
            <p>Please check back later for available vehicles.</p>
        </div>
        <?php } ?>
        
        <div class="no-cars-message" id="noCarsMessage">
            <i class="fas fa-search"></i>
            <h3>No Cars Found</h3>
            <p>No cars match the selected filter. Try another category.</p>
        </div>
    </div>

    <div class="view-all-container">
        <a href="users/cars.php" class="view-all-btn">
            View All Cars <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<section class="how-it-works" id="how-it-works">
    <div class="section-header">
        <span class="section-badge">Process</span>
        <h2>How It Works</h2>
        <p>Rent a car in 4 simple steps</p>
    </div>

    <div class="steps-container">
        <div class="step-card">
            <div class="step-number">1</div>
            <h3>Choose Your Car</h3>
            <p>Browse our fleet and select the perfect car for your needs.</p>
        </div>
        
        <div class="step-card">
            <div class="step-number">2</div>
            <h3>Select Dates</h3>
            <p>Pick your rental dates and check availability instantly.</p>
        </div>
        
        <div class="step-card">
            <div class="step-number">3</div>
            <h3>Make Payment</h3>
            <p>Secure payment options with instant confirmation.</p>
        </div>
        
        <div class="step-card">
            <div class="step-number">4</div>
            <h3>Enjoy Your Ride</h3>
            <p>Pick up your car and hit the road with confidence!</p>
        </div>
    </div>
</section>

<section class="why-us" id="why-us">
    <div class="why-us-container">
        <div class="why-us-content">
            <span class="section-badge">Why Choose Us</span>
            <h2>We Offer The Best <span>Experience</span> With Our Rentals</h2>
            <p>
                With years of experience in car rental services, we provide 
                exceptional quality and customer satisfaction on every ride.
            </p>
            
            <ul class="why-us-list">
                <li>
                    <div class="icon"><i class="fas fa-car-side"></i></div>
                    <div class="text">
                        <h4>Wide Range of Cars</h4>
                        <p>From economy to luxury, we have the perfect vehicle for every budget.</p>
                    </div>
                </li>
                <li>
                    <div class="icon"><i class="fas fa-tags"></i></div>
                    <div class="text">
                        <h4>Transparent Pricing</h4>
                        <p>No hidden charges. What you see is exactly what you pay.</p>
                    </div>
                </li>
                <li>
                    <div class="icon"><i class="fas fa-bolt"></i></div>
                    <div class="text">
                        <h4>Quick Booking</h4>
                        <p>Book your car in under 5 minutes with our easy platform.</p>
                    </div>
                </li>
            </ul>
        </div>
        
        <div class="why-us-image">
            <img src="https://images.unsplash.com/photo-1485291571150-772bcfc10da5?w=600" alt="Luxury Car">
        </div>
    </div>
</section>

<section class="testimonials" id="testimonials">
    <div class="section-header">
        <span class="section-badge">Testimonials</span>
        <h2>What Our Customers Say</h2>
        <p>Real feedback from our satisfied customers</p>
    </div>

    <div class="testimonials-grid">
        <div class="testimonial-card">
            <p class="testimonial-text">
                "Amazing service! The car was spotless and the booking process was incredibly smooth. 
                Will definitely use again for my next trip."
            </p>
            <div class="testimonial-author">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Customer">
                <div class="author-info">
                    <h4>Rajesh Kumar</h4>
                    <p>Business Traveler</p>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <p class="testimonial-text">
                "Best prices in town! Compared many rental services and this was the most 
                affordable without compromising on quality."
            </p>
            <div class="testimonial-author">
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Customer">
                <div class="author-info">
                    <h4>Priya Sharma</h4>
                    <p>Family Trip</p>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <p class="testimonial-text">
                "24/7 support helped me when I had issues at midnight. They resolved everything quickly. 
                Exceptional customer service!"
            </p>
            <div class="testimonial-author">
                <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Customer">
                <div class="author-info">
                    <h4>Amit Patel</h4>
                    <p>Weekend Getaway</p>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="cta-content">
        <h2>Ready to Hit the Road?</h2>
        <p>Book your dream car today and enjoy exclusive discounts on your first rental!</p>
        <div class="cta-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="users/cars.php" class="cta-btn primary">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
                <a href="users/my_bookings.php" class="cta-btn secondary">
                    <i class="fas fa-list"></i> My Bookings
                </a>
            <?php else: ?>
                <a href="register.php" class="cta-btn primary">
                    <i class="fas fa-user-plus"></i> Sign Up Now
                </a>
                <a href="login.php" class="cta-btn secondary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // DATE INPUTS CONFIGURATION
    // ==========================================
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const formatDate = (date) => date.toISOString().split('T')[0];
    
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if(startDate && endDate) {
        startDate.setAttribute('min', formatDate(today));
        startDate.value = formatDate(today);
        
        endDate.setAttribute('min', formatDate(tomorrow));
        endDate.value = formatDate(tomorrow);
        
        startDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const minEndDate = new Date(selectedDate);
            minEndDate.setDate(minEndDate.getDate() + 1);
            
            endDate.setAttribute('min', formatDate(minEndDate));
            
            if(new Date(endDate.value) <= selectedDate) {
                endDate.value = formatDate(minEndDate);
            }
        });
    }

    // ==========================================
    // SEARCH FORM VALIDATION
    // ==========================================
    const searchForm = document.getElementById('searchForm');
    if(searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            
            if(end <= start) {
                e.preventDefault();
                showToast('Return date must be after pick-up date!', 'error');
                return false;
            }
            
            showToast('Searching available cars...', 'info');
        });
    }

    // ==========================================
    // CAR FILTER FUNCTIONALITY
    // ==========================================
    const filterBtns = document.querySelectorAll('.filter-btn');
    const carCards = document.querySelectorAll('.car-card');
    const noCarsMessage = document.getElementById('noCarsMessage');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            let visibleCount = 0;
            
            carCards.forEach(card => {
                const cardType = card.getAttribute('data-type');
                
                if(filterValue === 'all' || cardType === filterValue) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if(noCarsMessage) {
                noCarsMessage.classList.toggle('show', visibleCount === 0);
            }
            
            const message = filterValue === 'all' ? 'Showing all cars' : `Showing ${filterValue} cars`;
            showToast(message, 'success');
        });
    });

    // ==========================================
    // FAVORITE BUTTON FUNCTIONALITY
    // ==========================================
    const favBtns = document.querySelectorAll('.car-favorite');
    
    favBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const icon = this.querySelector('i');
            
            this.classList.toggle('liked');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            
            if(this.classList.contains('liked')) {
                showToast('Added to favorites! ❤️', 'success');
            } else {
                showToast('Removed from favorites', 'info');
            }
        });
    });

    // ==========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ==========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            
            if(targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ==========================================
    // BUTTON INTERACTIONS
    // ==========================================
    document.querySelectorAll('.book-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if(!this.classList.contains('disabled')) {
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                }, 2000);
            }
        });
    });

    const viewAllBtn = document.querySelector('.view-all-btn');
    if(viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        });
    }

    document.querySelectorAll('.cta-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // ==========================================
    // SCROLL ANIMATIONS
    // ==========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .car-card, .testimonial-card, .step-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // ==========================================
    // TOAST NOTIFICATION FUNCTION
    // ==========================================
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle',
            warning: 'fa-exclamation-triangle'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    window.showToast = showToast;

    // ==========================================
    // COUNTER ANIMATION FOR STATS
    // ==========================================
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if(current >= target) {
                element.textContent = target + '+';
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current) + '+';
            }
        }, 30);
    }

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    const value = parseInt(stat.textContent);
                    if(!isNaN(value)) {
                        animateCounter(stat, value);
                    }
                });
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    const heroStats = document.querySelector('.hero-stats');
    if(heroStats) {
        statsObserver.observe(heroStats);
    }

});
</script>

<?php include("includes/footer.php"); ?>