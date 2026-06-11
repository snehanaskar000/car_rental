<?php
session_start();

// Redirect if not admin
if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

// DB CONNECTION
$conn = mysqli_connect("localhost", "root", "", "car_rental");

// ========== ADD CAR ==========
if(isset($_POST['add'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price = (int)$_POST['price'];
    $seats = (int)$_POST['seats'];
    $transmission = mysqli_real_escape_string($conn, $_POST['transmission']);
    $fuel = mysqli_real_escape_string($conn, $_POST['fuel']);
    
    // IMAGE UPLOAD
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $image = time() . '_' . $_FILES['image']['name']; // Unique filename
        $tmp = $_FILES['image']['tmp_name'];
        $upload_path = "../uploads/car_images/" . $image;
        
        // Check if uploads folder exists
        if(!file_exists("../uploads/car_images/")){
            mkdir("../uploads/car_images/", 0777, true);
        }
        
        if(move_uploaded_file($tmp, $upload_path)){
            $query = "INSERT INTO cars(name, type, price_per_day, image, seats, transmission, fuel, status) 
                      VALUES('$name', '$type', '$price', '$image', '$seats', '$transmission', '$fuel', 'available')";
            
            if(mysqli_query($conn, $query)){
                $success = "Car added successfully!";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        } else {
            $error = "Failed to upload image!";
        }
    } else {
        $error = "Please select an image!";
    }
}

// ========== DELETE CAR ==========
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    
    // Get image filename to delete
    $img_query = mysqli_query($conn, "SELECT image FROM cars WHERE id='$id'");
    if($img_row = mysqli_fetch_assoc($img_query)){
        $img_path = "../uploads/car_images/" . $img_row['image'];
        if(file_exists($img_path)){
            unlink($img_path); // Delete image file
        }
    }
    
    mysqli_query($conn, "DELETE FROM cars WHERE id='$id'");
    header("Location: manage_cars.php");
    exit;
}

// ========== SEARCH & FILTER ==========
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_type = isset($_GET['filter_type']) ? mysqli_real_escape_string($conn, $_GET['filter_type']) : "";

$query = "SELECT * FROM cars WHERE 1=1";
if($search != "") $query .= " AND name LIKE '%$search%'";
if($filter_type != "") $query .= " AND type='$filter_type'";
$query .= " ORDER BY id DESC";

$result = mysqli_query($conn, $query);
$total_cars = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars | Admin</title>
    
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
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { display: flex; background: var(--dark-bg); color: white; min-height: 100vh; }

        /* ========== SIDEBAR (Same as dashboard) ========== */
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .page-header h1 { font-size: 2rem; font-weight: 700; }
        .car-count {
            color: var(--text-gray);
            font-size: 14px;
            margin-top: 5px;
        }

        /* ========== ALERT MESSAGES ========== */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
        }

        /* ========== ADD CAR FORM ========== */
        .form-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
        }

        .form-card h2 {
            font-size: 1.4rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--text-gray);
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        /* File Upload Styling */
        .file-upload {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .file-upload:hover {
            border-color: var(--gold);
            background: rgba(245, 158, 11, 0.05);
        }

        .file-upload input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon {
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 15px;
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin-top: 15px;
            border-radius: 8px;
            display: none;
        }

        .btn-submit {
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            border: none;
            border-radius: 10px;
            color: #000;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            font-size: 15px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
        }

        /* ========== SEARCH BAR ========== */
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 12px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: white;
        }

        .filter-select {
            padding: 12px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: white;
            min-width: 150px;
        }

        /* ========== CAR TABLE ========== */
        .table-container {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--sidebar-bg);
        }

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
        }

        tbody tr {
            transition: 0.2s;
        }

        tbody tr:hover {
            background: rgba(245, 158, 11, 0.05);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .car-name {
            font-weight: 600;
            color: white;
        }

        .car-specs {
            display: flex;
            gap: 10px;
            font-size: 12px;
            color: var(--text-gray);
            margin-top: 5px;
        }

        .spec-badge {
            background: rgba(255, 255, 255, 0.05);
            padding: 3px 8px;
            border-radius: 4px;
        }

        .price-tag {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--gold);
        }

        .btn-delete {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.25);
            border-color: var(--danger);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .main-content { margin-left: 70px; padding: 20px; }
            .sidebar-logo h2, .nav-label, .sidebar a span { display: none; }
            .table-container { overflow-x: auto; }
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
            <a href="dashboard.php">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_cars.php" class="active">
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
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- ========== MAIN CONTENT ========== -->
<main class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-car"></i> Manage Cars</h1>
            <p class="car-count">Total vehicles: <?php echo $total_cars; ?></p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Add Car Form -->
    <div class="form-card">
        <h2><i class="fas fa-plus-circle"></i> Add New Car</h2>
        
        <form method="POST" enctype="multipart/form-data" id="carForm">
            <div class="form-grid">
                
                <div class="form-group">
                    <label><i class="fas fa-car"></i> Car Name</label>
                    <input type="text" name="name" placeholder="e.g. BMW X5" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Vehicle Type</label>
                    <select name="type" required>
                        <option value="">Select Type</option>
                        <option value="SUV">SUV</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="Luxury">Luxury</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-rupee-sign"></i> Price per Day</label>
                    <input type="number" name="price" placeholder="2500" min="500" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-users"></i> Seating Capacity</label>
                    <select name="seats" required>
                        <option value="2">2 Seats</option>
                        <option value="4">4 Seats</option>
                        <option value="5" selected>5 Seats</option>
                        <option value="7">7 Seats</option>
                        <option value="8">8 Seats</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-cog"></i> Transmission</label>
                    <select name="transmission" required>
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-gas-pump"></i> Fuel Type</label>
                    <select name="fuel" required>
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>

            </div>

            <!-- File Upload -->
            <div class="form-group">
                <label><i class="fas fa-image"></i> Car Image</label>
                <div class="file-upload">
                    <input type="file" name="image" accept="image/*" required onchange="previewImage(event)">
                    <div class="file-upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <p>Click or drag image here</p>
                    <img id="imagePreview" class="image-preview">
                </div>
            </div>

            <button type="submit" name="add" class="btn-submit">
                <i class="fas fa-plus"></i> Add Car to Fleet
            </button>
        </form>
    </div>

    <!-- Search & Filter -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" class="search-input" 
               placeholder="🔍 Search by car name..." 
               value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="filter_type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="SUV" <?php if($filter_type=="SUV") echo "selected"; ?>>SUV</option>
            <option value="Sedan" <?php if($filter_type=="Sedan") echo "selected"; ?>>Sedan</option>
            <option value="Hatchback" <?php if($filter_type=="Hatchback") echo "selected"; ?>>Hatchback</option>
            <option value="Luxury" <?php if($filter_type=="Luxury") echo "selected"; ?>>Luxury</option>
        </select>
        
        <button type="submit" class="btn-submit">Search</button>
        <a href="manage_cars.php" style="padding:12px 20px; background:var(--card-bg); border:1px solid var(--border); border-radius:10px; color:var(--text-gray); text-decoration:none;">Reset</a>
    </form>

    <!-- Cars Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Car Details</th>
                    <th>Specifications</th>
                    <th>Price/Day</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($total_cars > 0): ?>
                    <?php while($car = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $car['id']; ?></td>
                            <td>
                                <img src="../uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" 
                                     class="car-image" alt="Car">
                            </td>
                            <td>
                                <div class="car-name"><?php echo htmlspecialchars($car['name']); ?></div>
                                <div style="color:var(--text-gray); font-size:13px; margin-top:3px;">
                                    <?php echo $car['type']; ?>
                                </div>
                            </td>
                            <td>
                                <div class="car-specs">
                                    <span class="spec-badge">
                                        <i class="fas fa-users"></i> <?php echo $car['seats'] ?? '5'; ?> Seats
                                    </span>
                                    <span class="spec-badge">
                                        <i class="fas fa-cog"></i> <?php echo $car['transmission'] ?? 'Auto'; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="price-tag">₹<?php echo number_format($car['price_per_day']); ?></div>
                            </td>
                            <td>

    <!-- EDIT BUTTON -->
    <a href="edit_car.php?id=<?php echo $car['id']; ?>" 
       style="padding:8px 16px; background:rgba(59,130,246,0.15); border:1px solid rgba(59,130,246,0.3); color:#3b82f6; text-decoration:none; border-radius:6px; font-size:13px; font-weight:600; margin-right:8px; display:inline-block;">
        <i class="fas fa-edit"></i> Edit
    </a>

    <!-- DELETE BUTTON -->
    <a href="?delete=<?php echo $car['id']; ?>" 
       class="btn-delete" 
       onclick="return confirm('⚠️ Delete <?php echo htmlspecialchars($car['name']); ?>?')">
        <i class="fas fa-trash"></i> Delete
    </a>

</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:var(--text-gray);">
                            <i class="fas fa-inbox" style="font-size:48px; margin-bottom:15px; display:block;"></i>
                            No cars found. Add your first vehicle!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
    // Image Preview Function
    function previewImage(event) {
        const preview = document.getElementById('imagePreview');
        const file = event.target.files[0];
        
        if(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }
</script>

</body>
</html>