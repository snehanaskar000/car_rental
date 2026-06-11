<?php
session_start();
include("../includes/db.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if booking data exists OR booking_id is provided
if(isset($_GET['booking_id'])) {
    // Coming from direct booking confirmation
    $booking_id = (int)$_GET['booking_id'];
    
    // Fetch booking details
    $bookingQuery = mysqli_query($conn, "
        SELECT b.*, c.name as car_name, c.type as car_type, c.image as car_image, c.price_per_day,
               u.name as user_name, u.email as user_email
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = $booking_id AND b.user_id = {$_SESSION['user_id']}
    ");
    
    if(mysqli_num_rows($bookingQuery) == 0) {
        header("Location: my_bookings.php");
        exit();
    }
    
    $booking = mysqli_fetch_assoc($bookingQuery);
    
    // Calculate days
    $days = (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24);
    
    // Set booking data from database
    $booking_data = [
        'booking_id' => $booking_id,
        'car_id' => $booking['car_id'],
        'car_name' => $booking['car_name'],
        'car_type' => $booking['car_type'],
        'car_image' => $booking['car_image'],
        'pickup_date' => $booking['start_date'],
        'return_date' => $booking['end_date'],
        'days' => $days,
        'total_amount' => $booking['total_price'],
        'user_name' => $booking['user_name'],
        'user_email' => $booking['user_email']
    ];
    
} elseif(isset($_SESSION['booking_data'])) {
    // Coming from booking.php
    $booking_data = $_SESSION['booking_data'];
    $booking_id = isset($_SESSION['current_booking_id']) ? $_SESSION['current_booking_id'] : null;
    
    // Get user details
    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = {$_SESSION['user_id']}");
    $user = mysqli_fetch_assoc($userQuery);
    $booking_data['user_name'] = $user['name'];
    $booking_data['user_email'] = $user['email'];
    
} else {
    header("Location: cars.php");
    exit();
}

// Handle payment submission
$payment_error = '';
$payment_success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    
    if(empty($payment_method)) {
        $payment_error = "Please select a payment method.";
    } else {
        
        // If booking doesn't exist yet, create it
        if(!$booking_id) {
            $car_id = $booking_data['car_id'];
            $user_id = $_SESSION['user_id'];
            $start_date = $booking_data['pickup_date'];
            $end_date = $booking_data['return_date'];
            $total_price = $booking_data['total_amount'];
            
            // Insert booking
            $insertBooking = mysqli_query($conn, "
                INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status) 
                VALUES ($user_id, $car_id, '$start_date', '$end_date', $total_price, 'confirmed')
            ");
            
            if($insertBooking) {
                $booking_id = mysqli_insert_id($conn);
            } else {
                $payment_error = "Failed to create booking. Please try again.";
            }
        } else {
            // Update existing booking status
            mysqli_query($conn, "UPDATE bookings SET status = 'confirmed' WHERE id = $booking_id");
        }
        
        if($booking_id && empty($payment_error)) {
            // Check if payment record exists
            $paymentCheck = mysqli_query($conn, "SELECT id FROM payments WHERE booking_id = $booking_id");
            
            if(mysqli_num_rows($paymentCheck) > 0) {
                // Update existing payment
                $updatePayment = mysqli_query($conn, "
                    UPDATE payments SET status = 'paid' WHERE booking_id = $booking_id
                ");
            } else {
                // Create new payment record
                $amount = $booking_data['total_amount'];
                $insertPayment = mysqli_query($conn, "
                    INSERT INTO payments (booking_id, amount, status) 
                    VALUES ($booking_id, $amount, 'paid')
                ");
            }
            
            // Update car status to booked (optional)
            // mysqli_query($conn, "UPDATE cars SET status = 'booked' WHERE id = {$booking_data['car_id']}");
            
            // Clear session data
            unset($_SESSION['booking_data']);
            unset($_SESSION['current_booking_id']);
            unset($_SESSION['booking_amount']);
            
            // Store success booking ID
            $_SESSION['payment_success'] = true;
            $_SESSION['completed_booking_id'] = $booking_id;
            
            // Redirect to success page
            header("Location: booking_success.php?id=$booking_id");
            exit();
        }
    }
}

include("../includes/header.php");
?>

<style>
/* ==========================================
   CSS VARIABLES
   ========================================== */
:root {
    --primary: #F59E0B;
    --primary-dark: #D97706;
    --secondary: #3B82F6;
    --dark: #0f172a;
    --dark-light: #1e293b;
    --dark-lighter: #334155;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
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
    min-height: 100vh;
}

/* Breadcrumb */
.breadcrumb {
    background: var(--dark-light);
    padding: 15px 60px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    gap: 10px;
    list-style: none;
    max-width: 1200px;
    margin: 0 auto;
}

.breadcrumb-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.breadcrumb-list li a {
    color: var(--text-secondary);
    text-decoration: none;
}

.breadcrumb-list li a:hover {
    color: var(--primary);
}

.breadcrumb-list li.active {
    color: var(--primary);
}

.breadcrumb-list li i.fa-chevron-right {
    font-size: 10px;
    color: var(--text-muted);
}

/* Payment Page */
.payment-page {
    padding: 40px 60px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 32px;
    margin-bottom: 10px;
}

.page-header p {
    color: var(--text-secondary);
}

/* Progress Steps */
.progress-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
}

.step {
    display: flex;
    align-items: center;
    gap: 8px;
}

.step-num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.step.completed .step-num {
    background: var(--success);
    color: white;
}

.step.active .step-num {
    background: var(--primary);
    color: var(--dark);
}

.step.pending .step-num {
    background: var(--dark-lighter);
    color: var(--text-muted);
}

.step-label {
    font-size: 14px;
}

.step.completed .step-label { color: var(--success); }
.step.active .step-label { color: var(--primary); }
.step.pending .step-label { color: var(--text-muted); }

.step-line {
    width: 50px;
    height: 2px;
    background: var(--dark-lighter);
}

.step-line.completed {
    background: var(--success);
}

/* Payment Grid */
.payment-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    align-items: start;
}

/* Payment Methods */
.payment-section {
    background: var(--dark-light);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.section-title {
    font-size: 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary);
}

/* Payment Method Options */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.payment-method {
    background: var(--dark-lighter);
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 15px;
}

.payment-method:hover {
    border-color: rgba(245, 158, 11, 0.3);
}

.payment-method.selected {
    border-color: var(--primary);
    background: rgba(245, 158, 11, 0.1);
}

.payment-method input[type="radio"] {
    display: none;
}

.method-radio {
    width: 22px;
    height: 22px;
    border: 2px solid var(--text-muted);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: var(--transition);
}

.payment-method.selected .method-radio {
    border-color: var(--primary);
}

.payment-method.selected .method-radio::after {
    content: '';
    width: 12px;
    height: 12px;
    background: var(--primary);
    border-radius: 50%;
}

.method-icon {
    width: 50px;
    height: 50px;
    background: var(--dark);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: var(--primary);
}

.method-info {
    flex: 1;
}

.method-info h4 {
    font-size: 16px;
    margin-bottom: 4px;
}

.method-info p {
    font-size: 13px;
    color: var(--text-secondary);
}

.method-logos {
    display: flex;
    gap: 8px;
}

.method-logos img {
    height: 24px;
    opacity: 0.8;
}

/* Card Form */
.card-form {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: none;
}

.card-form.show {
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.form-group label i {
    color: var(--primary);
    margin-right: 5px;
}

.form-group input {
    width: 100%;
    padding: 14px 16px;
    background: var(--dark);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 15px;
    transition: var(--transition);
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary);
}

.form-group input::placeholder {
    color: var(--text-muted);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* UPI Section */
.upi-section {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: none;
}

.upi-section.show {
    display: block;
}

.upi-apps {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.upi-app {
    background: var(--dark);
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 15px 25px;
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
}

.upi-app:hover {
    border-color: rgba(245, 158, 11, 0.3);
}

.upi-app.selected {
    border-color: var(--primary);
    background: rgba(245, 158, 11, 0.1);
}

.upi-app i {
    font-size: 28px;
    color: var(--primary);
    display: block;
    margin-bottom: 8px;
}

.upi-app span {
    font-size: 12px;
    color: var(--text-secondary);
}

.upi-id-input {
    margin-top: 15px;
}

/* QR Section */
.qr-section {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
    display: none;
}

.qr-section.show {
    display: block;
}

.qr-code {
    background: white;
    padding: 20px;
    border-radius: 15px;
    display: inline-block;
    margin-bottom: 15px;
}

.qr-code img {
    width: 180px;
    height: 180px;
}

.qr-instructions {
    font-size: 14px;
    color: var(--text-secondary);
}

/* Secure Badge */
.secure-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 25px;
    padding: 15px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 10px;
    font-size: 13px;
    color: var(--success);
}

.secure-info i {
    font-size: 18px;
}

/* Order Summary */
.order-summary {
    background: var(--dark-light);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    position: sticky;
    top: 100px;
}

.order-summary h3 {
    font-size: 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.order-summary h3 i {
    color: var(--primary);
}

/* Car Preview */
.car-preview {
    display: flex;
    gap: 15px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.car-preview-image {
    width: 100px;
    height: 70px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}

.car-preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.car-preview-info h4 {
    font-size: 16px;
    margin-bottom: 5px;
}

.car-preview-info p {
    font-size: 13px;
    color: var(--text-secondary);
}

/* Booking Details */
.booking-details {
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 14px;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-row .label i {
    color: var(--primary);
    font-size: 12px;
    width: 16px;
}

.detail-row .value {
    font-weight: 500;
}

/* Price Summary */
.price-summary {
    background: var(--dark-lighter);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.price-row .label {
    color: var(--text-secondary);
}

.price-row.total {
    padding-top: 15px;
    margin-top: 10px;
    border-top: 2px solid rgba(255, 255, 255, 0.1);
}

.price-row.total .label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 16px;
}

.price-row.total .value {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
}

/* Pay Button */
.pay-btn {
    width: 100%;
    padding: 16px;
    background: var(--gradient);
    border: none;
    border-radius: 12px;
    color: var(--dark);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.pay-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
}

.pay-btn:disabled {
    background: var(--dark-lighter);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pay-btn i {
    font-size: 18px;
}

/* Cancel Link */
.cancel-link {
    display: block;
    text-align: center;
    margin-top: 15px;
    color: var(--text-muted);
    font-size: 14px;
    text-decoration: none;
}

.cancel-link:hover {
    color: var(--danger);
}

/* Guarantees */
.guarantees {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.guarantee-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.guarantee-item i {
    color: var(--success);
}

/* Alert */
.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.alert.error {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Toast */
.toast-container {
    position: fixed;
    top: 100px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: var(--dark-light);
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: var(--shadow);
    animation: slideIn 0.3s ease;
}

.toast.success { border-left: 4px solid var(--success); }
.toast.error { border-left: 4px solid var(--danger); }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Responsive */
@media (max-width: 1024px) {
    .payment-grid {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
        order: -1;
    }
}

@media (max-width: 768px) {
    .payment-page {
        padding: 20px;
    }
    
    .breadcrumb {
        padding: 15px 20px;
    }
    
    .progress-steps {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .step-line {
        display: none;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .upi-apps {
        justify-content: center;
    }
    
    .method-logos {
        display: none;
    }
}
</style>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <ul class="breadcrumb-list">
        <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><i class="fas fa-chevron-right"></i></li>
        <li><a href="cars.php">Cars</a></li>
        <li><i class="fas fa-chevron-right"></i></li>
        <li><a href="car_details.php?id=<?php echo $booking_data['car_id']; ?>"><?php echo htmlspecialchars($booking_data['car_name']); ?></a></li>
        <li><i class="fas fa-chevron-right"></i></li>
        <li class="active">Payment</li>
    </ul>
</nav>

<!-- Payment Page -->
<div class="payment-page">
    
    <!-- Header -->
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Secure Payment</h1>
        <p>Complete your booking by making a secure payment</p>
    </div>
    
    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step completed">
            <div class="step-num"><i class="fas fa-check"></i></div>
            <span class="step-label">Select Car</span>
        </div>
        <div class="step-line completed"></div>
        <div class="step completed">
            <div class="step-num"><i class="fas fa-check"></i></div>
            <span class="step-label">Booking Details</span>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-num">3</div>
            <span class="step-label">Payment</span>
        </div>
        <div class="step-line"></div>
        <div class="step pending">
            <div class="step-num">4</div>
            <span class="step-label">Confirmation</span>
        </div>
    </div>
    
    <form method="POST" action="" id="paymentForm">
        <div class="payment-grid">
            
            <!-- Payment Methods -->
            <div class="payment-section">
                <h2 class="section-title"><i class="fas fa-wallet"></i> Select Payment Method</h2>
                
                <!-- Error Message -->
                <?php if(!empty($payment_error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $payment_error; ?>
                </div>
                <?php endif; ?>
                
                <div class="payment-methods">
                    
                    <!-- Credit/Debit Card -->
                    <label class="payment-method" data-method="card">
                        <input type="radio" name="payment_method" value="card">
                        <span class="method-radio"></span>
                        <div class="method-icon"><i class="fas fa-credit-card"></i></div>
                        <div class="method-info">
                            <h4>Credit / Debit Card</h4>
                            <p>Visa, Mastercard, Rupay</p>
                        </div>
                        <div class="method-logos">
                            <img src="https://img.icons8.com/color/48/visa.png" alt="Visa">
                            <img src="https://img.icons8.com/color/48/mastercard.png" alt="Mastercard">
                        </div>
                    </label>
                    
                    <!-- UPI -->
                    <label class="payment-method" data-method="upi">
                        <input type="radio" name="payment_method" value="upi">
                        <span class="method-radio"></span>
                        <div class="method-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="method-info">
                            <h4>UPI</h4>
                            <p>Google Pay, PhonePe, Paytm</p>
                        </div>
                    </label>
                    
                    <!-- Net Banking -->
                    <label class="payment-method" data-method="netbanking">
                        <input type="radio" name="payment_method" value="netbanking">
                        <span class="method-radio"></span>
                        <div class="method-icon"><i class="fas fa-university"></i></div>
                        <div class="method-info">
                            <h4>Net Banking</h4>
                            <p>All major banks supported</p>
                        </div>
                    </label>
                    
                    <!-- Pay at Pickup -->
                    <label class="payment-method" data-method="cash">
                        <input type="radio" name="payment_method" value="cash">
                        <span class="method-radio"></span>
                        <div class="method-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="method-info">
                            <h4>Pay at Pickup</h4>
                            <p>Cash or card at pickup location</p>
                        </div>
                    </label>
                    
                </div>
                
                <!-- Card Form -->
                <div class="card-form" id="cardForm">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Cardholder Name</label>
                        <input type="text" name="card_name" placeholder="Name on card">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Card Number</label>
                        <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" id="cardNumber">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Expiry Date</label>
                            <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5" id="cardExpiry">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> CVV</label>
                            <input type="password" name="card_cvv" placeholder="***" maxlength="4">
                        </div>
                    </div>
                </div>
                
                <!-- UPI Section -->
                <div class="upi-section" id="upiSection">
                    <p style="margin-bottom: 15px; color: var(--text-secondary);">Choose UPI App:</p>
                    <div class="upi-apps">
                        <div class="upi-app" data-app="gpay">
                            <i class="fab fa-google"></i>
                            <span>Google Pay</span>
                        </div>
                        <div class="upi-app" data-app="phonepe">
                            <i class="fas fa-mobile-alt"></i>
                            <span>PhonePe</span>
                        </div>
                        <div class="upi-app" data-app="paytm">
                            <i class="fas fa-wallet"></i>
                            <span>Paytm</span>
                        </div>
                        <div class="upi-app" data-app="other">
                            <i class="fas fa-ellipsis-h"></i>
                            <span>Other</span>
                        </div>
                    </div>
                    <div class="upi-id-input">
                        <div class="form-group">
                            <label><i class="fas fa-at"></i> UPI ID</label>
                            <input type="text" name="upi_id" placeholder="yourname@upi">
                        </div>
                    </div>
                </div>
                
                <!-- QR Section -->
                <div class="qr-section" id="qrSection">
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=upi://pay?pa=merchant@upi%26am=<?php echo $booking_data['total_amount']; ?>" alt="QR Code">
                    </div>
                    <p class="qr-instructions">
                        Scan with any UPI app to pay<br>
                        <strong>₹<?php echo number_format($booking_data['total_amount']); ?></strong>
                    </p>
                </div>
                
                <!-- Secure Badge -->
                <div class="secure-info">
                    <i class="fas fa-shield-alt"></i>
                    <span>Your payment is secured with 256-bit SSL encryption</span>
                </div>
                
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                
                <!-- Car Preview -->
                <div class="car-preview">
                    <div class="car-preview-image">
                        <img src="../uploads/car_images/<?php echo htmlspecialchars($booking_data['car_image']); ?>" 
                             alt="<?php echo htmlspecialchars($booking_data['car_name']); ?>"
                             onerror="this.src='https://via.placeholder.com/100x70?text=Car'">
                    </div>
                    <div class="car-preview-info">
                        <h4><?php echo htmlspecialchars($booking_data['car_name']); ?></h4>
                        <p><?php echo htmlspecialchars($booking_data['car_type']); ?></p>
                    </div>
                </div>
                
                <!-- Booking Details -->
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-calendar"></i> Pick-up</span>
                        <span class="value"><?php echo date('d M Y', strtotime($booking_data['pickup_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-calendar-check"></i> Return</span>
                        <span class="value"><?php echo date('d M Y', strtotime($booking_data['return_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-clock"></i> Duration</span>
                        <span class="value"><?php echo $booking_data['days']; ?> Day<?php echo $booking_data['days'] > 1 ? 's' : ''; ?></span>
                    </div>
                    <?php if(isset($booking_data['pickup_location'])): ?>
                    <div class="detail-row">
                        <span class="label"><i class="fas fa-map-marker-alt"></i> Location</span>
                        <span class="value"><?php echo htmlspecialchars($booking_data['pickup_location']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Price Summary -->
                <div class="price-summary">
                    <?php if(isset($booking_data['subtotal'])): ?>
                    <div class="price-row">
                        <span class="label">Rental (<?php echo $booking_data['days']; ?> days)</span>
                        <span class="value">₹<?php echo number_format($booking_data['subtotal']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($booking_data['insurance_fee'])): ?>
                    <div class="price-row">
                        <span class="label">Insurance</span>
                        <span class="value">₹<?php echo number_format($booking_data['insurance_fee']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($booking_data['service_fee'])): ?>
                    <div class="price-row">
                        <span class="label">Service Fee</span>
                        <span class="value">₹<?php echo number_format($booking_data['service_fee']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($booking_data['location_fee']) && $booking_data['location_fee'] > 0): ?>
                    <div class="price-row">
                        <span class="label">Delivery</span>
                        <span class="value">₹<?php echo number_format($booking_data['location_fee']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="price-row total">
                        <span class="label">Total</span>
                        <span class="value">₹<?php echo number_format($booking_data['total_amount']); ?></span>
                    </div>
                </div>
                
                <!-- Pay Button -->
                <button type="submit" name="process_payment" class="pay-btn" id="payBtn">
                    <i class="fas fa-lock"></i>
                    <span>Pay ₹<?php echo number_format($booking_data['total_amount']); ?></span>
                </button>
                
                <!-- Cancel -->
                <a href="car_details.php?id=<?php echo $booking_data['car_id']; ?>" class="cancel-link">
                    <i class="fas fa-arrow-left"></i> Cancel and go back
                </a>
                
                <!-- Guarantees -->
                <div class="guarantees">
                    <div class="guarantee-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Free cancellation up to 24 hours before pickup</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Instant booking confirmation</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="fas fa-check-circle"></i>
                        <span>24/7 customer support</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const paymentMethods = document.querySelectorAll('.payment-method');
    const cardForm = document.getElementById('cardForm');
    const upiSection = document.getElementById('upiSection');
    const qrSection = document.getElementById('qrSection');
    const payBtn = document.getElementById('payBtn');
    const form = document.getElementById('paymentForm');
    
    // Payment method selection
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove selected from all
            paymentMethods.forEach(m => m.classList.remove('selected'));
            // Add selected to clicked
            this.classList.add('selected');
            // Check radio
            this.querySelector('input').checked = true;
            
            // Hide all sections
            cardForm.classList.remove('show');
            upiSection.classList.remove('show');
            qrSection.classList.remove('show');
            
            // Show relevant section
            const methodType = this.dataset.method;
            if(methodType === 'card') {
                cardForm.classList.add('show');
            } else if(methodType === 'upi') {
                upiSection.classList.add('show');
            }
            
            // Update button text
            if(methodType === 'cash') {
                payBtn.innerHTML = '<i class="fas fa-check"></i> <span>Confirm Booking</span>';
            } else {
                payBtn.innerHTML = '<i class="fas fa-lock"></i> <span>Pay ₹<?php echo number_format($booking_data['total_amount']); ?></span>';
            }
        });
    });
    
    // UPI app selection
    const upiApps = document.querySelectorAll('.upi-app');
    upiApps.forEach(app => {
        app.addEventListener('click', function() {
            upiApps.forEach(a => a.classList.remove('selected'));
            this.classList.add('selected');
            
            // Show QR for any selection
            qrSection.classList.add('show');
        });
    });
    
    // Card number formatting
    const cardNumber = document.getElementById('cardNumber');
    if(cardNumber) {
        cardNumber.addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '').replace(/\D/g, '');
            let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formatted;
        });
    }
    
    // Expiry date formatting
    const cardExpiry = document.getElementById('cardExpiry');
    if(cardExpiry) {
        cardExpiry.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if(value.length >= 2) {
                value = value.substring(0,2) + '/' + value.substring(2);
            }
            this.value = value;
        });
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if(!selectedMethod) {
            e.preventDefault();
            showToast('Please select a payment method', 'error');
            return;
        }
        
        // Show loading
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
        payBtn.disabled = true;
    });
    
    // Toast function
    function showToast(message, type) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i><span>' + message + '</span>';
        container.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }
});
</script>

<?php include("../includes/footer.php"); ?>