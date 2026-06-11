<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    header("Location: cars.php");
    exit();
}

$booking_id = (int)$_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// 1. Fetch details (Removed u.phone to prevent the SQL error you had)
$bookingQuery = mysqli_query($conn, "
    SELECT b.*, c.name as car_name, c.type as car_type, c.image as car_image,
           u.name as user_name, u.email as user_email
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = $booking_id AND b.user_id = $user_id
");

if (mysqli_num_rows($bookingQuery) == 0) {
    header("Location: cars.php");
    exit();
}

$booking = mysqli_fetch_assoc($bookingQuery);
$amount_in_paise = round($booking['total_price'] * 100);

// 2. Razorpay Config (Keys must be exactly as provided by Razorpay)
$razorpay_key_id = "rzp_test_STui2tERIeLURC"; 
$razorpay_key_secret = "J3PWrTq8mVSfAkTlpBj20gLg";

// 3. Handle Success Redirect (This part runs after the JS submits the hidden form)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['razorpay_payment_id'])) {
    $payment_id = $_POST['razorpay_payment_id'];
    
    // Update Booking & Payment Status
    mysqli_query($conn, "UPDATE bookings SET status = 'confirmed' WHERE id = $booking_id");
    mysqli_query($conn, "UPDATE payments SET status = 'paid', payment_id = '$payment_id' WHERE booking_id = $booking_id");

    unset($_SESSION['booking_data']);
    header("Location: booking_success.php?id=$booking_id");
    exit();
}

include("../includes/header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root { --gold: #F59E0B; --dark: #0f172a; --card: #1e293b; --text: #f8fafc; }
        body { background: var(--dark); color: var(--text); font-family: 'Poppins', sans-serif; }
        .pay-container { max-width: 500px; margin: 80px auto; text-align: center; }
        .glass-card { background: var(--card); padding: 40px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        .btn-pay { width: 100%; padding: 18px; background: var(--gold); border: none; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 18px; margin-top: 20px; transition: 0.3s; }
        .btn-pay:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(245,158,11,0.3); }
        .price { font-size: 36px; font-weight: 800; color: var(--gold); margin: 10px 0; }
    </style>
</head>
<body>

<div class="pay-container">
    <div class="glass-card">
        <i class="fas fa-shield-alt" style="font-size: 50px; color: #10b981; margin-bottom: 20px;"></i>
        <h2>Secure Checkout</h2>
        <p style="color: #94a3b8; margin-top: 10px;">Booking #<?=str_pad($booking_id, 5, '0', STR_PAD_LEFT)?></p>
        
        <div class="price">₹<?=number_format($booking['total_price'])?></div>
        <p><?=$booking['car_name']?></p>

        <button id="rzp-button" class="btn-pay">PAY NOW</button>
        
        <div style="margin-top: 25px; font-size: 12px; color: #64748b;">
            <p><i class="fas fa-lock"></i> Secured by Razorpay</p>
        </div>
    </div>
</div>

<form method="POST" id="razorpay-form" style="display: none;">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
</form>

<script>
document.getElementById('rzp-button').onclick = function(e) {
    var options = {
        "key": "<?=$razorpay_key_id?>",
        "amount": "<?=$amount_in_paise?>",
        "currency": "INR",
        "name": "Car Rental Service",
        "description": "Payment for Booking #<?=$booking_id?>",
        "image": "https://cdn-icons-png.flaticon.com/512/3774/3774278.png",
        "handler": function (response) {
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay-form').submit();
        },
        "prefill": {
            "name": "<?=$booking['user_name']?>",
            "email": "<?=$booking['user_email']?>",
            "contact": "9999999999"
        },
        "theme": { "color": "#F59E0B" }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
    e.preventDefault();
}
</script>

</body>
</html>