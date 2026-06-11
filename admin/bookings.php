<?php
session_start();

// Redirect if not admin
if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

// DB CONNECTION
$conn = mysqli_connect("localhost", "root", "", "car_rental");

// ========== HANDLE ACTIONS ==========

// CONFIRM BOOKING
if(isset($_GET['confirm'])){
    $id = (int)$_GET['confirm'];
    mysqli_query($conn, "UPDATE bookings SET status='Confirmed' WHERE id='$id'");
    header("Location: bookings.php?msg=confirmed");
    exit;
}

// CANCEL BOOKING
if(isset($_POST['cancel_booking'])){
    $id = (int)$_POST['booking_id'];
    $refund_amount = (float)$_POST['refund_amount'];
    $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason']);
    
    mysqli_query($conn, "UPDATE bookings SET 
        status='Cancelled', 
        refund_amount='$refund_amount',
        cancel_reason='$cancel_reason',
        cancelled_at=NOW() 
        WHERE id='$id'");
    
    header("Location: bookings.php?msg=cancelled");
    exit;
}

// TOP UP / ADD CHARGES
if(isset($_POST['add_topup'])){
    $id = (int)$_POST['booking_id'];
    $charge_type = mysqli_real_escape_string($conn, $_POST['charge_type']);
    $charge_amount = (float)$_POST['charge_amount'];
    $charge_note = mysqli_real_escape_string($conn, $_POST['charge_note']);
    
    // Add to charges table
    mysqli_query($conn, "INSERT INTO booking_charges (booking_id, charge_type, amount, note, created_at) 
                         VALUES ('$id', '$charge_type', '$charge_amount', '$charge_note', NOW())");
    
    // Update total price in bookings
    mysqli_query($conn, "UPDATE bookings SET total_price = total_price + $charge_amount WHERE id='$id'");
    
    header("Location: bookings.php?msg=topup_added");
    exit;
}

// ========== FILTERS ==========
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// ========== FETCH BOOKINGS ==========
$query = "
    SELECT 
        bookings.*, 
        users.name as user_name, 
        users.email as user_email,
        users.phone as user_phone,
        cars.name as car_name,
        cars.image as car_image,
        cars.type as car_type,
        cars.price_per_day
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN cars ON bookings.car_id = cars.id
    WHERE 1=1
";

if($status_filter != "") {
    $query .= " AND bookings.status='$status_filter'";
}

if($search != "") {
    $query .= " AND (users.name LIKE '%$search%' OR cars.name LIKE '%$search%' OR bookings.id LIKE '%$search%')";
}

$query .= " ORDER BY bookings.id DESC";

$result = mysqli_query($conn, $query);
$total_bookings = mysqli_num_rows($result);

// ========== STATISTICS ==========
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='Confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status='Cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status='Confirmed' THEN total_price ELSE 0 END) as revenue
    FROM bookings
"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management | Admin</title>
    
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
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { display: flex; background: var(--dark-bg); color: white; min-height: 100vh; }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            padding: 30px 20px;
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo i { font-size: 28px; color: var(--gold); }
        .sidebar-logo h2 { font-size: 22px; font-weight: 800; }
        .sidebar-logo span { color: var(--gold); }

        .nav-section { margin-bottom: 30px; }
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

        .sidebar a i { width: 20px; text-align: center; }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); }
        .alert-warning { background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: var(--warning); }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 10px; }
        .breadcrumb { color: var(--text-gray); font-size: 14px; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card:nth-child(1) .stat-icon { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .stat-card:nth-child(2) .stat-icon { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .stat-card:nth-child(3) .stat-icon { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .stat-card:nth-child(4) .stat-icon { background: rgba(59, 130, 246, 0.2); color: var(--info); }

        .stat-label { color: var(--text-gray); font-size: 13px; margin-bottom: 5px; }
        .stat-value { font-size: 2rem; font-weight: 800; color: white; }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 45px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: white;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .status-tabs {
            display: flex;
            gap: 10px;
            background: var(--card-bg);
            padding: 5px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .tab-btn {
            padding: 10px 20px;
            background: transparent;
            border: none;
            color: var(--text-gray);
            cursor: pointer;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
        }

        .tab-btn:hover, .tab-btn.active {
            background: rgba(245, 158, 11, 0.15);
            color: var(--gold);
        }

        /* Table */
        .table-container {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--sidebar-bg); }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-gray);
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr { transition: 0.2s; }
        tbody tr:hover { background: rgba(245, 158, 11, 0.05); }
        tbody tr:last-child td { border-bottom: none; }

        .booking-id { font-weight: 700; color: var(--gold); }

        .user-info { display: flex; align-items: center; gap: 12px; }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
        }

        .user-details h4 { font-size: 14px; margin-bottom: 3px; }
        .user-contact { font-size: 12px; color: var(--text-gray); }

        .car-info { display: flex; align-items: center; gap: 12px; }

        .car-thumb {
            width: 60px;
            height: 45px;
            border-radius: 6px;
            object-fit: cover;
        }

        .car-details h4 { font-size: 14px; margin-bottom: 3px; }
        .car-type { font-size: 12px; color: var(--text-gray); }

        .date-range { font-size: 13px; line-height: 1.6; }
        .date-range i { color: var(--gold); margin-right: 5px; }

        .price-tag { font-size: 1.3rem; font-weight: 700; color: var(--gold); }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: rgba(245, 158, 11, 0.2); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-confirmed { background: rgba(16, 185, 129, 0.2); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-cancelled { background: rgba(239, 68, 68, 0.2); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); }

        /* ========== ACTION DROPDOWN ========== */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-btn-main {
            padding: 10px 16px;
            background: var(--sidebar-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
        }

        .action-btn-main:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--sidebar-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            min-width: 180px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .dropdown-menu.show { display: block; animation: fadeIn 0.2s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover { background: rgba(255, 255, 255, 0.05); color: white; }

        .dropdown-item i { width: 18px; text-align: center; }

        .dropdown-item.view { color: var(--info); }
        .dropdown-item.topup { color: var(--gold); }
        .dropdown-item.confirm { color: var(--success); }
        .dropdown-item.cancel { color: var(--danger); }

        .dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 5px 0;
        }

        /* ========== MODALS ========== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-overlay.show { display: flex; animation: fadeIn 0.3s ease; }

        .modal {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: none;
            color: var(--text-gray);
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s;
        }

        .modal-close:hover { background: rgba(239, 68, 68, 0.2); color: var(--danger); }

        .modal-body { padding: 25px; }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        /* View Modal Specific */
        .booking-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-card {
            background: var(--sidebar-bg);
            border-radius: 12px;
            padding: 15px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--text-gray);
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
        }

        .detail-value.gold { color: var(--gold); }

        .car-preview {
            display: flex;
            gap: 15px;
            background: var(--sidebar-bg);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .car-preview img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .charges-list {
            background: var(--sidebar-bg);
            border-radius: 12px;
            padding: 15px;
        }

        .charge-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .charge-item:last-child { border-bottom: none; }

        .charge-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--gold);
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--border);
        }

        /* Form Styles in Modal */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--text-gray);
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--sidebar-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
        }

        .form-group textarea { resize: vertical; min-height: 80px; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            color: #000;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
        }

        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-secondary { background: var(--sidebar-bg); color: var(--text-gray); border: 1px solid var(--border); }

        .btn:hover { transform: translateY(-2px); }

        /* Print Button */
        .btn-print {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Cancel Modal Warning */
        .warning-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }

        .warning-box i { font-size: 24px; color: var(--danger); }

        .refund-preview {
            background: var(--sidebar-bg);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .refund-amount {
            font-size: 2rem;
            font-weight: 800;
            color: var(--success);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-gray);
        }

        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .main-content { margin-left: 70px; padding: 20px; }
            .sidebar-logo h2, .nav-label, .sidebar a span { display: none; }
            .table-container { overflow-x: auto; }
            .booking-detail-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
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
            <a href="dashboard.php"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="manage_cars.php"><i class="fas fa-car"></i><span>Manage Cars</span></a>
            <a href="bookings.php" class="active"><i class="fas fa-calendar-check"></i><span>Bookings</span></a>
            <a href="users.php"><i class="fas fa-users"></i><span>Users</span></a>
             <a href="manage_contacts.php">
                <i class="fas fa-users"></i>
                <span>Contacts</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Settings</div>
            <a href="../index.php"><i class="fas fa-globe"></i><span>View Site</span></a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </nav>
</aside>

<!-- ========== MAIN CONTENT ========== -->
<main class="main-content">

    <!-- Alert Messages -->
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'confirmed'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Booking confirmed successfully!
            </div>
        <?php elseif($_GET['msg'] == 'cancelled'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-ban"></i> Booking has been cancelled.
            </div>
        <?php elseif($_GET['msg'] == 'topup_added'): ?>
            <div class="alert alert-success">
                <i class="fas fa-plus-circle"></i> Extra charges added successfully!
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Booking Management</h1>
        <p class="breadcrumb">Dashboard / Bookings / All</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-label">Pending Approvals</div>
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-label">Confirmed Bookings</div>
            <div class="stat-value"><?php echo $stats['confirmed']; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-label">Cancelled</div>
            <div class="stat-value"><?php echo $stats['cancelled']; ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹<?php echo number_format($stats['revenue'] ?? 0); ?></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search by ID, user or car..." 
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <div class="status-tabs">
            <a href="bookings.php" class="tab-btn <?php echo ($status_filter == '') ? 'active' : ''; ?>">
                All (<?php echo $stats['total']; ?>)
            </a>
            <a href="?status=Pending" class="tab-btn <?php echo ($status_filter == 'Pending') ? 'active' : ''; ?>">
                Pending
            </a>
            <a href="?status=Confirmed" class="tab-btn <?php echo ($status_filter == 'Confirmed') ? 'active' : ''; ?>">
                Confirmed
            </a>
            <a href="?status=Cancelled" class="tab-btn <?php echo ($status_filter == 'Cancelled') ? 'active' : ''; ?>">
                Cancelled
            </a>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Rental Period</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($total_bookings > 0): ?>
                    <?php while($booking = mysqli_fetch_assoc($result)): 
                        $days = (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / 86400;
                        $days = max(1, $days);
                    ?>
                        <tr>
                            <td><span class="booking-id">#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></span></td>

                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?php echo strtoupper(substr($booking['user_name'], 0, 1)); ?></div>
                                    <div class="user-details">
                                        <h4><?php echo htmlspecialchars($booking['user_name']); ?></h4>
                                        <div class="user-contact"><?php echo htmlspecialchars($booking['user_email']); ?></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="car-info">
                                    <img src="../uploads/car_images/<?php echo htmlspecialchars($booking['car_image']); ?>" class="car-thumb" alt="Car">
                                    <div class="car-details">
                                        <h4><?php echo htmlspecialchars($booking['car_name']); ?></h4>
                                        <div class="car-type"><?php echo $booking['car_type']; ?></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="date-range">
                                    <div><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($booking['start_date'])); ?></div>
                                    <div><i class="fas fa-calendar-check"></i> <?php echo date('M d, Y', strtotime($booking['end_date'])); ?></div>
                                    <small style="color:var(--text-gray)">(<?php echo $days; ?> days)</small>
                                </div>
                            </td>

                            <td><div class="price-tag">₹<?php echo number_format($booking['total_price']); ?></div></td>

                            <td>
                                <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>

                            <!-- ACTION DROPDOWN -->
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn-main" onclick="toggleDropdown(this)">
                                        <i class="fas fa-ellipsis-v"></i> Actions <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <!-- VIEW -->
                                        <button class="dropdown-item view" onclick="openViewModal(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>

                                        <!-- PRINT -->
                                        <button class="dropdown-item" onclick="printInvoice(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-print"></i> Print Invoice
                                        </button>

                                        <div class="dropdown-divider"></div>

                                        <?php if($booking['status'] == 'Pending'): ?>
                                            <!-- CONFIRM -->
                                            <a href="?confirm=<?php echo $booking['id']; ?>" class="dropdown-item confirm" 
                                               onclick="return confirm('✅ Confirm this booking?')">
                                                <i class="fas fa-check-circle"></i> Approve Booking
                                            </a>
                                        <?php endif; ?>

                                        <?php if($booking['status'] != 'Cancelled'): ?>
                                            <!-- TOP UP -->
                                            <button class="dropdown-item topup" onclick="openTopUpModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['car_name']); ?>')">
                                                <i class="fas fa-plus-circle"></i> Add Charges
                                            </button>

                                            <div class="dropdown-divider"></div>

                                            <!-- CANCEL -->
                                            <button class="dropdown-item cancel" onclick="openCancelModal(<?php echo $booking['id']; ?>, <?php echo $booking['total_price']; ?>, '<?php echo htmlspecialchars($booking['car_name']); ?>')">
                                                <i class="fas fa-times-circle"></i> Cancel Booking
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h3>No Bookings Found</h3>
                                <p>There are no bookings matching your filters.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<!-- ========== VIEW DETAILS MODAL ========== -->
<div class="modal-overlay" id="viewModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-file-invoice" style="color:var(--info)"></i> Booking Details</h2>
            <button class="modal-close" onclick="closeModal('viewModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div id="viewModalContent">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <button class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
        </div>
    </div>
</div>

<!-- ========== TOP UP MODAL ========== -->
<div class="modal-overlay" id="topupModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle" style="color:var(--gold)"></i> Add Extra Charges</h2>
            <button class="modal-close" onclick="closeModal('topupModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="booking_id" id="topupBookingId">
                
                <p style="color:var(--text-gray); margin-bottom:20px;">
                    Adding charges to booking: <strong id="topupCarName" style="color:white"></strong>
                </p>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Charge Type</label>
                        <select name="charge_type" required>
                            <option value="">Select Type</option>
                            <option value="Fuel">Fuel Charges</option>
                            <option value="Damage">Damage Repair</option>
                            <option value="Late Fee">Late Return Fee</option>
                            <option value="Cleaning">Cleaning Fee</option>
                            <option value="Toll">Toll Charges</option>
                            <option value="Insurance">Extra Insurance</option>
                            <option value="GPS">GPS Rental</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-rupee-sign"></i> Amount (₹)</label>
                        <input type="number" name="charge_amount" min="1" placeholder="500" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Note (Optional)</label>
                    <textarea name="charge_note" placeholder="Add details about this charge..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('topupModal')">Cancel</button>
                <button type="submit" name="add_topup" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Charge
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== CANCEL MODAL ========== -->
<div class="modal-overlay" id="cancelModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Cancel Booking</h2>
            <button class="modal-close" onclick="closeModal('cancelModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="booking_id" id="cancelBookingId">
                
                <div class="warning-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Warning!</strong><br>
                        <span style="color:var(--text-gray)">This action cannot be undone. The customer will be notified.</span>
                    </div>
                </div>

                <p style="color:var(--text-gray); margin-bottom:15px;">
                    Cancelling booking for: <strong id="cancelCarName" style="color:white"></strong>
                </p>

                <div class="refund-preview">
                    <div style="color:var(--text-gray); font-size:14px; margin-bottom:5px;">Refund Amount</div>
                    <div class="refund-amount" id="refundDisplay">₹0</div>
                    <small style="color:var(--text-gray)">Adjust using the slider below</small>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-percentage"></i> Refund Percentage</label>
                    <input type="range" min="0" max="100" value="100" id="refundSlider" 
                           oninput="updateRefund(this.value)" style="width:100%">
                    <div style="display:flex; justify-content:space-between; color:var(--text-gray); font-size:12px; margin-top:5px;">
                        <span>0% (No Refund)</span>
                        <span>100% (Full Refund)</span>
                    </div>
                </div>

                <input type="hidden" name="refund_amount" id="refundAmount">
                <input type="hidden" id="originalAmount">

                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Cancellation Reason</label>
                    <select name="cancel_reason" required>
                        <option value="">Select Reason</option>
                        <option value="Customer Request">Customer Request</option>
                        <option value="Vehicle Unavailable">Vehicle Unavailable</option>
                        <option value="Payment Issue">Payment Issue</option>
                        <option value="Duplicate Booking">Duplicate Booking</option>
                        <option value="Policy Violation">Policy Violation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('cancelModal')">Go Back</button>
                <button type="submit" name="cancel_booking" class="btn btn-danger">
                    <i class="fas fa-times"></i> Cancel Booking
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ========== DROPDOWN TOGGLE ==========
    function toggleDropdown(btn) {
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if(menu !== btn.nextElementSibling) {
                menu.classList.remove('show');
            }
        });
        
        btn.nextElementSibling.classList.toggle('show');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if(!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // ========== MODAL FUNCTIONS ==========
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // VIEW MODAL
    function openViewModal(booking) {
        const modal = document.getElementById('viewModal');
        const content = document.getElementById('viewModalContent');
        
        const days = Math.ceil((new Date(booking.end_date) - new Date(booking.start_date)) / (1000 * 60 * 60 * 24));
        
        content.innerHTML = `
            <div class="car-preview">
                <img src="../uploads/car_images/${booking.car_image}" alt="Car">
                <div>
                    <h3 style="margin-bottom:5px;">${booking.car_name}</h3>
                    <div style="color:var(--text-gray)">${booking.car_type}</div>
                    <div style="color:var(--gold); font-weight:700; margin-top:10px;">₹${parseInt(booking.price_per_day).toLocaleString()}/day</div>
                </div>
            </div>

            <div class="booking-detail-grid">
                <div class="detail-card">
                    <div class="detail-label">Booking ID</div>
                    <div class="detail-value gold">#${String(booking.id).padStart(5, '0')}</div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">${booking.status}</div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Customer Name</div>
                    <div class="detail-value">${booking.user_name}</div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">${booking.user_email}</div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Start Date</div>
                    <div class="detail-value">${new Date(booking.start_date).toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'})}</div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">End Date</div>
                    <div class="detail-value">${new Date(booking.end_date).toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'})}</div>
                </div>
            </div>

            <div class="charges-list">
                <h4 style="margin-bottom:15px;"><i class="fas fa-receipt"></i> Price Breakdown</h4>
                <div class="charge-item">
                    <span>Daily Rate × ${days} days</span>
                    <span>₹${(booking.price_per_day * days).toLocaleString()}</span>
                </div>
                <div class="charge-item">
                    <span>Taxes & Fees</span>
                    <span>Included</span>
                </div>
                <div class="charge-total">
                    Total: ₹${parseInt(booking.total_price).toLocaleString()}
                </div>
            </div>
        `;
        
        modal.classList.add('show');
    }

    // TOP UP MODAL
    function openTopUpModal(bookingId, carName) {
        document.getElementById('topupBookingId').value = bookingId;
        document.getElementById('topupCarName').textContent = carName;
        document.getElementById('topupModal').classList.add('show');
    }

    // CANCEL MODAL
    let originalPrice = 0;
    
    function openCancelModal(bookingId, totalPrice, carName) {
        originalPrice = totalPrice;
        document.getElementById('cancelBookingId').value = bookingId;
        document.getElementById('cancelCarName').textContent = carName;
        document.getElementById('originalAmount').value = totalPrice;
        document.getElementById('refundSlider').value = 100;
        updateRefund(100);
        document.getElementById('cancelModal').classList.add('show');
    }

    function updateRefund(percentage) {
        const refund = Math.round(originalPrice * (percentage / 100));
        document.getElementById('refundDisplay').textContent = '₹' + refund.toLocaleString();
        document.getElementById('refundAmount').value = refund;
    }

    // PRINT INVOICE
    function printInvoice(bookingId) {
        window.open('print_invoice.php?id=' + bookingId, '_blank');
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
</script>

</body>
</html>