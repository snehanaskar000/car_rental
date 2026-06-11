<?php
session_start();
include("../includes/db.php");

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch confirmed booking details
$query = "SELECT b.*, c.name as car_name, c.image as car_image, c.type as car_type
          FROM bookings b 
          JOIN cars c ON b.car_id = c.id 
          WHERE b.id = $booking_id AND b.user_id = $user_id";

$result = mysqli_query($conn, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: ../index.php");
    exit();
}

include("../includes/header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed! | Car Rental</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #F59E0B;
            --success: #10b981;
            --dark: #0a0e17;
            --card: #1e293b;
            --text: #f8fafc;
        }

        body {
            background: var(--dark);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .success-container {
            max-width: 600px;
            margin: 60px auto;
            text-align: center;
            padding: 20px;
        }

        /* Animated Checkmark */
        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            margin: 0 auto 30px;
            border: 2px solid var(--success);
            animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes scaleUp {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        h1 { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .sub-text { color: #94a3b8; margin-bottom: 40px; }

        .receipt-card {
            background: var(--card);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: left;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .booking-id { color: var(--gold); font-weight: 600; font-size: 14px; }
        
        .car-details { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; }
        .car-details img { width: 80px; height: 50px; object-fit: cover; border-radius: 8px; }
        .car-details h4 { margin: 0; font-size: 18px; }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .info-label { color: #94a3b8; }
        
        .total-amount {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-price { font-size: 24px; font-weight: 700; color: var(--gold); }

        .actions { margin-top: 40px; display: flex; gap: 15px; justify-content: center; }
        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            font-size: 14px;
        }
        .btn-primary { background: var(--gold); color: var(--dark); }
        .btn-secondary { background: rgba(255, 255, 255, 0.05); color: white; border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn:hover { transform: translateY(-3px); opacity: 0.9; }

        /* Print Button */
        .print-link { margin-top: 20px; display: block; color: #64748b; font-size: 13px; cursor: pointer; text-decoration: underline; }

        @media print {
            .actions, .print-link, nav, footer { display: none !important; }
            .receipt-card { border: none; box-shadow: none; background: white; color: black; }
            .info-label, .sub-text { color: #333 !important; }
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon">
        <i class="fas fa-check"></i>
    </div>
    
    <h1>Booking Confirmed!</h1>
    <p class="sub-text">Thank you for your payment. Your ride is ready!</p>

    <div class="receipt-card">
        <div class="receipt-header">
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Status</p>
                <span style="color: var(--success); font-weight: 600; font-size: 14px;"><i class="fas fa-circle" style="font-size: 8px; vertical-align: middle;"></i> Payment Received</span>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Booking ID</p>
                <span class="booking-id">#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
        </div>

        <div class="car-details">
            <img src="../uploads/car_images/<?php echo $booking['car_image']; ?>" onerror="this.src='https://via.placeholder.com/80x50'">
            <div>
                <h4><?php echo $booking['car_name']; ?></h4>
                <small style="color: var(--gold);"><?php echo $booking['car_type']; ?></small>
            </div>
        </div>

        <div class="info-row">
            <span class="info-label">Pick-up Date</span>
            <span class="info-value"><?php echo date('d M, Y', strtotime($booking['start_date'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Return Date</span>
            <span class="info-value"><?php echo date('d M, Y', strtotime($booking['end_date'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Method</span>
            <span class="info-value">Razorpay (Online)</span>
        </div>

        <div class="total-amount">
            <span style="font-weight: 600;">Total Paid</span>
            <span class="total-price">₹<?php echo number_format($booking['total_price']); ?></span>
        </div>
    </div>

    <div class="actions">
        <a href="../index.php" class="btn btn-secondary">Back to Home</a>
        <a href="my_bookings.php" class="btn btn-primary">My Bookings</a>
    </div>

    <span class="print-link" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</span>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>