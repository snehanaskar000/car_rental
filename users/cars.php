<?php
session_start();
include("../includes/db.php");
include("../includes/header.php");

// 1. INPUT SANITIZATION LOGIC
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : "";
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 10000;

// 2. DYNAMIC QUERY BUILDING
$query = "SELECT * FROM cars WHERE status = 'available'";

if($type != "") {
    $query .= " AND type = '$type'";
}
if($search != "") {
    $query .= " AND name LIKE '%$search%'";
}
$query .= " AND price_per_day <= $max_price";
$query .= " ORDER BY price_per_day ASC";

$res = mysqli_query($conn, $query);
$count = mysqli_num_rows($res);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    :root {
        --gold: #F59E0B;
        --dark-blue: #0f172a;
        --glass: rgba(30, 41, 59, 0.7);
        --border: rgba(255, 255, 255, 0.1);
    }

    body {
        background-color: var(--dark-blue);
        color: #f8fafc;
        font-family: 'Poppins', sans-serif;
    }

    .main-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* FILTER BAR SECTION */
    .search-section {
        background: var(--glass);
        backdrop-filter: blur(10px);
        padding: 25px;
        border-radius: 15px;
        border: 1px solid var(--border);
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 40px;
    }

    .form-group { flex: 1; min-width: 200px; }
    .form-group label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 8px; font-weight: 600; text-transform: uppercase; }
    
    .form-group input, .form-group select {
        width: 100%;
        padding: 12px;
        background: #020617;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: white;
        outline: none;
    }

    .btn-filter {
        background: var(--gold);
        color: #000;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(245,158,11,0.4); }

    /* CAR GRID SECTION */
    .car-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    .car-card {
        background: var(--card);
        background-color: #1e293b;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid var(--border);
        transition: 0.4s;
    }

    .car-card:hover { transform: translateY(-10px); border-color: var(--gold); }

    .img-container { width: 100%; height: 200px; position: relative; }
    .img-container img { width: 100%; height: 100%; object-fit: cover; }
    
    .type-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--gold); color: #000;
        padding: 4px 10px; font-size: 11px; font-weight: 800; border-radius: 5px;
    }

    .car-content { padding: 20px; }
    .car-content h3 { font-size: 20px; margin-bottom: 10px; }
    
    .car-features { display: flex; gap: 15px; color: #94a3b8; font-size: 13px; margin-bottom: 20px; }
    .car-features i { color: var(--gold); }

    .price-row {
        display: flex; justify-content: space-between; align-items: center;
        border-top: 1px solid var(--border); padding-top: 15px;
    }

    .price-val { font-size: 22px; font-weight: 800; color: var(--gold); }
    .price-val span { font-size: 12px; color: #94a3b8; font-weight: 400; }

    .btn-book {
        background: transparent; border: 1px solid var(--gold); color: var(--gold);
        padding: 8px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s;
    }

    .btn-book:hover { background: var(--gold); color: #000; }

    /* EMPTY STATE */
    .no-results { text-align: center; padding: 100px 0; grid-column: 1 / -1; }
</style>

<div class="main-container">
    
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 32px;">Our <span style="color: var(--gold);">Fleet</span></h2>
        <p style="color: #94a3b8;">Found <?php echo $count; ?> cars available for you.</p>
    </div>

    <form method="GET" class="search-section">
        <div class="form-group">
            <label>Search Name</label>
            <input type="text" name="search" placeholder="Search car name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="form-group">
            <label>Car Type</label>
            <select name="type">
                <option value="">All Types</option>
                <option value="SUV" <?php echo ($type == 'SUV') ? 'selected' : ''; ?>>SUV</option>
                <option value="Sedan" <?php echo ($type == 'Sedan') ? 'selected' : ''; ?>>Sedan</option>
                <option value="Hatchback" <?php echo ($type == 'Hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                <option value="Luxury" <?php echo ($type == 'Luxury') ? 'selected' : ''; ?>>Luxury</option>
            </select>
        </div>

        <div class="form-group">
            <label>Max Budget (per day)</label>
            <input type="range" name="max_price" min="500" max="10000" step="500" value="<?php echo $max_price; ?>" oninput="this.nextElementSibling.innerText = '₹' + this.value">
            <span style="display:block; color: var(--gold); font-weight: bold; margin-top: 5px;">₹<?php echo $max_price; ?></span>
        </div>

        <button type="submit" class="btn-filter">FILTER RESULTS</button>
        <a href="cars.php" style="color: #ef4444; font-size: 13px; text-decoration: none; margin-bottom: 15px;">Reset</a>
    </form>

    <div class="car-grid">
        <?php if($count > 0): ?>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
                <div class="car-card">
                    <div class="img-container">
                        <img src="../uploads/car_images/<?php echo $row['image']; ?>" onerror="this.src='https://via.placeholder.com/400x250?text=Car+Image'">
                        <div class="type-badge"><?php echo $row['type']; ?></div>
                    </div>
                    
                    <div class="car-content">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        
                        <div class="car-features">
                            <span><i class="fas fa-users"></i> 5 Seats</span>
                            <span><i class="fas fa-cog"></i> Auto</span>
                        </div>

                        <div class="price-row">
                            <div class="price-val">₹<?php echo number_format($row['price_per_day']); ?> <span>/ day</span></div>
                            <a href="car_details.php?id=<?php echo $row['id']; ?>" class="btn-book">Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search-minus" style="font-size: 50px; color: #334155; margin-bottom: 20px;"></i>
                <h3>No cars found!</h3>
                <p style="color: #94a3b8;">Try adjusting your filters or price range.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>