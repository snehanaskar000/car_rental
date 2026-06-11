<?php
session_start();
include("includes/db.php");

$error = "";
$success = "";

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    // Use password_hash instead of md5 for modern security
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // CHECK IF EMAIL EXISTS using Prepared Statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $error = "Email already exists!";
    } else {
        // INSERT NEW USER
        $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $name, $email, $pass);
        
        if($insert->execute()){
            $success = "Registration Successful! <a href='login.php' style='color:white; font-weight:bold;'>Login here</a>";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Car Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0B1120, #1e293b);
            color: white;
        }
        .container {
            width: 380px;
            padding: 40px;
            border-radius: 20px;
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 { text-align: center; margin-bottom: 25px; font-weight: 600; }
        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(15, 23, 42, 0.6);
            color: white;
            outline: none;
        }
        input:focus { border-color: #F59E0B; }
        .btn {
            width: 100%;
            padding: 14px;
            background: #F59E0B;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn:hover { background: #d97706; transform: translateY(-2px); }
        .msg { text-align: center; margin-top: 15px; padding: 10px; border-radius: 8px; font-size: 0.9rem; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .link { text-align: center; margin-top: 25px; font-size: 0.9rem; color: #94a3b8; }
        .link a { color: #F59E0B; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Account</h2>

    <form method="POST" action="">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="register" class="btn">Register</button>
    </form>

    <?php if($error): ?>
        <div class="msg error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="msg success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>