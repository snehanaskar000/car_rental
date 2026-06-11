<?php
session_start();
include("../includes/db.php");

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User's Bookings with Car Details
$query = "SELECT b.*, c.name as car_name, c.image as car_image, c.type as car_type, p.status as payment_status
          FROM bookings b
          JOIN cars c ON b.car_id = c.id
          LEFT JOIN payments p ON b.id = p.booking_id
          WHERE b.user_id = $user_id
          ORDER BY b.id DESC";

$result = mysqli_query($conn, $query);

include("../includes/header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Car Rental</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --gold: #F59E0B;
            --dark: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --success: #10b981;
            --pending: #f59e0b;
            --danger: #ef4444;
        }

        body { background: var(--dark); color: var(--text); font-family: 'Poppins', sans-serif; }
        
        .container { max-width: 1000px; margin: 50px auto; padding: 0 20px; }
        
        .page-title { margin-bottom: 30px; font-size: 28px; font-weight: 700; border-left: 5px solid var(--gold); padding-left: 15px; }

        /* Booking Card */
        .booking-card {
            background: var(--card);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: grid;
            grid-template-columns: 120px 1fr 180px;
            gap: 20px;
            align-items: center;
            transition: 0.3s;
        }

        .booking-card:hover { transform: scale(1.01); border-color: rgba(245, 158, 11, 0.3); }

        .car-img { width: 120px; height: 80px; object-fit: cover; border-radius: 10px; }

        .booking-info h4 { margin: 0 0 5px 0; font-size: 18px; }
        .booking-meta { display: flex; gap: 15px; font-size: 13px; color: #94a3b8; margin-top: 8px; }
        .booking-meta i { color: var(--gold); margin-right: 5px; }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-confirmed { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: var(--pending); }
        
        .price-tag { font-size: 20px; font-weight: 800; color: var(--gold); text-align: right; }
        .view-btn { 
            display: block; 
            text-align: right; 
            font-size: 12px; 
            color: #94a3b8; 
            text-decoration: none; 
            margin-top: 10px; 
        }
        .view-btn:hover { color: var(--gold); }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; background: var(--card); border-radius: 20px; }
        .empty-state i { font-size: 50px; color: #334155; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="page-title">My Bookings</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="booking-card">
                <div class="car-img-wrapper">
                    <img src="../uploads/car_images/<?= $row['car_image'] ?>" class="car-img" onerror="this.src='https://via.placeholder.com/120x80'">
                </div>

                <div class="booking-info">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4><?= htmlspecialchars($row['car_name']) ?></h4>
                        <span class="badge <?= ($row['status'] == 'confirmed') ? 'status-confirmed' : 'status-pending' ?>">
                            <?= $row['status'] ?>
                        </span>
                    </div>
                    <p style="font-size: 13px; color: #94a3b8;"><?= $row['car_type'] ?></p>
                    
                    <div class="booking-meta">
                        <span><i class="fas fa-calendar-alt"></i> <?= date('d M', strtotime($row['start_date'])) ?> - <?= date('d M, Y', strtotime($row['end_date'])) ?></span>
                        <span><i class="fas fa-receipt"></i> ID: #<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span><i class="fas fa-credit-card"></i> <?= ucfirst($row['payment_status'] ?? 'Unpaid') ?></span>
                    </div>
                </div>

                <div>
                    <div class="price-tag">₹<?= number_format($row['total_price']) ?></div>
                    <?php if ($row['payment_status'] == 'unpaid' || is_null($row['payment_status'])): ?>
                        <a href="payment.php?booking_id=<?= $row['id'] ?>" class="view-btn" style="color: var(--pending); font-weight: bold;">
                            Pay Now <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="booking_success.php?id=<?= $row['id'] ?>" class="view-btn">
                            View Receipt <i class="fas fa-external-link-alt"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-car-side"></i>
            <h3>No bookings found</h3>
            <p style="color: #94a3b8; margin-top: 10px;">You haven't rented any cars yet. Start your journey today!</p>
            <a href="cars.php" class="btn" style="display:inline-block; margin-top:20px; background: var(--gold); padding: 10px 25px; border-radius: 8px; color: var(--dark); text-decoration:none; font-weight:600;">Browse Cars</a>
        </div>
    <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>