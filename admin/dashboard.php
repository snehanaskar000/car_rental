<?php
session_start();

// Redirect if not admin
if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

// DB CONNECTION
$conn = mysqli_connect("localhost", "root", "", "car_rental");

// ========== STATISTICS ==========
$cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cars"))['total'];
$bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings"))['total'];
$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total FROM bookings"))['total'] ?? 0;

// Pending Bookings
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status='Pending'"))['total'];

// Recent Bookings (Last 5)
$recent_bookings = mysqli_query($conn, "SELECT b.*, c.name as car_name, u.email as user_email 
                                         FROM bookings b 
                                         JOIN cars c ON b.car_id = c.id 
                                         JOIN users u ON b.user_id = u.id 
                                         ORDER BY b.id DESC LIMIT 5");

// Available Cars
$available_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cars WHERE status='available'"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CarRental</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gold: #F59E0B;
            --gold-hover: #D97706;
            --dark-bg: #020617;
            --sidebar-bg: #0f172a;
            --card-bg: #1e293b;
            --text-gray: #94A3B8;
            --border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            display: flex;
            background: var(--dark-bg);
            color: white;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            padding: 30px 20px;
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo i {
            font-size: 28px;
            color: var(--gold);
        }

        .sidebar-logo h2 {
            font-size: 22px;
            font-weight: 800;
        }

        .sidebar-logo span {
            color: var(--gold);
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-label {
            font-size: 11px;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: 0.3s;
            font-size: 14px;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(245, 158, 11, 0.1);
            color: var(--gold);
        }

        .sidebar a i {
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            margin-top: 30px;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444 !important;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 700;
        }

        .welcome-text {
            color: var(--text-gray);
            font-size: 14px;
            margin-top: 5px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .quick-btn {
            padding: 10px 20px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quick-btn:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        /* ========== STATS CARDS ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.15);
            border-color: var(--gold);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.1), transparent);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-card:nth-child(1) .stat-icon { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .stat-card:nth-child(2) .stat-icon { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .stat-card:nth-child(3) .stat-icon { background: rgba(245, 158, 11, 0.2); color: var(--gold); }
        .stat-card:nth-child(4) .stat-icon { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 5px;
            background: linear-gradient(135deg, white, var(--text-gray));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 14px;
            font-weight: 500;
        }

        .stat-badge {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 12px;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        /* ========== CONTENT SECTIONS ========== */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 25px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .view-all {
            color: var(--gold);
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* Recent Bookings Table */
        .bookings-table {
            width: 100%;
        }

        .bookings-table tr {
            border-bottom: 1px solid var(--border);
        }

        .bookings-table td {
            padding: 15px 8px;
            font-size: 14px;
        }

        .bookings-table tr:last-child {
            border-bottom: none;
        }

        .booking-car {
            font-weight: 600;
            color: white;
        }

        .booking-user {
            color: var(--text-gray);
            font-size: 13px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--gold);
        }

        .status-confirmed {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-btn {
            padding: 15px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
        }

        .action-btn:hover {
            background: rgba(245, 158, 11, 0.2);
            border-color: var(--gold);
        }

        .action-btn i {
            width: 40px;
            height: 40px;
            background: var(--gold);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .main-content { margin-left: 70px; padding: 20px; }
            .sidebar-logo h2, .nav-label, .sidebar a span { display: none; }
            .sidebar-logo { justify-content: center; }
        }
    </style>
</head>
<body>

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-car-side"></i>
        <h2>CAR<span>RENTAL</span></h2>
    </div>

    <nav>
        <div class="nav-section">
            <div class="nav-label">Main Menu</div>
            <a href="dashboard.php" class="active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_cars.php">
                <i class="fas fa-car"></i>
                <span>Manage Cars</span>
            </a>
            <a href="bookings.php">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
            <a href="users.php">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
             <a href="manage_contacts.php">
                <i class="fas fa-users"></i>
                <span>Contacts</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Settings</div>
            <a href="../index.php">
                <i class="fas fa-globe"></i>
                <span>View Site</span>
            </a>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- ========== MAIN CONTENT ========== -->
<main class="main-content">

    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1>Dashboard Overview</h1>
            <p class="welcome-text">Welcome back, Admin! Here's what's happening today.</p>
        </div>
        <div class="header-actions">
            <a href="manage_cars.php" class="quick-btn">
                <i class="fas fa-plus"></i> Add New Car
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo $cars; ?></div>
                    <div class="stat-label">Total Cars</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-car"></i>
                </div>
            </div>
            <div class="stat-badge"><?php echo $available_cars; ?> Available</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo $bookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="stat-badge"><?php echo $pending; ?> Pending</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-number">₹<?php echo number_format($revenue); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
            <div class="stat-badge">+12% this month</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo $users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-badge">+<?php echo rand(5, 15); ?> new</div>
        </div>

    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        
        <!-- Recent Bookings -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Bookings</h3>
                <a href="bookings.php" class="view-all">View All →</a>
            </div>

            <table class="bookings-table">
                <?php if(mysqli_num_rows($recent_bookings) > 0): ?>
                    <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                        <tr>
                            <td>
                                <div class="booking-car"><?php echo $booking['car_name']; ?></div>
                                <div class="booking-user"><?php echo $booking['user_email']; ?></div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo $booking['status']; ?>
                                </span>
                            </td>
                            <td style="color: var(--gold); font-weight: 600;">
                                ₹<?php echo number_format($booking['total_price']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; color:var(--text-gray)">No bookings yet</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>

            <div class="quick-actions">
                <a href="manage_cars.php" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <div>
                        <div style="font-weight:600">Add New Car</div>
                        <div style="font-size:12px; color:var(--text-gray)">Expand your fleet</div>
                    </div>
                </a>

                <a href="bookings.php?filter=pending" class="action-btn">
                    <i class="fas fa-clock"></i>
                    <div>
                        <div style="font-weight:600">Pending Bookings</div>
                        <div style="font-size:12px; color:var(--text-gray)"><?php echo $pending; ?> waiting</div>
                    </div>
                </a>

                <a href="users.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    <div>
                        <div style="font-weight:600">Manage Users</div>
                        <div style="font-size:12px; color:var(--text-gray)"><?php echo $users; ?> registered</div>
                    </div>
                </a>
            </div>
        </div>

    </div>

</main>

</body>
</html>