<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "car_rental");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

// ========== MARK AS READ ==========
if(isset($_GET['read'])){
    $id = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE contacts SET status='read' WHERE id=$id");
    header("Location: manage_contacts.php?msg=marked_read");
    exit;
}

// ========== MARK AS UNREAD ==========
if(isset($_GET['unread'])){
    $id = (int)$_GET['unread'];
    mysqli_query($conn, "UPDATE contacts SET status='unread' WHERE id=$id");
    header("Location: manage_contacts.php?msg=marked_unread");
    exit;
}

// ========== DELETE CONTACT ==========
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM contacts WHERE id=$id");
    header("Location: manage_contacts.php?msg=deleted");
    exit;
}

// ========== FILTER ==========
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// ========== FETCH CONTACTS ==========
$query = "SELECT * FROM contacts WHERE 1=1";

if($status_filter != ""){
    $query .= " AND status='$status_filter'";
}

if($search != ""){
    $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%')";
}

$query .= " ORDER BY id DESC";

$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);

// ========== STATISTICS ==========
$total_contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contacts"))['count'];
$unread_contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contacts WHERE status='unread'"))['count'];
$read_contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contacts WHERE status='read'"))['count'];
$today_contacts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contacts WHERE DATE(created_at) = CURDATE()"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        
        :root {
            --gold: #F59E0B;
            --dark: #020617;
            --card: #1e293b;
            --sidebar: #0f172a;
            --gray: #94A3B8;
            --border: rgba(255,255,255,0.1);
            --success: #10b981;
            --danger: #ef4444;
            --info: #3b82f6;
            --warning: #f59e0b;
        }

        body { display: flex; background: var(--dark); color: #fff; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            border-right: 1px solid var(--border);
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

        .sidebar-logo i { font-size: 28px; color: var(--gold); }
        .sidebar-logo h2 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 0; }
        .sidebar-logo span { color: var(--gold); }

        .nav-section { margin-bottom: 30px; }
        .nav-label {
            font-size: 11px;
            color: var(--gray);
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
            background: rgba(245,158,11,0.1);
            color: var(--gold);
        }

        .sidebar a i { width: 20px; text-align: center; }

        .logout-btn {
            margin-top: 30px;
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2) !important;
        }

        /* Main */
        .main { margin-left: 260px; flex: 1; padding: 40px; }

        /* Alert */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success { background: rgba(16,185,129,0.15); color: var(--success); }
        .alert-info { background: rgba(59,130,246,0.15); color: var(--info); }
        .alert-danger { background: rgba(239,68,68,0.15); color: var(--danger); }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title { font-size: 1.6rem; }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            transition: 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(245,158,11,0.1);
            border-color: var(--gold);
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card:nth-child(1) .stat-icon { background: rgba(59,130,246,0.15); color: var(--info); }
        .stat-card:nth-child(2) .stat-icon { background: rgba(239,68,68,0.15); color: var(--danger); }
        .stat-card:nth-child(3) .stat-icon { background: rgba(16,185,129,0.15); color: var(--success); }
        .stat-card:nth-child(4) .stat-icon { background: rgba(245,158,11,0.15); color: var(--gold); }

        .stat-info h3 { font-size: 1.6rem; margin-bottom: 3px; }
        .stat-info p { color: var(--gray); font-size: 13px; }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--gold);
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            background: var(--card);
            padding: 5px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .filter-tab {
            padding: 10px 18px;
            background: transparent;
            border: none;
            color: var(--gray);
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }

        .filter-tab:hover, .filter-tab.active {
            background: rgba(245,158,11,0.15);
            color: var(--gold);
        }

        .btn-search {
            padding: 12px 20px;
            background: var(--gold);
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(245,158,11,0.4);
        }

        /* Messages Grid */
        .messages-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Message Card */
        .message-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            transition: 0.3s;
        }

        .message-card:hover {
            border-color: rgba(245,158,11,0.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .message-card.unread {
            border-left: 4px solid var(--gold);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: var(--sidebar);
            border-bottom: 1px solid var(--border);
        }

        .sender-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sender-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #D97706);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
            font-size: 20px;
        }

        .sender-details h4 { 
            font-size: 16px; 
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .unread-badge {
            padding: 3px 10px;
            background: var(--gold);
            color: #000;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .sender-details p {
            color: var(--gray);
            font-size: 13px;
        }

        .message-meta {
            text-align: right;
        }

        .message-id {
            color: var(--gold);
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .message-date {
            color: var(--gray);
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            justify-content: flex-end;
        }

        .message-body {
            padding: 25px;
        }

        .subject-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .subject-icon {
            width: 40px;
            height: 40px;
            background: rgba(245,158,11,0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
        }

        .subject-text h5 {
            font-size: 15px;
            color: #fff;
        }

        .subject-text span {
            font-size: 12px;
            color: var(--gray);
        }

        .message-content {
            background: var(--sidebar);
            border-radius: 12px;
            padding: 20px;
            color: #E2E8F0;
            font-size: 14px;
            line-height: 1.8;
            max-height: 100px;
            overflow: hidden;
            position: relative;
        }

        .message-content.expanded {
            max-height: none;
        }

        .message-content::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(transparent, var(--sidebar));
        }

        .message-content.expanded::after {
            display: none;
        }

        .contact-details {
            display: flex;
            gap: 25px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
            font-size: 13px;
        }

        .contact-item i { color: var(--gold); }

        .contact-item a {
            color: var(--gray);
            text-decoration: none;
            transition: 0.3s;
        }

        .contact-item a:hover { color: var(--gold); }

        /* Message Actions */
        .message-actions {
            display: flex;
            gap: 10px;
            padding: 15px 25px;
            background: var(--sidebar);
            border-top: 1px solid var(--border);
        }

        .btn-action {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-action:hover { transform: translateY(-2px); }

        .btn-view {
            background: rgba(59,130,246,0.15);
            color: var(--info);
            border: 1px solid rgba(59,130,246,0.3);
        }

        .btn-view:hover {
            background: rgba(59,130,246,0.25);
            box-shadow: 0 5px 15px rgba(59,130,246,0.3);
        }

        .btn-reply {
            background: rgba(16,185,129,0.15);
            color: var(--success);
            border: 1px solid rgba(16,185,129,0.3);
        }

        .btn-reply:hover {
            background: rgba(16,185,129,0.25);
            box-shadow: 0 5px 15px rgba(16,185,129,0.3);
        }

        .btn-read {
            background: rgba(245,158,11,0.15);
            color: var(--gold);
            border: 1px solid rgba(245,158,11,0.3);
        }

        .btn-read:hover {
            background: rgba(245,158,11,0.25);
            box-shadow: 0 5px 15px rgba(245,158,11,0.3);
        }

        .btn-delete {
            background: rgba(239,68,68,0.15);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,0.3);
            margin-left: auto;
        }

        .btn-delete:hover {
            background: rgba(239,68,68,0.25);
            box-shadow: 0 5px 15px rgba(239,68,68,0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--card);
            border-radius: 16px;
            color: var(--gray);
        }

        .empty-state i { 
            font-size: 60px; 
            margin-bottom: 20px; 
            opacity: 0.3;
            display: block; 
        }

        .empty-state h3 { 
            color: #fff;
            margin-bottom: 10px;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal.show { display: flex; }

        .modal-box {
            background: var(--card);
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-head {
            padding: 22px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--sidebar);
        }

        .modal-head h3 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .close-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        .close-btn:hover { 
            background: rgba(239,68,68,0.2); 
            color: var(--danger); 
        }

        .modal-body { padding: 28px; }

        .modal-footer {
            padding: 20px 28px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            background: var(--sidebar);
        }

        /* View Modal Content */
        .view-sender {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 20px;
            background: var(--sidebar);
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .view-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #D97706);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
            font-size: 26px;
        }

        .view-sender-info h4 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .view-sender-info p {
            color: var(--gray);
            font-size: 14px;
        }

        .view-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .view-item {
            background: var(--sidebar);
            padding: 15px;
            border-radius: 10px;
        }

        .view-item .label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .view-item .value {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .view-item .value.gold { color: var(--gold); }

        .view-message {
            background: var(--sidebar);
            border-radius: 14px;
            padding: 22px;
        }

        .view-message h5 {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-message h5 i { color: var(--gold); }

        .view-message p {
            color: #E2E8F0;
            font-size: 15px;
            line-height: 1.9;
        }

        .btn-modal {
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-modal:hover { transform: translateY(-2px); }

        .btn-primary { background: var(--gold); color: #000; }
        .btn-secondary { background: var(--sidebar); color: var(--gray); border: 1px solid var(--border); }
        .btn-danger-solid { background: var(--danger); color: #fff; }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-logo h2, .nav-label, .sidebar a span { display: none; }
            .sidebar-logo { justify-content: center; padding-bottom: 15px; border-bottom: none; }
            .main { margin-left: 70px; padding: 20px; }
            .stats-row { grid-template-columns: 1fr; }
            .filter-bar { flex-direction: column; }
            .search-box { min-width: 100%; }
            .message-header { flex-direction: column; gap: 15px; text-align: center; }
            .message-meta { text-align: center; }
            .contact-details { flex-direction: column; gap: 12px; }
            .message-actions { flex-wrap: wrap; }
            .btn-delete { margin-left: 0; width: 100%; justify-content: center; }
            .view-details { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<!-- ========== SIDEBAR (SAME AS DASHBOARD) ========== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-car-side"></i>
        <h2>CAR<span>RENTAL</span></h2>
    </div>

    <nav>
        <div class="nav-section">
            <div class="nav-label">Main Menu</div>
            
            <a href="dashboard.php">
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

            <a href="manage_contacts.php" class="active">
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

<!-- Main Content -->
<main class="main">

    <!-- Alert Messages -->
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'marked_read'): ?>
            <div class="alert alert-success"><i class="fas fa-check"></i> Message marked as read!</div>
        <?php elseif($_GET['msg'] == 'marked_unread'): ?>
            <div class="alert alert-info"><i class="fas fa-envelope"></i> Message marked as unread!</div>
        <?php elseif($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-danger"><i class="fas fa-trash"></i> Message deleted!</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-envelope"></i> Contact Messages</h1>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <a href="manage_contacts.php" class="stat-card" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_contacts; ?></h3>
                <p>Total Messages</p>
            </div>
        </a>
        <a href="?status=unread" class="stat-card" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-envelope-open"></i></div>
            <div class="stat-info">
                <h3><?php echo $unread_contacts; ?></h3>
                <p>Unread Messages</p>
            </div>
        </a>
        <a href="?status=read" class="stat-card" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <h3><?php echo $read_contacts; ?></h3>
                <p>Read Messages</p>
            </div>
        </a>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?php echo $today_contacts; ?></h3>
                <p>Today's Messages</p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search by name, email or subject..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filter-tabs">
            <a href="manage_contacts.php" class="filter-tab <?php echo ($status_filter == '') ? 'active' : ''; ?>">All</a>
            <a href="?status=unread" class="filter-tab <?php echo ($status_filter == 'unread') ? 'active' : ''; ?>">Unread</a>
            <a href="?status=read" class="filter-tab <?php echo ($status_filter == 'read') ? 'active' : ''; ?>">Read</a>
        </div>

        <button type="submit" class="btn-search">
            <i class="fas fa-search"></i> Search
        </button>
    </form>

    <!-- Messages -->
    <div class="messages-grid">
        <?php if($total > 0): ?>
            <?php while($contact = mysqli_fetch_assoc($result)): ?>
                <div class="message-card <?php echo ($contact['status'] == 'unread') ? 'unread' : ''; ?>">
                    
                    <!-- Header -->
                    <div class="message-header">
                        <div class="sender-info">
                            <div class="sender-avatar">
                                <?php echo strtoupper(substr($contact['name'], 0, 1)); ?>
                            </div>
                            <div class="sender-details">
                                <h4>
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                    <?php if($contact['status'] == 'unread'): ?>
                                        <span class="unread-badge">NEW</span>
                                    <?php endif; ?>
                                </h4>
                                <p><?php echo htmlspecialchars($contact['email']); ?></p>
                            </div>
                        </div>
                        <div class="message-meta">
                            <div class="message-id">#<?php echo str_pad($contact['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            <div class="message-date">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y - h:i A', strtotime($contact['created_at'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="message-body">
                        <div class="subject-row">
                            <div class="subject-icon"><i class="fas fa-tag"></i></div>
                            <div class="subject-text">
                                <h5><?php echo htmlspecialchars($contact['subject']); ?></h5>
                                <span>Subject</span>
                            </div>
                        </div>

                        <div class="message-content" id="content-<?php echo $contact['id']; ?>">
                            <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                        </div>

                        <div class="contact-details">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo $contact['email']; ?>"><?php echo htmlspecialchars($contact['email']); ?></a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo $contact['phone']; ?>"><?php echo htmlspecialchars($contact['phone']); ?></a>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="message-actions">
                        <button class="btn-action btn-view" onclick='viewMessage(<?php echo json_encode($contact); ?>)'>
                            <i class="fas fa-eye"></i> View
                        </button>
                        
                        <a href="mailto:<?php echo $contact['email']; ?>?subject=Re: <?php echo urlencode($contact['subject']); ?>" class="btn-action btn-reply">
                            <i class="fas fa-reply"></i> Reply
                        </a>

                        <?php if($contact['status'] == 'unread'): ?>
                            <a href="?read=<?php echo $contact['id']; ?>" class="btn-action btn-read">
                                <i class="fas fa-check"></i> Mark Read
                            </a>
                        <?php else: ?>
                            <a href="?unread=<?php echo $contact['id']; ?>" class="btn-action btn-read">
                                <i class="fas fa-envelope"></i> Mark Unread
                            </a>
                        <?php endif; ?>

                        <button class="btn-action btn-delete" onclick="deleteMessage(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars(addslashes($contact['name'])); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Messages Found</h3>
                <p><?php echo $search != "" || $status_filter != "" ? "No messages match your filters." : "You haven't received any contact messages yet."; ?></p>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- View Message Modal -->
<div class="modal" id="viewModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-envelope-open" style="color:var(--info)"></i> Message Details</h3>
            <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            
            <!-- Sender Info -->
            <div class="view-sender">
                <div class="view-avatar" id="modalAvatar">J</div>
                <div class="view-sender-info">
                    <h4 id="modalName">John Doe</h4>
                    <p id="modalEmail">john@example.com</p>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="view-details">
                <div class="view-item">
                    <div class="label">Message ID</div>
                    <div class="value gold" id="modalId">#0001</div>
                </div>
                <div class="view-item">
                    <div class="label">Phone</div>
                    <div class="value" id="modalPhone">+91 98765 43210</div>
                </div>
                <div class="view-item">
                    <div class="label">Subject</div>
                    <div class="value" id="modalSubject">General Inquiry</div>
                </div>
                <div class="view-item">
                    <div class="label">Received On</div>
                    <div class="value" id="modalDate">Jan 01, 2024</div>
                </div>
            </div>

            <!-- Message -->
            <div class="view-message">
                <h5><i class="fas fa-comment-alt"></i> Message</h5>
                <p id="modalMessage">Message content goes here...</p>
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn-modal btn-secondary" onclick="closeModal()">Close</button>
            <a href="#" id="modalReplyBtn" class="btn-modal btn-primary">
                <i class="fas fa-reply"></i> Reply
            </a>
        </div>
    </div>
</div>

<script>
    // View Message Modal
    function viewMessage(contact) {
        document.getElementById('modalAvatar').textContent = contact.name.charAt(0).toUpperCase();
        document.getElementById('modalName').textContent = contact.name;
        document.getElementById('modalEmail').textContent = contact.email;
        document.getElementById('modalId').textContent = '#' + String(contact.id).padStart(4, '0');
        document.getElementById('modalPhone').textContent = contact.phone;
        document.getElementById('modalSubject').textContent = contact.subject;
        document.getElementById('modalMessage').innerHTML = contact.message.replace(/\n/g, '<br>');
        
        // Format date
        const date = new Date(contact.created_at);
        document.getElementById('modalDate').textContent = date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Set reply button
        document.getElementById('modalReplyBtn').href = 'mailto:' + contact.email + '?subject=Re: ' + encodeURIComponent(contact.subject);

        // Mark as read automatically
        if(contact.status == 'unread') {
            window.location.href = '?read=' + contact.id;
            return;
        }

        document.getElementById('viewModal').classList.add('show');
    }

    // Close Modal
    function closeModal() {
        document.getElementById('viewModal').classList.remove('show');
    }

    // Delete Message
    function deleteMessage(id, name) {
        if(confirm('🗑️ Delete message from "' + name + '"?\n\nThis action cannot be undone!')) {
            window.location.href = '?delete=' + id;
        }
    }

    // Close modal on outside click
    document.getElementById('viewModal').onclick = function(e) {
        if(e.target === this) closeModal();
    }

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        });
    }, 3000);

    // Expand message on click
    document.querySelectorAll('.message-content').forEach(content => {
        content.addEventListener('click', function() {
            this.classList.toggle('expanded');
        });
    });
</script>

</body>
</html>