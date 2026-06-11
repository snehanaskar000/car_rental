<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "car_rental");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

// ========== DELETE USER ==========
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    
    // First delete user's bookings
    mysqli_query($conn, "DELETE FROM bookings WHERE user_id=$id");
    
    // Then delete user
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    
    header("Location: users.php?msg=deleted");
    exit;
}

// ========== SEARCH ==========
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// ========== FETCH USERS ==========
$query = "SELECT * FROM users WHERE 1=1";
if($search != ""){
    $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
$query .= " ORDER BY id DESC";

$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);

// ========== STATISTICS ==========
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
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
        }

        .alert-success { background: rgba(16,185,129,0.15); color: var(--success); }
        .alert-danger { background: rgba(239,68,68,0.15); color: var(--danger); }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title { font-size: 1.6rem; }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245,158,11,0.1);
            border-color: var(--gold);
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

        .stat-card:nth-child(1) .stat-icon { background: rgba(59,130,246,0.2); color: var(--info); }
        .stat-card:nth-child(2) .stat-icon { background: rgba(16,185,129,0.2); color: var(--success); }
        .stat-card:nth-child(3) .stat-icon { background: rgba(245,158,11,0.2); color: var(--gold); }

        .stat-info h3 { font-size: 1.5rem; margin-bottom: 2px; }
        .stat-info p { color: var(--gray); font-size: 13px; }

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-input input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
        }

        .search-input input:focus {
            outline: none;
            border-color: var(--gold);
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

        .btn-reset {
            padding: 12px 18px;
            background: var(--card);
            color: var(--gray);
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-reset:hover { color: #fff; }

        /* Users Grid */
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        /* User Card */
        .user-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            transition: 0.3s;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(245,158,11,0.15);
            border-color: var(--gold);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 18px;
            background: var(--sidebar);
            border-bottom: 1px solid var(--border);
        }

        .user-id {
            color: var(--gold);
            font-weight: 700;
            font-size: 14px;
        }

        .booking-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: rgba(59,130,246,0.15);
            color: var(--info);
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .card-body { padding: 18px; }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .user-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #D97706);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
            font-size: 22px;
        }

        .user-info h3 { font-size: 17px; margin-bottom: 3px; }
        .user-info p { color: var(--gray); font-size: 13px; }

        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .detail-item {
            background: var(--sidebar);
            padding: 10px 12px;
            border-radius: 8px;
        }

        .detail-item .label {
            font-size: 10px;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .detail-item .value {
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .detail-item .value.gold { color: var(--gold); }

        /* Card Actions */
        .card-actions {
            display: flex;
            gap: 10px;
            padding: 12px 18px;
            background: var(--sidebar);
            border-top: 1px solid var(--border);
        }

        .btn-action {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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
            box-shadow: 0 4px 15px rgba(59,130,246,0.3);
        }

        .btn-delete {
            background: rgba(239,68,68,0.15);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,0.3);
        }

        .btn-delete:hover {
            background: rgba(239,68,68,0.25);
            box-shadow: 0 4px 15px rgba(239,68,68,0.3);
        }

        /* Empty State */
        .empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: var(--card);
            border-radius: 14px;
            color: var(--gray);
        }

        .empty i { font-size: 50px; margin-bottom: 15px; opacity: 0.3; display: block; }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal.show { display: flex; }

        .modal-box {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-head {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-head h3 {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 16px;
        }

        .close-btn:hover { background: rgba(239,68,68,0.2); color: var(--danger); }

        .modal-body { padding: 22px; }
        
        .modal-foot {
            padding: 16px 22px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* User Profile in Modal */
        .modal-profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .modal-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #D97706);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
            font-size: 32px;
            margin: 0 auto 12px;
        }

        .modal-name { font-size: 1.3rem; margin-bottom: 5px; }
        .modal-email { color: var(--gray); font-size: 14px; }

        /* Detail Grid in Modal */
        .modal-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .modal-detail {
            background: var(--sidebar);
            padding: 14px;
            border-radius: 10px;
        }

        .modal-detail .label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .modal-detail .value {
            font-size: 15px;
            font-weight: 600;
        }

        .modal-detail .value.gold { color: var(--gold); }
        .modal-detail .value.green { color: var(--success); }

        .btn-modal {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.3s;
        }

        .btn-modal:hover { transform: translateY(-2px); }

        .btn-secondary { background: var(--sidebar); color: var(--gray); }
        .btn-danger-solid { background: var(--danger); color: #fff; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-logo h2, .nav-label, .sidebar a span { display: none; }
            .sidebar-logo { justify-content: center; padding-bottom: 15px; border-bottom: none; }
            .main { margin-left: 70px; padding: 20px; }
            .users-grid { grid-template-columns: 1fr; }
            .search-bar { flex-direction: column; }
            .search-input { min-width: 100%; }
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

            <a href="users.php" class="active">
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
<!-- Main Content -->
<main class="main">

    <!-- Alert -->
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-danger"><i class="fas fa-trash"></i> User deleted successfully!</div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-users"></i> Manage Users</h1>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_bookings; ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-search"></i></div>
            <div class="stat-info">
                <h3><?php echo $total; ?></h3>
                <p>Search Results</p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <form method="GET" class="search-bar">
        <div class="search-input">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search by name, email or phone..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn-search">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if($search != ""): ?>
            <a href="users.php" class="btn-reset">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>

    <!-- Users Grid -->
    <div class="users-grid">
        <?php if($total > 0): ?>
            <?php while($user = mysqli_fetch_assoc($result)): 
                // Get booking count for this user
                $booking_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id=" . $user['id']);
                $booking_count = mysqli_fetch_assoc($booking_res)['count'];
            ?>
                <div class="user-card">
                    
                    <!-- Card Header -->
                    <div class="card-header">
                        <span class="user-id">#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        <span class="booking-badge">
                            <i class="fas fa-car"></i> <?php echo $booking_count; ?> Bookings
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        
                        <!-- User Profile -->
                        <div class="user-profile">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>

                        <!-- User Details -->
                        <div class="user-details">
                            <div class="detail-item">
                                <div class="label">Phone</div>
                                <div class="value"><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : 'N/A'; ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="label">Bookings</div>
                                <div class="value gold"><?php echo $booking_count; ?></div>
                            </div>
                        </div>

                    </div>

                    <!-- Card Actions -->
                    <div class="card-actions">
                        <button class="btn-action btn-view" onclick='viewUser(<?php echo json_encode($user); ?>, <?php echo $booking_count; ?>)'>
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['name'])); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
                <p><?php echo $search != "" ? "No users match your search." : "No registered users yet."; ?></p>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- View User Modal -->
<div class="modal" id="viewModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-user" style="color:var(--info)"></i> User Details</h3>
            <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            
            <!-- User Profile -->
            <div class="modal-profile">
                <div class="modal-avatar" id="modalAvatar">J</div>
                <h3 class="modal-name" id="modalName">John Doe</h3>
                <p class="modal-email" id="modalEmail">john@example.com</p>
            </div>

            <!-- User Details -->
            <div class="modal-details">
                <div class="modal-detail">
                    <div class="label">User ID</div>
                    <div class="value gold" id="modalId">#0001</div>
                </div>
                <div class="modal-detail">
                    <div class="label">Phone</div>
                    <div class="value" id="modalPhone">N/A</div>
                </div>
                <div class="modal-detail">
                    <div class="label">Total Bookings</div>
                    <div class="value" id="modalBookings">0</div>
                </div>
                <div class="modal-detail">
                    <div class="label">Status</div>
                    <div class="value green"><i class="fas fa-check-circle"></i> Active</div>
                </div>
            </div>

        </div>
        <div class="modal-foot">
            <button class="btn-modal btn-secondary" onclick="closeModal()">Close</button>
            <button class="btn-modal btn-danger-solid" id="modalDeleteBtn">
                <i class="fas fa-trash"></i> Delete User
            </button>
        </div>
    </div>
</div>

<script>
    // Current user ID for delete action
    let currentUserId = null;
    let currentUserName = null;

    // View User Modal
    function viewUser(user, bookings) {
        currentUserId = user.id;
        currentUserName = user.name;
        
        document.getElementById('modalAvatar').textContent = user.name.charAt(0).toUpperCase();
        document.getElementById('modalName').textContent = user.name;
        document.getElementById('modalEmail').textContent = user.email;
        document.getElementById('modalId').textContent = '#' + String(user.id).padStart(4, '0');
        document.getElementById('modalPhone').textContent = user.phone || 'N/A';
        document.getElementById('modalBookings').textContent = bookings;
        
        document.getElementById('viewModal').classList.add('show');
    }

    // Close Modal
    function closeModal() {
        document.getElementById('viewModal').classList.remove('show');
    }

    // Delete User from Card
    function deleteUser(id, name) {
        if(confirm('⚠️ Delete user "' + name + '"?\n\nThis will also delete all their bookings!')) {
            window.location.href = '?delete=' + id;
        }
    }

    // Delete User from Modal
    document.getElementById('modalDeleteBtn').onclick = function() {
        if(currentUserId && confirm('⚠️ Delete user "' + currentUserName + '"?\n\nThis will also delete all their bookings!')) {
            window.location.href = '?delete=' + currentUserId;
        }
    }

    // Close modal on outside click
    document.getElementById('viewModal').onclick = function(e) {
        if(e.target === this) closeModal();
    }

    // Auto hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            a.style.opacity = '0';
            a.style.transition = 'opacity 0.3s';
            setTimeout(() => a.remove(), 300);
        });
    }, 3000);
</script>

</body>
</html>