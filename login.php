<?php
session_start();
include("includes/db.php");

$error = "";

if(isset($_POST['login'])){
    $role = $_POST['role'];
    $password = $_POST['password'];

    if($role == "user"){
        $email = $_POST['email'];
        // Using Prepared Statements for security
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
    } else {
        $username = $_POST['username'];
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){
        $isValid = false;

        // ADMIN LOGIC: Uses MD5 as per your SQL commands
        if($role == "admin"){
            if(md5($password) === $row['password']){
                $isValid = true;
            }
        } 
        // USER LOGIC: Uses password_verify for modern security
        else {
            if(password_verify($password, $row['password'])){
                $isValid = true;
            }
        }

        if($isValid){
            if($role == "user"){
                $_SESSION['user'] = $row['email'];
                $_SESSION['user_id'] = $row['id'];
                header("Location: index.php");
            } else {
                $_SESSION['admin'] = $row['username'];
                header("Location: admin/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "Account not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Car Rental</title>
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
            padding: 35px;
            border-radius: 20px;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(15px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }
        h2 { text-align: center; margin-bottom: 25px; }
        .toggle {
            display: flex;
            background: #1e293b;
            padding: 5px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .toggle button {
            flex: 1;
            padding: 10px;
            border: none;
            cursor: pointer;
            background: transparent;
            color: white;
            border-radius: 8px;
            transition: 0.3s;
        }
        .toggle button.active { background: #F59E0B; color: black; font-weight: 600; }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: none;
            background: #1e293b;
            color: white;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #F59E0B;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .error { color: #ff4d4d; text-align: center; margin-bottom: 10px; font-size: 0.9rem; }
        .link { text-align: center; margin-top: 15px; font-size: 0.9rem; }
        .link a { color: #F59E0B; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <div class="toggle">
        <button id="userBtn" class="active" onclick="setRole('user')">User</button>
        <button id="adminBtn" onclick="setRole('admin')">Admin</button>
    </div>

    <form method="POST">
        <input type="hidden" name="role" id="role" value="user">
        
        <input type="email" name="email" id="emailField" placeholder="Email Address" required>
        
        <input type="text" name="username" id="usernameField" placeholder="Admin Username" style="display:none;">
        
        <input type="password" name="password" placeholder="Password" required>

        <?php if($error) echo "<div class='error'>$error</div>"; ?>

        <button class="btn" name="login">Login</button>
    </form>

    <div class="link">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

<script>
function setRole(role){
    document.getElementById("role").value = role;
    const userBtn = document.getElementById("userBtn");
    const adminBtn = document.getElementById("adminBtn");
    const emailField = document.getElementById("emailField");
    const usernameField = document.getElementById("usernameField");

    if(role === 'admin'){
        adminBtn.classList.add("active");
        userBtn.classList.remove("active");
        emailField.style.display = "none";
        emailField.required = false;
        usernameField.style.display = "block";
        usernameField.required = true;
    } else {
        userBtn.classList.add("active");
        adminBtn.classList.remove("active");
        emailField.style.display = "block";
        emailField.required = true;
        usernameField.style.display = "none";
        usernameField.required = false;
    }
}
</script>

</body>
</html>