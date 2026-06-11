<?php
session_start();
include("../includes/db.php");

// ============================================
// 1. SECURITY & CONTEXT VALIDATION
// ============================================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['booking_data']) || empty($_SESSION['booking_data'])) {
    header("Location: cars.php");
    exit();
}

// ============================================
// 2. DATA RETRIEVAL
// ============================================

// From Session
$booking_data    = $_SESSION['booking_data'];
$car_id          = $booking_data['car_id'];
$user_id         = $_SESSION['user_id'];
$pickup_date     = $booking_data['pickup_date'];
$return_date     = $booking_data['return_date'];
$pickup_location = isset($booking_data['pickup_location']) ? $booking_data['pickup_location'] : 'City Center';
$pickup_time     = isset($booking_data['pickup_time']) ? $booking_data['pickup_time'] : '10:00 AM';
$days            = $booking_data['days'];

// From Database (Users)
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($userQuery);

// From Database (Cars)
$carQuery = mysqli_query($conn, "SELECT * FROM cars WHERE id = $car_id");
$car = mysqli_fetch_assoc($carQuery);

if (!$car) {
    header("Location: cars.php");
    exit();
}

// ============================================
// 3. FINANCIAL CALCULATION LOGIC
// ============================================
$price_per_day = $car['price_per_day'];

// Subtotal: Days × Rate
$subtotal = $days * $price_per_day;

// Location Fee: Conditional
$location_fee = 0;
if ($pickup_location == 'Home Delivery') {
    $location_fee = 300;
} elseif ($pickup_location == 'Hotel Delivery') {
    $location_fee = 200;
}

// Service Fee: 5% of subtotal
$service_fee = round($subtotal * 0.05);

// Insurance: ₹100 per day
$insurance_fee = $days * 100;

// Total: Sum of all
$total_amount = $subtotal + $location_fee + $service_fee + $insurance_fee;

// ============================================
// 4. DATABASE TRANSACTION (PROCEED LOGIC)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proceed_payment'])) {
    
    // Sanitize dates
    $start = mysqli_real_escape_string($conn, $pickup_date);
    $end   = mysqli_real_escape_string($conn, $return_date);
    
    // Insert Booking (status: pending)
    $insertBooking = mysqli_query($conn, "
        INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status) 
        VALUES ($user_id, $car_id, '$start', '$end', $total_amount, 'pending')
    ");
    
    if ($insertBooking) {
        // Capture ID
        $booking_id = mysqli_insert_id($conn);
        
        // Insert Payment (status: unpaid)
        mysqli_query($conn, "
            INSERT INTO payments (booking_id, amount, status) 
            VALUES ($booking_id, $total_amount, 'unpaid')
        ");
        
        // Store in session
        $_SESSION['current_booking_id'] = $booking_id;
        
        // Hand-off to payment page
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
    } else {
        $error = "Booking failed. Please try again.";
    }
}

include("../includes/header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking | Car Rental</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================
           ROOT VARIABLES
           ============================================ */
        :root {
            --primary: #F59E0B;
            --primary-dark: #D97706;
            --primary-glow: rgba(245, 158, 11, 0.3);
            --secondary: #3B82F6;
            --accent: #8B5CF6;
            
            --dark-900: #0a0e17;
            --dark-800: #0f172a;
            --dark-700: #1e293b;
            --dark-600: #334155;
            --dark-500: #475569;
            
            --text-white: #ffffff;
            --text-light: #f1f5f9;
            --text-muted: #94a3b8;
            --text-dark: #64748b;
            
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --danger: #ef4444;
            --warning: #f59e0b;
            
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================
           RESET & BASE
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--dark-900);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(245, 158, 11, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(139, 92, 246, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* ============================================
           BREADCRUMB
           ============================================ */
        .breadcrumb {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 16px 0;
        }

        .breadcrumb-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb .separator {
            color: var(--dark-500);
            font-size: 10px;
        }

        .breadcrumb .current {
            color: var(--primary);
            font-weight: 500;
        }

        /* ============================================
           PAGE CONTAINER
           ============================================ */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px 60px;
        }

        /* ============================================
           PAGE HEADER
           ============================================ */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, var(--text-white), var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header h1 i {
            background: var(--primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 10px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 16px;
        }

        /* ============================================
           PROGRESS STEPS
           ============================================ */
        .progress-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            margin-bottom: 50px;
            padding: 0 20px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition);
            position: relative;
        }

        .step.completed .step-circle {
            background: var(--success);
            color: white;
            box-shadow: 0 0 20px var(--success-glow);
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--dark-900);
            box-shadow: 0 0 25px var(--primary-glow);
            animation: pulse-glow 2s infinite;
        }

        .step.pending .step-circle {
            background: var(--dark-600);
            color: var(--text-dark);
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px var(--primary-glow); }
            50% { box-shadow: 0 0 35px var(--primary-glow); }
        }

        .step-label {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }

        .step.completed .step-label { color: var(--success); }
        .step.active .step-label { color: var(--primary); }
        .step.pending .step-label { color: var(--text-dark); }

        .step-line {
            width: 60px;
            height: 3px;
            background: var(--dark-600);
            border-radius: 2px;
            margin: 0 5px;
        }

        .step-line.completed {
            background: linear-gradient(90deg, var(--success), var(--primary));
        }

        /* ============================================
           MAIN GRID
           ============================================ */
        .booking-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 30px;
            align-items: start;
        }

        /* ============================================
           GLASS CARD
           ============================================ */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: var(--glass-shadow);
            transition: var(--transition);
        }

        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .glass-card:last-child {
            margin-bottom: 0;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--glass-border);
        }

        .card-header i {
            font-size: 20px;
            color: var(--primary);
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-white);
        }

        /* ============================================
           CAR PREVIEW
           ============================================ */
        .car-preview {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .car-image-box {
            width: 200px;
            height: 140px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            flex-shrink: 0;
            position: relative;
            border: 2px solid var(--glass-border);
        }

        .car-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .car-image-box:hover img {
            transform: scale(1.05);
        }

        .car-info-box h4 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 8px;
        }

        .car-type-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.15);
            color: var(--secondary);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 14px;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .car-specs {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-muted);
            background: var(--dark-700);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
        }

        .spec-item i {
            color: var(--primary);
            font-size: 12px;
        }

        /* ============================================
           INFO GRID
           ============================================ */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .info-tile {
            background: linear-gradient(135deg, var(--dark-700), var(--dark-800));
            padding: 18px;
            border-radius: var(--radius-md);
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .info-tile:hover {
            border-color: rgba(245, 158, 11, 0.3);
            background: linear-gradient(135deg, var(--dark-600), var(--dark-700));
        }

        .info-tile .tile-icon {
            width: 36px;
            height: 36px;
            background: rgba(245, 158, 11, 0.15);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .info-tile .tile-icon i {
            color: var(--primary);
            font-size: 14px;
        }

        .info-tile .tile-label {
            font-size: 11px;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
        }

        .info-tile .tile-value {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-white);
        }

        /* ============================================
           CUSTOMER INFO
           ============================================ */
        .customer-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .customer-tile {
            background: var(--dark-700);
            padding: 16px;
            border-radius: var(--radius-md);
            border-left: 3px solid var(--primary);
        }

        .customer-tile .c-label {
            font-size: 12px;
            color: var(--text-dark);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .customer-tile .c-label i {
            color: var(--primary);
        }

        .customer-tile .c-value {
            font-size: 15px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* ============================================
           POLICY LIST
           ============================================ */
        .policy-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .policy-item {
            display: flex;
            gap: 14px;
            padding: 16px;
            background: var(--dark-700);
            border-radius: var(--radius-md);
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .policy-item:hover {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.05);
        }

        .policy-icon {
            width: 40px;
            height: 40px;
            background: var(--success-glow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .policy-icon i {
            color: var(--success);
            font-size: 16px;
        }

        .policy-content h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 4px;
        }

        .policy-content p {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* ============================================
           SUMMARY CARD (STICKY)
           ============================================ */
        .summary-card {
            background: linear-gradient(180deg, var(--dark-700), var(--dark-800));
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 28px;
            position: sticky;
            top: 100px;
            box-shadow: 
                var(--glass-shadow),
                0 0 60px rgba(245, 158, 11, 0.05);
        }

        .summary-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--glass-border);
        }

        .summary-header i {
            font-size: 22px;
            color: var(--primary);
        }

        .summary-header h3 {
            font-size: 20px;
            font-weight: 700;
        }

        /* Price Rows */
        .price-list {
            margin-bottom: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed var(--dark-500);
        }

        .price-row:last-child {
            border-bottom: none;
        }

        .price-row .p-label {
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .price-row .p-label i {
            color: var(--primary);
            font-size: 12px;
            width: 16px;
        }

        .price-row .p-value {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-light);
        }

        /* Total Row */
        .total-row {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
            margin: 20px -28px;
            padding: 20px 28px;
            border-top: 2px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-row .t-label {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-white);
        }

        .total-row .t-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            text-shadow: 0 0 30px var(--primary-glow);
        }

        /* Terms */
        .terms-box {
            background: var(--dark-700);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 20px;
        }

        .terms-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .terms-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .terms-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-label a:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: var(--radius-lg);
            color: var(--dark-900);
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px var(--primary-glow);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn i {
            font-size: 20px;
        }

        /* Secure Badge */
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 16px;
            padding: 14px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: var(--radius-md);
            font-size: 13px;
            color: var(--success);
        }

        .secure-badge i {
            font-size: 18px;
        }

        /* Back Link */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            color: var(--text-dark);
            font-size: 14px;
            text-decoration: none;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--danger);
        }

        /* Error Alert */
        .error-alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--danger);
            font-size: 14px;
        }

        .error-alert i {
            font-size: 20px;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-card {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .page-container {
                padding: 24px 16px 40px;
            }
            
            .page-header h1 {
                font-size: 26px;
            }
            
            .progress-container {
                gap: 0;
            }
            
            .step-line {
                width: 30px;
            }
            
            .step-label {
                display: none;
            }
            
            .car-preview {
                flex-direction: column;
                text-align: center;
            }
            
            .car-image-box {
                width: 100%;
                height: 180px;
            }
            
            .car-specs {
                justify-content: center;
            }
            
            .info-grid,
            .customer-grid {
                grid-template-columns: 1fr;
            }
            
            .total-row .t-value {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            .breadcrumb-container {
                font-size: 12px;
            }
            
            .glass-card {
                padding: 20px;
            }
            
            .summary-card {
                padding: 20px;
            }
            
            .total-row {
                margin: 20px -20px;
                padding: 16px 20px;
            }
        }
    </style>
</head>
<body>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <div class="breadcrumb-container">
        <a href="../index.php"><i class="fas fa-home"></i> Home</a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <a href="cars.php">Cars</a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <a href="car_details.php?id=<?php echo $car_id; ?>"><?php echo htmlspecialchars($car['name']); ?></a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <span class="current">Confirm Booking</span>
    </div>
</nav>

<!-- Page Container -->
<div class="page-container">

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-calendar-check"></i> Confirm Your Booking</h1>
        <p>Review your details before proceeding to payment</p>
    </div>

    <!-- Progress Steps -->
    <div class="progress-container">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <span class="step-label">Select Car</span>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">2</div>
            <span class="step-label">Confirm Details</span>
        </div>
        <div class="step-line"></div>
        <div class="step pending">
            <div class="step-circle">3</div>
            <span class="step-label">Payment</span>
        </div>
        <div class="step-line"></div>
        <div class="step pending">
            <div class="step-circle">4</div>
            <span class="step-label">Complete</span>
        </div>
    </div>

    <!-- Error Alert -->
    <?php if (isset($error)): ?>
    <div class="error-alert">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $error; ?></span>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="" id="bookingForm">
        <div class="booking-grid">

            <!-- Left Column -->
            <div class="left-column">

                <!-- Car Details Card -->
                <div class="glass-card">
                    <div class="card-header">
                        <i class="fas fa-car-side"></i>
                        <h3>Car Details</h3>
                    </div>
                    <div class="car-preview">
                        <div class="car-image-box">
                            <img src="../uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($car['name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/200x140/1e293b/94a3b8?text=No+Image'">
                        </div>
                        <div class="car-info-box">
                            <h4><?php echo htmlspecialchars($car['name']); ?></h4>
                            <div class="car-type-tag">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($car['type']); ?>
                            </div>
                            <div class="car-specs">
                                <span class="spec-item"><i class="fas fa-users"></i> 5 Seats</span>
                                <span class="spec-item"><i class="fas fa-cog"></i> Manual</span>
                                <span class="spec-item"><i class="fas fa-gas-pump"></i> Petrol</span>
                                <span class="spec-item"><i class="fas fa-snowflake"></i> AC</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Information Card -->
                <div class="glass-card">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Booking Information</h3>
                    </div>
                    <div class="info-grid">
                        <div class="info-tile">
                            <div class="tile-icon"><i class="fas fa-calendar"></i></div>
                            <div class="tile-label">Pick-up Date</div>
                            <div class="tile-value"><?php echo date('D, d M Y', strtotime($pickup_date)); ?></div>
                        </div>
                        <div class="info-tile">
                            <div class="tile-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="tile-label">Return Date</div>
                            <div class="tile-value"><?php echo date('D, d M Y', strtotime($return_date)); ?></div>
                        </div>
                        <div class="info-tile">
                            <div class="tile-icon"><i class="fas fa-clock"></i></div>
                            <div class="tile-label">Duration</div>
                            <div class="tile-value"><?php echo $days; ?> Day<?php echo $days > 1 ? 's' : ''; ?></div>
                        </div>
                        <div class="info-tile">
                            <div class="tile-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="tile-label">Pick-up Location</div>
                            <div class="tile-value"><?php echo htmlspecialchars($pickup_location); ?></div>
                        </div>
                        <div class="info-tile">
                            <div class="tile-icon"><i class="fas fa-hourglass-start"></i></div>
                            <div class="tile-label">Pick-up Time</div>
                            <div class="tile-value"><?php echo $pickup_time; ?></div>
                        </div>
                        <div class="info-tile">
                            <div class="tile-icon"><i class="fas fa-rupee-sign"></i></div>
                            <div class="tile-label">Daily Rate</div>
                            <div class="tile-value">₹<?php echo number_format($price_per_day); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Customer Details Card -->
                <div class="glass-card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                        <h3>Customer Details</h3>
                    </div>
                    <div class="customer-grid">
                        <div class="customer-tile">
                            <div class="c-label"><i class="fas fa-user"></i> Full Name</div>
                            <div class="c-value"><?php echo htmlspecialchars($user['name']); ?></div>
                        </div>
                        <div class="customer-tile">
                            <div class="c-label"><i class="fas fa-envelope"></i> Email Address</div>
                            <div class="c-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Rental Policies Card -->
                <div class="glass-card">
                    <div class="card-header">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Rental Policies</h3>
                    </div>
                    <div class="policy-list">
                        <div class="policy-item">
                            <div class="policy-icon"><i class="fas fa-undo"></i></div>
                            <div class="policy-content">
                                <h5>Free Cancellation</h5>
                                <p>Cancel up to 24 hours before pickup for a full refund.</p>
                            </div>
                        </div>
                        <div class="policy-item">
                            <div class="policy-icon"><i class="fas fa-gas-pump"></i></div>
                            <div class="policy-content">
                                <h5>Fuel Policy</h5>
                                <p>Full to full - receive with full tank, return with full tank.</p>
                            </div>
                        </div>
                        <div class="policy-item">
                            <div class="policy-icon"><i class="fas fa-car-crash"></i></div>
                            <div class="policy-content">
                                <h5>Insurance Included</h5>
                                <p>Basic insurance coverage is included in your rental price.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column - Summary -->
            <div class="right-column">
                <div class="summary-card">
                    <div class="summary-header">
                        <i class="fas fa-receipt"></i>
                        <h3>Price Summary</h3>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="price-list">
                        <div class="price-row">
                            <span class="p-label">
                                <i class="fas fa-car"></i>
                                ₹<?php echo number_format($price_per_day); ?> × <?php echo $days; ?> day<?php echo $days > 1 ? 's' : ''; ?>
                            </span>
                            <span class="p-value">₹<?php echo number_format($subtotal); ?></span>
                        </div>

                        <?php if ($location_fee > 0): ?>
                        <div class="price-row">
                            <span class="p-label">
                                <i class="fas fa-truck"></i>
                                Delivery Charge
                            </span>
                            <span class="p-value">₹<?php echo number_format($location_fee); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="price-row">
                            <span class="p-label">
                                <i class="fas fa-shield-alt"></i>
                                Insurance (₹100/day)
                            </span>
                            <span class="p-value">₹<?php echo number_format($insurance_fee); ?></span>
                        </div>

                        <div class="price-row">
                            <span class="p-label">
                                <i class="fas fa-percentage"></i>
                                Service Fee (5%)
                            </span>
                            <span class="p-value">₹<?php echo number_format($service_fee); ?></span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="total-row">
                        <span class="t-label">Total Amount</span>
                        <span class="t-value">₹<?php echo number_format($total_amount); ?></span>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="terms-box">
                        <label class="terms-label">
                            <input type="checkbox" name="accept_terms" id="acceptTerms" required>
                            <span>I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Rental Policy</a></span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="proceed_payment" value="1" class="submit-btn" id="submitBtn">
                        <i class="fas fa-lock"></i>
                        <span>PROCEED TO PAYMENT</span>
                    </button>

                    <!-- Secure Badge -->
                    <div class="secure-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>256-bit SSL Secured Payment</span>
                    </div>

                    <!-- Back Link -->
                    <a href="car_details.php?id=<?php echo $car_id; ?>" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>Go back & modify</span>
                    </a>

                </div>
            </div>

        </div>
    </form>

</div>

<script>
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    var checkbox = document.getElementById('acceptTerms');
    var btn = document.getElementById('submitBtn');
    
    if (!checkbox.checked) {
        e.preventDefault();
        alert('Please accept the Terms & Conditions to proceed.');
        return false;
    }
    
    // Show loading
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>PROCESSING...</span>';
    btn.style.pointerEvents = 'none';
    btn.style.opacity = '0.8';
    
    return true;
});
</script>

<?php include("../includes/footer.php"); ?>
</body>
</html>