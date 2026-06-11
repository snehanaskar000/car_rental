<?php
session_start();
include("includes/db.php");
include("includes/header.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = "";
$error = "";

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {

    // Rate limit (optional, you can remove if you want)
    if (isset($_SESSION['last_contact_submit']) && time() - $_SESSION['last_contact_submit'] < 10) {
        $error = "Please wait a few seconds before sending again.";
    }

    // Honeypot (anti-spam)
    elseif (!empty($_POST['website'])) {
        $success = "Message sent successfully!";
    }

    else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 5) {
            $error = "Please fill all fields correctly!";
        } else {

            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);

            $stmt = mysqli_prepare($conn, "
                INSERT INTO contacts 
                (name, email, phone, subject, message, ip, user_agent, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'unread', NOW())
            ");

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $phone, $subject, $message, $ip, $ua);

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['last_contact_submit'] = time();
                    $_SESSION['success_msg'] = "Message sent successfully!";

                    mysqli_stmt_close($stmt);

                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = "Database Error: " . mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);
            } else {
                $error = "Prepare Error: " . mysqli_error($conn);
            }
        }
    }
}

// Show success after redirect
if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
?>
<!-- Font Awesome - All icons will now work -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

<style>
    
    /* Fix icon alignment and sharpness */
    i {
        line-height: 1;
        -webkit-font-smoothing: antialiased;
    }

    /* ========== RESET & BASE ========== */
    .contact-page {
        background: #020617;
        min-height: 100vh;
    }

    /* ========== HERO SECTION ========== */
    .contact-hero {
        position: relative;
        padding: 140px 5% 100px;
        background: linear-gradient(135deg, #020617 0%, #0f172a 50%, #020617 100%);
        overflow: hidden;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(245,158,11,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .contact-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(245,158,11,0.05) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .hero-text h1 {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 25px;
        color: #fff;
    }

    .hero-text h1 span {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-text p {
        font-size: 1.2rem;
        color: #94A3B8;
        line-height: 1.8;
        margin-bottom: 40px;
    }

    /* Quick Contact Buttons */
    .quick-contact {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .quick-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 28px;
        background: rgba(30, 41, 59, 0.8);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 60px;
        color: #fff;
        text-decoration: none;
        transition: all 0.4s ease;
    }

    .quick-btn:hover {
        background: #F59E0B;
        border-color: #F59E0B;
        color: #000;
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(245,158,11,0.3);
    }

    .quick-btn i {
        font-size: 20px;
    }

    .quick-btn span {
        font-weight: 600;
    }

    /* Hero Image/3D Element */
    .hero-visual {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .contact-illustration {
        width: 100%;
        max-width: 450px;
        position: relative;
    }

    .floating-card {
        position: absolute;
        background: rgba(30, 41, 59, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        gap: 15px;
        animation: float 3s ease-in-out infinite;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    .floating-card:nth-child(1) {
        top: 10%;
        left: -20px;
        animation-delay: 0s;
    }

    .floating-card:nth-child(2) {
        top: 45%;
        right: -30px;
        animation-delay: 1s;
    }

    .floating-card:nth-child(3) {
        bottom: 10%;
        left: 10%;
        animation-delay: 2s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    .floating-card .icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 22px;
    }

    .floating-card .card-text h4 {
        font-size: 14px;
        color: #fff;
        margin-bottom: 3px;
    }

    .floating-card .card-text p {
        font-size: 12px;
        color: #94A3B8;
    }

    .center-circle {
        width: 300px;
        height: 300px;
        background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(245,158,11,0.05));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(245,158,11,0.3);
        position: relative;
    }

    .center-circle::before {
        content: '';
        position: absolute;
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, rgba(245,158,11,0.3), rgba(245,158,11,0.1));
        border-radius: 50%;
        border: 2px solid rgba(245,158,11,0.4);
    }

    .center-circle i {
        font-size: 60px;
        color: #F59E0B;
        position: relative;
        z-index: 2;
    }

    /* ========== CONTACT INFO SECTION ========== */
    .info-section {
        padding: 100px 5%;
        background: #0f172a;
    }

    .info-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }

    .info-card {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 24px;
        padding: 35px 25px;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: all 0.4s ease;
    }

    .info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #F59E0B, #D97706);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }

    .info-card:hover {
        transform: translateY(-10px);
        border-color: rgba(245,158,11,0.3);
        box-shadow: 0 30px 60px rgba(245,158,11,0.1);
    }

    .info-card:hover::before {
        transform: scaleX(1);
    }

    .info-card .icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 25px;
        background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(245,158,11,0.05));
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: #F59E0B;
        transition: all 0.4s ease;
    }

    .info-card:hover .icon {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: #000;
        transform: scale(1.1) rotate(5deg);
    }

    .info-card h3 {
        font-size: 20px;
        color: #fff;
        margin-bottom: 12px;
    }

    .info-card p {
        color: #94A3B8;
        font-size: 15px;
        line-height: 1.6;
    }

    .info-card a {
        color: #F59E0B;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
    }

    .info-card a:hover {
        color: #fff;
    }

    /* ========== MAIN CONTACT SECTION ========== */
    .main-contact {
        padding: 100px 5%;
        background: #020617;
    }

    .contact-container {
        max-width: 1300px;
        margin: 0 auto;
    }

    .contact-wrapper {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 50px;
        align-items: stretch;
    }

    /* Form Section */
    .form-section {
        background: linear-gradient(145deg, #1e293b, #0f172a);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 30px;
        padding: 50px;
        position: relative;
        overflow: hidden;
    }

    .form-section::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(245,158,11,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .form-header {
        margin-bottom: 40px;
        position: relative;
        z-index: 2;
    }

    .form-badge {
        display: inline-block;
        padding: 8px 18px;
        background: rgba(245,158,11,0.1);
        border: 1px solid rgba(245,158,11,0.3);
        border-radius: 30px;
        color: #F59E0B;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .form-header h2 {
        font-size: 2.5rem;
        color: #fff;
        margin-bottom: 12px;
    }

    .form-header h2 span {
        color: #F59E0B;
    }

    .form-header p {
        color: #94A3B8;
        font-size: 16px;
    }

    /* Alert Styles */
    .alert {
        padding: 18px 22px;
        border-radius: 14px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 14px;
        animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #10b981;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
    }

    /* Form Styles */
    .contact-form {
        position: relative;
        z-index: 2;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .form-group {
        margin-bottom: 28px;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #E2E8F0;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .form-group label i {
        color: #F59E0B;
        font-size: 16px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 18px 22px;
        background: rgba(15, 23, 42, 0.8);
        border: 2px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        color: #fff;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #64748B;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #F59E0B;
        background: rgba(15, 23, 42, 1);
        box-shadow: 0 0 0 4px rgba(245,158,11,0.1);
    }

    .form-group textarea {
        resize: none;
        min-height: 140px;
    }

    .form-group select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23F59E0B' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 24px;
    }

    .btn-submit {
        width: 100%;
        padding: 20px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border: none;
        border-radius: 14px;
        color: #000;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: wait;
        transform: none !important;
    }

    .btn-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: 0.5s;
    }

    .btn-submit:hover::before {
        left: 100%;
    }

    .btn-submit:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(245,158,11,0.4);
    }

    /* Map Section */
    .map-section {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .map-card {
        flex: 1;
        background: linear-gradient(145deg, #1e293b, #0f172a);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 30px;
        overflow: hidden;
        position: relative;
    }

    .map-header {
        padding: 25px 30px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .map-header .icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 20px;
    }

    .map-header h3 {
        font-size: 20px;
        color: #fff;
    }

    .map-header p {
        font-size: 13px;
        color: #94A3B8;
    }

    .map-container {
        height: 300px;
        position: relative;
    }

    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .map-overlay {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .map-overlay p {
        color: #fff;
        font-size: 14px;
    }

    .map-overlay p i {
        color: #F59E0B;
        margin-right: 8px;
    }

    .btn-directions {
        padding: 10px 20px;
        background: #F59E0B;
        border-radius: 10px;
        color: #000;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: 0.3s;
    }

    .btn-directions:hover {
        background: #fff;
    }

    /* Social Card */
    .social-card {
        background: linear-gradient(145deg, #1e293b, #0f172a);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 24px;
        padding: 30px;
    }

    .social-card h4 {
        font-size: 18px;
        color: #fff;
        margin-bottom: 20px;
    }

    .social-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .social-btn {
        width: 100%;
        aspect-ratio: 1;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94A3B8;
        font-size: 22px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-btn:hover {
        background: #F59E0B;
        border-color: #F59E0B;
        color: #000;
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 15px 30px rgba(245,158,11,0.3);
    }

    /* ========== FAQ SECTION ========== */
    .faq-section {
        padding: 100px 5%;
        background: #0f172a;
    }

    .faq-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .section-badge {
        display: inline-block;
        padding: 8px 20px;
        background: rgba(245,158,11,0.1);
        border: 1px solid rgba(245,158,11,0.3);
        border-radius: 30px;
        color: #F59E0B;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 2.8rem;
        color: #fff;
        margin-bottom: 15px;
    }

    .section-title span {
        color: #F59E0B;
    }

    .section-subtitle {
        color: #94A3B8;
        font-size: 17px;
    }

    .faq-list {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .faq-item {
        background: linear-gradient(145deg, #1e293b, #0f172a);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 18px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .faq-item:hover {
        border-color: rgba(245,158,11,0.3);
    }

    .faq-item.active {
        border-color: #F59E0B;
        box-shadow: 0 10px 40px rgba(245,158,11,0.1);
    }

    .faq-question {
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: 0.3s;
    }

    .faq-question h4 {
        font-size: 17px;
        color: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .faq-question h4 .num {
        width: 35px;
        height: 35px;
        background: rgba(245,158,11,0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #F59E0B;
        font-size: 14px;
    }

    .faq-item.active .faq-question h4 .num {
        background: #F59E0B;
        color: #000;
    }

    .faq-question .toggle-icon {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #F59E0B;
        transition: 0.3s;
    }

    .faq-item.active .faq-question .toggle-icon {
        background: #F59E0B;
        color: #000;
        transform: rotate(180deg);
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
    }

    .faq-item.active .faq-answer {
        max-height: 300px;
    }

    .faq-answer-content {
        padding: 0 30px 25px;
        padding-left: 80px;
        color: #94A3B8;
        line-height: 1.8;
        font-size: 15px;
    }

    /* ========== CTA SECTION ========== */
    .cta-section {
        padding: 100px 5%;
        background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(245,158,11,0.02));
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .cta-container {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .cta-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 30px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 45px;
        color: #000;
    }

    .cta-container h2 {
        font-size: 2.5rem;
        color: #fff;
        margin-bottom: 15px;
    }

    .cta-container h2 span {
        color: #F59E0B;
    }

    .cta-container p {
        color: #94A3B8;
        font-size: 18px;
        margin-bottom: 40px;
    }

    .cta-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .btn-cta {
        padding: 18px 40px;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.4s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: #000;
    }

    .btn-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(245,158,11,0.4);
    }

    .btn-outline {
        border: 2px solid rgba(255,255,255,0.2);
        color: #fff;
    }

    .btn-outline:hover {
        border-color: #F59E0B;
        color: #F59E0B;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1024px) {
        .hero-grid { grid-template-columns: 1fr; text-align: center; }
        .hero-visual { display: none; }
        .quick-contact { justify-content: center; }
        .info-grid { grid-template-columns: repeat(2, 1fr); }
        .contact-wrapper { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .hero-text h1 { font-size: 2.5rem; }
        .info-grid { grid-template-columns: 1fr; }
        .form-section { padding: 30px 25px; }
        .form-row { grid-template-columns: 1fr; }
        .section-title { font-size: 2rem; }
        .quick-contact { flex-direction: column; }
        .social-grid { grid-template-columns: repeat(5, 1fr); }
        .cta-buttons { flex-direction: column; }
        .faq-question h4 { font-size: 15px; }
        .faq-answer-content { padding-left: 30px; }
    }

</style>

<div class="contact-page">

    <!-- ========== HERO SECTION ========== -->
    <section class="contact-hero">
        <div class="hero-container">
            <div class="hero-grid">
                
                <div class="hero-text">
                    <h1>Let's Start a <span>Conversation</span></h1>
                    <p>Have questions about our car rental services? We're here to help you 24/7. Reach out and let us make your journey unforgettable.</p>
                    
                    <div class="quick-contact">
                        <a href="tel:+919876543210" class="quick-btn">
                            <i class="fas fa-phone-alt"></i>
                            <span>+91 98765 43210</span>
                        </a>
                        <a href="mailto:info@carrental.com" class="quick-btn">
                            <i class="fas fa-envelope"></i>
                            <span>info@carrental.com</span>
                        </a>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="contact-illustration">
                        <div class="floating-card">
                            <div class="icon-box"><i class="fas fa-headset"></i></div>
                            <div class="card-text">
                                <h4>24/7 Support</h4>
                                <p>Always available</p>
                            </div>
                        </div>
                        <div class="floating-card">
                            <div class="icon-box"><i class="fas fa-bolt"></i></div>
                            <div class="card-text">
                                <h4>Fast Response</h4>
                                <p>Within 1 hour</p>
                            </div>
                        </div>
                        <div class="floating-card">
                            <div class="icon-box"><i class="fas fa-star"></i></div>
                            <div class="card-text">
                                <h4>5-Star Service</h4>
                                <p>Premium quality</p>
                            </div>
                        </div>
                        <div class="center-circle">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========== CONTACT INFO CARDS ========== -->
    <section class="info-section">
        <div class="info-container">
            <div class="info-grid">
                
                <div class="info-card">
                    <div class="icon"><i class="fas fa-phone-alt"></i></div>
                    <h3>Call Us</h3>
                    <p><a href="tel:+919876543210">+91 98765 43210</a></p>
                    <p><a href="tel:+919876543211">+91 98765 43211</a></p>
                </div>

                <div class="info-card">
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                    <h3>Email Us</h3>
                    <p><a href="mailto:info@carrental.com">info@carrental.com</a></p>
                    <p><a href="mailto:support@carrental.com">support@carrental.com</a></p>
                </div>

                <div class="info-card">
                    <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                    <h3>Visit Us</h3>
                    <p>123 Park Street</p>
                    <p>Kolkata, WB 700001</p>
                </div>

                <div class="info-card">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h3>Working Hours</h3>
                    <p>Mon - Sun: 9 AM - 9 PM</p>
                    <p>24/7 Support Available</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ========== MAIN CONTACT FORM & MAP ========== -->
    <section class="main-contact">
        <div class="contact-container">
            <div class="contact-wrapper">
                
                <!-- Form Section -->
                <div class="form-section">
                    <div class="form-header">
                        <span class="form-badge"><i class="fas fa-paper-plane"></i> Send Message</span>
                        <h2>Drop Us a <span>Line</span></h2>
                        <p>Fill out the form and our team will get back to you within 24 hours</p>
                    </div>

                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error ?>
                        </div>
                    <?php endif; ?>
                    

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="contact-form">                        <input type="text" name="website" value="" style="position:absolute; left:-9999px; opacity:0; pointer-events:none;" tabindex="-1" autocomplete="off">

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" name="name" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" name="email" placeholder="john@example.com" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-tag"></i> Subject</label>
                                <select name="subject" required>
                                    <option value="">Select Subject</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Booking Support">Booking Support</option>
                                    <option value="Payment Issue">Payment Issue</option>
                                    <option value="Feedback">Feedback</option>
                                    <option value="Partnership">Partnership</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-comment-alt"></i> Your Message</label>
                            <textarea name="message" placeholder="Tell us how we can help you..." required></textarea>
                        </div>

                        <button type="submit" name="send_message" class="btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Map & Social Section -->
                <div class="map-section">
                    
                    <!-- Map Card -->
                    <div class="map-card">
                        <div class="map-header">
                            <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h3>Our Location</h3>
                                <p>Find us on Google Maps</p>
                            </div>
                        </div>
                        <div class="map-container">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3684.1674990908847!2d88.34371631495779!3d22.572645985184842!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a0277b027a9d205%3A0x9dd5b063e1b4a14!2sPark%20Street%2C%20Kolkata!5e0!3m2!1sen!2sin!4v1234567890" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
                            <div class="map-overlay">
                                <p><i class="fas fa-map-pin"></i> 123 Park Street, Kolkata</p>
                                <a href="https://www.google.com/maps/dir/?api=1&destination=Park+Street+Kolkata" target="_blank" class="btn-directions">
                                    <i class="fas fa-directions"></i> Directions
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Social Card -->
                    <div class="social-card">
                        <h4><i class="fas fa-globe"></i> Connect With Us</h4>
                        <div class="social-grid">
                            <a href="https://facebook.com" target="_blank" class="social-btn" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com" target="_blank" class="social-btn" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://instagram.com" target="_blank" class="social-btn" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://linkedin.com" target="_blank" class="social-btn" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://youtube.com" target="_blank" class="social-btn" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ========== FAQ SECTION ========== -->
    <section class="faq-section">
        <div class="faq-container">
            <div class="section-header">
                <span class="section-badge"><i class="fas fa-question-circle"></i> FAQ</span>
                <h2 class="section-title">Common <span>Questions</span></h2>
                <p class="section-subtitle">Find answers to frequently asked questions</p>
            </div>

            <div class="faq-list">
                
                <div class="faq-item active">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h4><span class="num">01</span> How do I book a car?</h4>
                        <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p class="faq-answer-content">Simply browse our fleet, select your preferred car, choose your rental dates, and complete the booking. You'll receive instant confirmation via email and SMS.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h4><span class="num">02</span> What documents do I need?</h4>
                        <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p class="faq-answer-content">You'll need a valid driver's license, government-issued ID (Aadhar/PAN/Passport), and a credit/debit card for the security deposit.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h4><span class="num">03</span> Can I cancel my booking?</h4>
                        <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p class="faq-answer-content">Yes! Free cancellation up to 24 hours before pickup. Cancellations within 24 hours may incur a fee. Contact our support for assistance.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h4><span class="num">04</span> Is insurance included?</h4>
                        <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p class="faq-answer-content">Yes, all rentals include basic insurance coverage. Premium insurance options are available for additional protection during booking.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <h4><span class="num">05</span> What if I return late?</h4>
                        <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p class="faq-answer-content">Late returns are charged hourly. We recommend extending your booking in advance through our app or by calling support to avoid extra charges.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========== CTA SECTION ========== -->
    <section class="cta-section">
        <div class="cta-container">
            <div class="cta-icon">
                <i class="fas fa-car"></i>
            </div>
            <h2>Ready to <span>Drive?</span></h2>
            <p>Browse our premium fleet and find your perfect ride today!</p>
            <div class="cta-buttons">
                <a href="users/cars.php" class="btn-cta btn-primary">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
                <a href="tel:+919876543210" class="btn-cta btn-outline">
                    <i class="fas fa-phone"></i> Call Now
                </a>
            </div>
        </div>
    </section>

</div>

<script>
    // FAQ Toggle
    function toggleFAQ(element) {
        const faqItem = element.parentElement;
        const allItems = document.querySelectorAll('.faq-item');
        
        allItems.forEach(item => {
            if(item !== faqItem) {
                item.classList.remove('active');
            }
        });
        
        faqItem.classList.toggle('active');
    }

    // Prevent double form submit
    // document.querySelector('.contact-form').addEventListener('submit', function() {
    //     const btn = this.querySelector('.btn-submit');
    //     btn.disabled = true;
    //     btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Message...';
    // });

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        });
    }, 5000);

    // Scroll animations
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

    document.querySelectorAll('.info-card, .faq-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>

<?php include("includes/footer.php"); ?>