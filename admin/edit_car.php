<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit;
}

// Check if ID exists
if(!isset($_GET['id'])){
    header("Location: manage_cars.php");
    exit;
}

$id = (int)$_GET['id'];

// FETCH CAR DATA
$res = mysqli_query($conn, "SELECT * FROM cars WHERE id=$id");
$car = mysqli_fetch_assoc($res);

// If car not found
if(!$car){
    header("Location: manage_cars.php");
    exit;
}

// UPDATE CAR
if(isset($_POST['update'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price = (float)$_POST['price'];

    // Check if new image uploaded
    if($_FILES['image']['name'] != ""){
        $image = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/car_images/" . $image);
        
        mysqli_query($conn, "UPDATE cars SET 
            name='$name',
            type='$type',
            price_per_day=$price,
            image='$image'
            WHERE id=$id
        ");
    } else {
        mysqli_query($conn, "UPDATE cars SET 
            name='$name',
            type='$type',
            price_per_day=$price
            WHERE id=$id
        ");
    }

    header("Location: manage_cars.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car | Admin</title>
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
        }

        body { 
            display: flex; 
            background: var(--dark); 
            color: #fff; 
            min-height: 100vh; 
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: var(--sidebar);
            padding: 20px;
            position: fixed;
            height: 100vh;
            border-right: 1px solid var(--border);
        }

        .sidebar h2 { 
            color: var(--gold); 
            margin-bottom: 30px; 
            font-size: 20px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(245,158,11,0.1);
            color: var(--gold);
        }

        /* Main */
        .main { 
            margin-left: 220px; 
            flex: 1; 
            padding: 30px;
            display: flex;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            max-width: 550px;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-link:hover { color: var(--gold); }

        /* Page Title */
        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .page-header h1 { font-size: 1.6rem; }

        .car-id {
            background: rgba(245,158,11,0.15);
            color: var(--gold);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Form Card */
        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px;
            background: var(--sidebar);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header i { color: var(--gold); font-size: 18px; }
        .card-header h3 { font-size: 1rem; }

        .card-body { padding: 22px; }

        /* Current Car Preview */
        .current-car {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: var(--sidebar);
            border-radius: 12px;
            margin-bottom: 22px;
            border: 1px solid var(--border);
        }

        .current-car img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .current-info h4 { font-size: 16px; margin-bottom: 4px; }

        .current-info .type {
            display: inline-block;
            padding: 2px 8px;
            background: rgba(245,158,11,0.15);
            color: var(--gold);
            border-radius: 10px;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .current-info .price {
            font-size: 20px;
            font-weight: 700;
            color: var(--gold);
        }

        .current-info .price span {
            font-size: 13px;
            color: var(--gray);
            font-weight: 400;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            color: var(--gray);
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group label i {
            margin-right: 6px;
            color: var(--gold);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px 15px;
            background: var(--sidebar);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* File Upload */
        .file-upload {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            position: relative;
        }

        .file-upload:hover {
            border-color: var(--gold);
            background: rgba(245,158,11,0.05);
        }

        .file-upload input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload i { font-size: 30px; color: var(--gold); margin-bottom: 10px; }
        .file-upload p { color: var(--gray); font-size: 14px; margin-bottom: 5px; }
        .file-upload small { color: #64748b; font-size: 12px; }

        .preview-img {
            max-width: 100%;
            max-height: 120px;
            border-radius: 8px;
            margin-top: 12px;
            display: none;
        }

        /* Card Footer */
        .card-footer {
            padding: 18px 22px;
            background: var(--sidebar);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn {
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

        .btn:hover { transform: translateY(-2px); }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #D97706);
            color: #000;
        }

        .btn-primary:hover { box-shadow: 0 6px 20px rgba(245,158,11,0.4); }

        .btn-secondary {
            background: var(--card);
            color: var(--gray);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover { color: #fff; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 60px; }
            .sidebar h2 span, .sidebar a span { display: none; }
            .main { margin-left: 60px; padding: 20px; }
            .form-row { grid-template-columns: 1fr; }
            .current-car { flex-direction: column; }
            .current-car img { width: 100%; height: 140px; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <h2><i class="fas fa-car"></i> <span>Admin</span></h2>
    <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
    <a href="manage_cars.php" class="active"><i class="fas fa-car"></i> <span>Cars</span></a>
    <a href="bookings.php"><i class="fas fa-calendar"></i> <span>Bookings</span></a>
    <a href="users.php"><i class="fas fa-users"></i> <span>Users</span></a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
</aside>

<!-- Main Content -->
<main class="main">
    <div class="form-container">

        <!-- Back Link -->
        <a href="manage_cars.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Cars
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-edit" style="color:var(--gold)"></i> Edit Car</h1>
            <span class="car-id">#<?php echo str_pad($car['id'], 4, '0', STR_PAD_LEFT); ?></span>
        </div>

        <!-- Form Card -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-card">
                
                <!-- Card Header -->
                <div class="card-header">
                    <i class="fas fa-car-side"></i>
                    <h3>Car Details</h3>
                </div>

                <!-- Card Body -->
                <div class="card-body">

                    <!-- Current Car Preview -->
                    <div class="current-car">
                        <img src="../uploads/car_images/<?php echo $car['image']; ?>" alt="<?php echo $car['name']; ?>">
                        <div class="current-info">
                            <h4><?php echo $car['name']; ?></h4>
                            <span class="type"><?php echo $car['type']; ?></span>
                            <div class="price">
                                ₹<?php echo number_format($car['price_per_day']); ?> <span>/day</span>
                            </div>
                        </div>
                    </div>

                    <!-- Car Name -->
                    <div class="form-group">
                        <label><i class="fas fa-car"></i> Car Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($car['name']); ?>" placeholder="e.g. BMW X5" required>
                    </div>

                    <!-- Type & Price -->
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Car Type</label>
                            <select name="type" required>
                                <option value="">Select Type</option>
                                <option value="SUV" <?php if($car['type']=="SUV") echo "selected"; ?>>SUV</option>
                                <option value="Sedan" <?php if($car['type']=="Sedan") echo "selected"; ?>>Sedan</option>
                                <option value="Hatchback" <?php if($car['type']=="Hatchback") echo "selected"; ?>>Hatchback</option>
                                <option value="Luxury" <?php if($car['type']=="Luxury") echo "selected"; ?>>Luxury</option>
                                <option value="Sports" <?php if($car['type']=="Sports") echo "selected"; ?>>Sports</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-rupee-sign"></i> Price per Day</label>
                            <input type="number" name="price" value="<?php echo $car['price_per_day']; ?>" min="100" required>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Car Image (Optional)</label>
                        <div class="file-upload">
                            <input type="file" name="image" accept="image/*" onchange="previewImage(event)">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload new image</p>
                            <small>Leave empty to keep current image</small>
                            <img id="preview" class="preview-img">
                        </div>
                    </div>

                </div>

                <!-- Card Footer -->
                <div class="card-footer">
                    <a href="manage_cars.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>

            </div>
        </form>

    </div>
</main>

<script>
    function previewImage(event) {
        const preview = document.getElementById('preview');
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