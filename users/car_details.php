<?php
session_start();
include("../includes/db.php");

// Check if car ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$car_id = (int)$_GET['id'];

// Fetch car details
$carQuery = mysqli_query($conn, "SELECT * FROM cars WHERE id = $car_id");

if(!$carQuery || mysqli_num_rows($carQuery) == 0) {
    header("Location: cars.php");
    exit();
}

$car = mysqli_fetch_assoc($carQuery);

// Get pre-filled dates from URL if available
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+1 day'));

// Fetch related cars
$relatedQuery = mysqli_query($conn, "SELECT * FROM cars WHERE type = '".$car['type']."' AND id != $car_id AND status = 'available' LIMIT 3");

// Function to check car availability
function isCarAvailable($conn, $car_id, $start, $end) {
    if(empty($start) || empty($end)) return true;
    
    $query = "SELECT id FROM bookings 
              WHERE car_id = $car_id 
              AND status NOT IN ('cancelled', 'completed')
              AND ((start_date BETWEEN '$start' AND '$end') 
              OR (end_date BETWEEN '$start' AND '$end')
              OR (start_date <= '$start' AND end_date >= '$end'))";
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) == 0;
}

// Initialize variables
$booking_error = '';
$booking_success = '';

// ==========================================
// HANDLE FORM SUBMISSION
// ==========================================
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $pickup_date = isset($_POST['pickup_date']) ? trim($_POST['pickup_date']) : '';
    $return_date = isset($_POST['return_date']) ? trim($_POST['return_date']) : '';
    $pickup_location = isset($_POST['pickup_location']) ? trim($_POST['pickup_location']) : '';
    $pickup_time = isset($_POST['pickup_time']) ? trim($_POST['pickup_time']) : '10:00';
    
    // Validation
    $today = date('Y-m-d');
    
    if(empty($pickup_date)) {
        $booking_error = "Please select a pick-up date.";
    } elseif(empty($return_date)) {
        $booking_error = "Please select a return date.";
    } elseif(empty($pickup_location)) {
        $booking_error = "Please select a pick-up location.";
    } elseif($pickup_date < $today) {
        $booking_error = "Pick-up date cannot be in the past.";
    } elseif($return_date <= $pickup_date) {
        $booking_error = "Return date must be after pick-up date.";
    } elseif($car['status'] != 'available') {
        $booking_error = "Sorry, this car is currently not available.";
    } elseif(!isCarAvailable($conn, $car_id, $pickup_date, $return_date)) {
        $booking_error = "This car is already booked for the selected dates.";
    } else {
        
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            // Store booking intent in session
            $_SESSION['pending_booking'] = [
                'car_id' => $car_id,
                'pickup_date' => $pickup_date,
                'return_date' => $return_date,
                'pickup_time' => $pickup_time,
                'pickup_location' => $pickup_location
            ];
            
            // Redirect to login
            header("Location: ../login.php?redirect=users/car_details.php&id=$car_id&message=login_required");
            exit();
        }
        
        // User is logged in - proceed with booking
        $user_id = $_SESSION['user_id'];
        
        // Calculate booking details
        $days = (strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24);
        $price_per_day = $car['price_per_day'];
        $subtotal = $days * $price_per_day;
        
        // Calculate additional charges
        $service_fee = $subtotal * 0.05;
        $insurance_fee = $days * 100;
        $location_fee = 0;
        
        if($pickup_location == 'Hotel Delivery') {
            $location_fee = 200;
        } elseif($pickup_location == 'Home Delivery') {
            $location_fee = 300;
        }
        
        $total_amount = $subtotal + $service_fee + $insurance_fee + $location_fee;
        
        // Store booking data in session
        $_SESSION['booking_data'] = [
            'car_id' => $car_id,
            'car_name' => $car['name'],
            'car_type' => $car['type'],
            'car_image' => $car['image'],
            'car_price' => $car['price_per_day'],
            'user_id' => $user_id,
            'pickup_date' => $pickup_date,
            'return_date' => $return_date,
            'pickup_time' => $pickup_time,
            'pickup_location' => $pickup_location,
            'days' => $days,
            'price_per_day' => $price_per_day,
            'subtotal' => $subtotal,
            'service_fee' => $service_fee,
            'insurance_fee' => $insurance_fee,
            'location_fee' => $location_fee,
            'total_amount' => $total_amount
        ];
        
        // Redirect to booking page
        header("Location: booking.php");
        exit();
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
    max-width: 1400px;
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
    transition: var(--transition);
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

/* Page Container */
.car-details-page {
    padding: 40px 60px;
    max-width: 1400px;
    margin: 0 auto;
}

.car-details-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    align-items: start;
}

/* Car Gallery */
.car-gallery {
    background: var(--dark-light);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.main-image {
    position: relative;
    height: 400px;
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.main-image:hover img {
    transform: scale(1.05);
}

.car-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    gap: 10px;
}

.badge {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge.available {
    background: var(--success);
    color: white;
}

.badge.booked {
    background: var(--danger);
    color: white;
}

.badge.type-badge {
    background: var(--secondary);
    color: white;
}

.car-actions {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    gap: 10px;
}

.action-btn {
    width: 42px;
    height: 42px;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 10px;
    color: white;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.action-btn:hover {
    background: var(--primary);
    color: var(--dark);
}

.action-btn.liked {
    background: var(--danger);
}

/* Car Info Section */
.car-info-section {
    background: var(--dark-light);
    border-radius: 20px;
    padding: 25px;
    margin-top: 20px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.car-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
}

.car-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.car-type-badge {
    background: rgba(59, 130, 246, 0.15);
    color: var(--secondary);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
}

.car-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    color: var(--primary);
    font-size: 14px;
}

.car-rating span {
    color: var(--text-secondary);
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin: 25px 0;
}

.feature-box {
    background: var(--dark-lighter);
    padding: 18px;
    border-radius: 12px;
    text-align: center;
}

.feature-box i {
    font-size: 22px;
    color: var(--primary);
    margin-bottom: 10px;
    display: block;
}

.feature-box .label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    display: block;
    margin-bottom: 5px;
}

.feature-box .value {
    font-size: 14px;
    font-weight: 600;
}

/* Section Title */
.section-title {
    font-size: 18px;
    margin: 25px 0 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.section-title i {
    color: var(--primary);
}

/* Description */
.description {
    color: var(--text-secondary);
    line-height: 1.8;
    font-size: 14px;
}

/* Features List */
.features-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    list-style: none;
}

.features-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--text-secondary);
    padding: 10px 15px;
    background: var(--dark-lighter);
    border-radius: 8px;
}

.features-list li i {
    color: var(--success);
    font-size: 12px;
}

/* ==========================================
   BOOKING CARD (SIDEBAR)
   ========================================== */
.booking-sidebar {
    position: sticky;
    top: 100px;
}

.booking-card {
    background: var(--dark-light);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.booking-header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.price-display .amount {
    font-size: 40px;
    font-weight: 700;
    color: var(--primary);
}

.price-display .period {
    font-size: 16px;
    color: var(--text-muted);
}

.availability-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    margin-top: 10px;
}

.availability-status.available {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
}

.availability-status.unavailable {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
}

/* Alert Messages */
.alert {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
}

.alert i {
    margin-top: 2px;
}

.alert.error {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.alert.success {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.alert.info {
    background: rgba(59, 130, 246, 0.15);
    color: var(--secondary);
    border: 1px solid rgba(59, 130, 246, 0.3);
}

/* Booking Form */
.booking-form .form-group {
    margin-bottom: 15px;
}

.booking-form label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 6px;
}

.booking-form label i {
    color: var(--primary);
    margin-right: 5px;
}

.booking-form input,
.booking-form select {
    width: 100%;
    padding: 12px 14px;
    background: var(--dark-lighter);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 14px;
    transition: var(--transition);
}

.booking-form input:focus,
.booking-form select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.booking-form select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Price Summary */
.price-summary {
    background: var(--dark-lighter);
    padding: 15px;
    border-radius: 12px;
    margin: 15px 0;
}

.price-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
}

.price-row:last-child {
    border-bottom: none;
}

.price-row .label {
    color: var(--text-secondary);
}

.price-row .value {
    font-weight: 500;
}

.price-row.total {
    padding-top: 12px;
    margin-top: 8px;
    border-top: 2px solid rgba(255, 255, 255, 0.1);
    border-bottom: none;
}

.price-row.total .label {
    font-weight: 600;
    color: var(--text-primary);
}

.price-row.total .value {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
}

/* Book Now Button */
.book-btn {
    width: 100%;
    padding: 15px;
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

.book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
}

.book-btn:disabled {
    background: var(--dark-lighter);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.book-btn.loading {
    pointer-events: none;
}

/* Login Prompt */
.login-prompt {
    text-align: center;
    padding: 15px;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 10px;
    margin-top: 15px;
    font-size: 14px;
    color: var(--text-secondary);
}

.login-prompt a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
}

.login-prompt a:hover {
    text-decoration: underline;
}

/* Contact Buttons */
.contact-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.contact-btn {
    flex: 1;
    padding: 12px;
    background: var(--dark-lighter);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: var(--text-secondary);
    font-size: 13px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: var(--transition);
}

.contact-btn:hover {
    background: rgba(245, 158, 11, 0.1);
    color: var(--primary);
    border-color: rgba(245, 158, 11, 0.3);
}

/* Trust Badges */
.trust-badges {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.trust-item i {
    color: var(--success);
}

/* Related Cars */
.related-section {
    margin-top: 40px;
}

.related-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.related-header h2 {
    font-size: 22px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.related-header h2 i {
    color: var(--primary);
}

.related-header a {
    color: var(--primary);
    font-size: 14px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.related-card {
    background: var(--dark-light);
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: var(--transition);
}

.related-card:hover {
    transform: translateY(-5px);
    border-color: rgba(245, 158, 11, 0.3);
}

.related-card .image {
    height: 160px;
    overflow: hidden;
}

.related-card .image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.related-card:hover .image img {
    transform: scale(1.1);
}

.related-card .info {
    padding: 15px;
}

.related-card h4 {
    font-size: 16px;
    margin-bottom: 10px;
}

.related-card .meta {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 12px;
    color: var(--text-secondary);
}

.related-card .meta span i {
    color: var(--primary);
    margin-right: 4px;
}

.related-card .footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.related-card .price {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
}

.related-card .price span {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 400;
}

.related-card .view-btn {
    padding: 8px 16px;
    background: var(--dark-lighter);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 13px;
    text-decoration: none;
    transition: var(--transition);
}

.related-card .view-btn:hover {
    background: var(--primary);
    color: var(--dark);
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
    color: var(--text-primary);
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
.toast.info { border-left: 4px solid var(--secondary); }

.toast.success i { color: var(--success); }
.toast.error i { color: var(--danger); }
.toast.info i { color: var(--secondary); }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Responsive */
@media (max-width: 1024px) {
    .car-details-grid { grid-template-columns: 1fr; }
    .booking-sidebar { position: static; }
}

@media (max-width: 768px) {
    .car-details-page { padding: 20px; }
    .breadcrumb { padding: 15px 20px; }
    .features-grid { grid-template-columns: repeat(2, 1fr); }
    .features-list { grid-template-columns: 1fr; }
    .related-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
    .main-image { height: 280px; }
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
        <li class="active"><?php echo htmlspecialchars($car['name']); ?></li>
    </ul>
</nav>

<!-- Car Details Page -->
<div class="car-details-page">
    <div class="car-details-grid">
        
        <!-- Left Column -->
        <div class="car-main">
            <!-- Gallery -->
            <div class="car-gallery">
                <div class="main-image">
                    <img src="../uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" 
                         alt="<?php echo htmlspecialchars($car['name']); ?>"
                         id="mainImage"
                         onerror="this.src='https://via.placeholder.com/800x400?text=Car+Image'">
                    
                    <div class="car-badges">
                        <?php if($car['status'] == 'available'): ?>
                            <span class="badge available"><i class="fas fa-check"></i> Available</span>
                        <?php else: ?>
                            <span class="badge booked"><i class="fas fa-times"></i> Booked</span>
                        <?php endif; ?>
                        <span class="badge type-badge"><?php echo htmlspecialchars($car['type']); ?></span>
                    </div>
                    
                    <div class="car-actions">
                        <button class="action-btn" id="favBtn" title="Favorite">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="action-btn" id="shareBtn" title="Share">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Info -->
            <div class="car-info-section">
                <h1 class="car-title"><?php echo htmlspecialchars($car['name']); ?></h1>
                
                <div class="car-meta">
                    <span class="car-type-badge"><?php echo htmlspecialchars($car['type']); ?></span>
                    <div class="car-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>4.5 (120 reviews)</span>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="features-grid">
                    <div class="feature-box">
                        <i class="fas fa-users"></i>
                        <span class="label">Seats</span>
                        <span class="value">5 Persons</span>
                    </div>
                    <div class="feature-box">
                        <i class="fas fa-cog"></i>
                        <span class="label">Transmission</span>
                        <span class="value">Manual</span>
                    </div>
                    <div class="feature-box">
                        <i class="fas fa-gas-pump"></i>
                        <span class="label">Fuel</span>
                        <span class="value">Petrol</span>
                    </div>
                    <div class="feature-box">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="label">Mileage</span>
                        <span class="value">15 km/l</span>
                    </div>
                </div>
                
                <h3 class="section-title"><i class="fas fa-info-circle"></i> Description</h3>
                <p class="description">
                    Experience comfort and style with the <?php echo htmlspecialchars($car['name']); ?>. 
                    Perfect for both city drives and long trips. Features modern amenities and excellent fuel efficiency.
                </p>
                
                <h3 class="section-title"><i class="fas fa-check-circle"></i> Features</h3>
                <ul class="features-list">
                    <li><i class="fas fa-check"></i> Air Conditioning</li>
                    <li><i class="fas fa-check"></i> Power Steering</li>
                    <li><i class="fas fa-check"></i> Power Windows</li>
                    <li><i class="fas fa-check"></i> Central Locking</li>
                    <li><i class="fas fa-check"></i> ABS</li>
                    <li><i class="fas fa-check"></i> Airbags</li>
                    <li><i class="fas fa-check"></i> Music System</li>
                    <li><i class="fas fa-check"></i> Bluetooth</li>
                </ul>
            </div>
        </div>
        
        <!-- Right Column - Booking Card -->
        <div class="booking-sidebar">
            <div class="booking-card">
                <div class="booking-header">
                    <div class="price-display">
                        <span class="amount">₹<?php echo number_format($car['price_per_day']); ?></span>
                        <span class="period">/ day</span>
                    </div>
                    <?php if($car['status'] == 'available'): ?>
                        <div class="availability-status available">
                            <i class="fas fa-check-circle"></i> Available
                        </div>
                    <?php else: ?>
                        <div class="availability-status unavailable">
                            <i class="fas fa-times-circle"></i> Not Available
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Show Error -->
                <?php if(!empty($booking_error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $booking_error; ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Booking Form -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $car_id; ?>" class="booking-form" id="bookingForm">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Pick-up Date</label>
                            <input type="date" name="pickup_date" id="pickupDate" 
                                   value="<?php echo htmlspecialchars($start_date); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Return Date</label>
                            <input type="date" name="return_date" id="returnDate" 
                                   value="<?php echo htmlspecialchars($end_date); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Pick-up Time</label>
                        <select name="pickup_time" id="pickupTime">
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00" selected>10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                            <option value="18:00">06:00 PM</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Pick-up Location</label>
                        <select name="pickup_location" id="pickupLocation" required>
                            <option value="">-- Select Location --</option>
                            <option value="Airport">Airport</option>
                            <option value="City Center">City Center</option>
                            <option value="Railway Station">Railway Station</option>
                            <option value="Bus Stand">Bus Stand</option>
                            <option value="Hotel Delivery">Hotel Delivery (+₹200)</option>
                            <option value="Home Delivery">Home Delivery (+₹300)</option>
                        </select>
                    </div>
                    
                    <!-- Price Summary -->
                    <div class="price-summary">
                        <div class="price-row">
                            <span class="label">Daily Rate</span>
                            <span class="value">₹<?php echo number_format($car['price_per_day']); ?></span>
                        </div>
                        <div class="price-row">
                            <span class="label">Days</span>
                            <span class="value" id="numDays">1</span>
                        </div>
                        <div class="price-row">
                            <span class="label">Subtotal</span>
                            <span class="value" id="subtotal">₹<?php echo number_format($car['price_per_day']); ?></span>
                        </div>
                        <div class="price-row">
                            <span class="label">Insurance</span>
                            <span class="value" id="insurance">₹100</span>
                        </div>
                        <div class="price-row">
                            <span class="label">Service Fee (5%)</span>
                            <span class="value" id="serviceFee">₹<?php echo number_format($car['price_per_day'] * 0.05); ?></span>
                        </div>
                        <div class="price-row" id="deliveryRow" style="display: none;">
                            <span class="label">Delivery</span>
                            <span class="value" id="deliveryFee">₹0</span>
                        </div>
                        <div class="price-row total">
                            <span class="label">Total</span>
                            <span class="value" id="totalAmount">₹<?php echo number_format($car['price_per_day'] * 1.05 + 100); ?></span>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <?php if($car['status'] == 'available'): ?>
                        <button type="submit" class="book-btn" id="bookBtn">
                            <i class="fas fa-arrow-right"></i>
                            <span>Continue to Booking</span>
                        </button>
                        
                        <?php if(!isset($_SESSION['user_id'])): ?>
                        <div class="login-prompt">
                            <i class="fas fa-info-circle"></i> 
                            <a href="../login.php">Login</a> or 
                            <a href="../register.php">Register</a> to book
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="book-btn" disabled>
                            <i class="fas fa-ban"></i> Currently Unavailable
                        </button>
                    <?php endif; ?>
                </form>
                
                <!-- Contact -->
                <div class="contact-buttons">
                    <a href="tel:+919876543210" class="contact-btn">
                        <i class="fas fa-phone"></i> Call
                    </a>
                    <a href="https://wa.me/919876543210" target="_blank" class="contact-btn">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
                
                <!-- Trust -->
                <div class="trust-badges">
                    <div class="trust-item"><i class="fas fa-shield-alt"></i> Secure Payment</div>
                    <div class="trust-item"><i class="fas fa-undo"></i> Free Cancellation</div>
                    <div class="trust-item"><i class="fas fa-headset"></i> 24/7 Support</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Cars -->
    <?php if(mysqli_num_rows($relatedQuery) > 0): ?>
    <div class="related-section">
        <div class="related-header">
            <h2><i class="fas fa-car"></i> Similar Cars</h2>
            <a href="cars.php?type=<?php echo urlencode($car['type']); ?>">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="related-grid">
            <?php while($related = mysqli_fetch_assoc($relatedQuery)): ?>
            <div class="related-card">
                <div class="image">
                    <img src="../uploads/car_images/<?php echo htmlspecialchars($related['image']); ?>" 
                         alt="<?php echo htmlspecialchars($related['name']); ?>"
                         onerror="this.src='https://via.placeholder.com/400x200?text=Car'">
                </div>
                <div class="info">
                    <h4><?php echo htmlspecialchars($related['name']); ?></h4>
                    <div class="meta">
                        <span><i class="fas fa-car"></i> <?php echo htmlspecialchars($related['type']); ?></span>
                        <span><i class="fas fa-users"></i> 5 Seats</span>
                    </div>
                    <div class="footer">
                        <span class="price">₹<?php echo number_format($related['price_per_day']); ?> <span>/day</span></span>
                        <a href="car_details.php?id=<?php echo $related['id']; ?>" class="view-btn">View</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const pricePerDay = <?php echo $car['price_per_day']; ?>;
    const pickupDate = document.getElementById('pickupDate');
    const returnDate = document.getElementById('returnDate');
    const pickupLocation = document.getElementById('pickupLocation');
    const bookBtn = document.getElementById('bookBtn');
    const form = document.getElementById('bookingForm');
    
    // Set min dates
    const today = new Date().toISOString().split('T')[0];
    pickupDate.min = today;
    
    if(!pickupDate.value || pickupDate.value < today) {
        pickupDate.value = today;
    }
    
    function updateReturnMin() {
        const nextDay = new Date(pickupDate.value);
        nextDay.setDate(nextDay.getDate() + 1);
        const minReturn = nextDay.toISOString().split('T')[0];
        returnDate.min = minReturn;
        
        if(!returnDate.value || returnDate.value <= pickupDate.value) {
            returnDate.value = minReturn;
        }
    }
    
    updateReturnMin();
    
    // Event listeners
    pickupDate.addEventListener('change', function() {
        updateReturnMin();
        calculatePrice();
    });
    
    returnDate.addEventListener('change', calculatePrice);
    pickupLocation.addEventListener('change', calculatePrice);
    
    // Calculate price
    function calculatePrice() {
        const start = new Date(pickupDate.value);
        const end = new Date(returnDate.value);
        
        if(end > start) {
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            const subtotal = days * pricePerDay;
            const insurance = days * 100;
            const serviceFee = subtotal * 0.05;
            
            let deliveryFee = 0;
            if(pickupLocation.value === 'Hotel Delivery') deliveryFee = 200;
            if(pickupLocation.value === 'Home Delivery') deliveryFee = 300;
            
            const total = subtotal + insurance + serviceFee + deliveryFee;
            
            document.getElementById('numDays').textContent = days;
            document.getElementById('subtotal').textContent = '₹' + subtotal.toLocaleString('en-IN');
            document.getElementById('insurance').textContent = '₹' + insurance.toLocaleString('en-IN');
            document.getElementById('serviceFee').textContent = '₹' + Math.round(serviceFee).toLocaleString('en-IN');
            document.getElementById('totalAmount').textContent = '₹' + Math.round(total).toLocaleString('en-IN');
            
            const deliveryRow = document.getElementById('deliveryRow');
            if(deliveryFee > 0) {
                deliveryRow.style.display = 'flex';
                document.getElementById('deliveryFee').textContent = '₹' + deliveryFee;
            } else {
                deliveryRow.style.display = 'none';
            }
        }
    }
    
    calculatePrice();
    
    // Form submission with validation
    if(form) {
        form.addEventListener('submit', function(e) {
            // Basic validation
            if(!pickupDate.value) {
                e.preventDefault();
                showToast('Please select pick-up date', 'error');
                return;
            }
            
            if(!returnDate.value) {
                e.preventDefault();
                showToast('Please select return date', 'error');
                return;
            }
            
            if(!pickupLocation.value) {
                e.preventDefault();
                showToast('Please select pick-up location', 'error');
                pickupLocation.focus();
                return;
            }
            
            const start = new Date(pickupDate.value);
            const end = new Date(returnDate.value);
            
            if(end <= start) {
                e.preventDefault();
                showToast('Return date must be after pick-up date', 'error');
                return;
            }
            
            // Show loading
            if(bookBtn) {
                bookBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                bookBtn.classList.add('loading');
            }
            
            // Form will submit naturally
            console.log('Form submitting...');
        });
    }
    
    // Favorite button
    const favBtn = document.getElementById('favBtn');
    if(favBtn) {
        favBtn.addEventListener('click', function() {
            this.classList.toggle('liked');
            const icon = this.querySelector('i');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            showToast(this.classList.contains('liked') ? 'Added to favorites!' : 'Removed from favorites', 'success');
        });
    }
    
    // Share button
    const shareBtn = document.getElementById('shareBtn');
    if(shareBtn) {
        shareBtn.addEventListener('click', function() {
            if(navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($car['name']); ?>',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                showToast('Link copied!', 'success');
            }
        });
    }
    
    // Toast function
    function showToast(message, type) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i><span>' + message + '</span>';
        container.appendChild(toast);
        
        setTimeout(function() {
            toast.remove();
        }, 3000);
    }
    
    window.showToast = showToast;
});
</script>

<?php include("../includes/footer.php"); ?>