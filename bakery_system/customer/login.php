<?php
include '../includes/functions.php';

// redirect if already logged in as customer
if (is_logged_in() && get_role() == 'customer') {
    header('Location: menu.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/db_connect.php';
    
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = '$username' AND role = 'customer' AND is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = 'customer';
            header('Location: menu.php');
            exit;
        } else {
            $error = 'Wrong password';
        }
    } else {
        $error = 'Customer not found';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Login - Bakery Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI'; background: linear-gradient(135deg, #f5e6d3 0%, #d4a574 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center;
        }
        .login-box {
            background: white; padding: 40px; border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); width: 100%; max-width: 400px;
        }
        .login-box h1 { color: #8B4513; text-align: center; margin-bottom: 10px; }
        .login-box p.subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; color: #5D4037; font-weight: 600; }
        .form-group input {
            width: 100%; padding: 12px; border: 2px solid #d4a574; border-radius: 8px; font-size: 16px;
        }
        .btn-login {
            width: 100%; padding: 14px; background: #8B4513; color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;
        }
        .btn-login:hover { background: #6d360f; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .register-link { text-align: center; margin-top: 20px; color: #666; }
        .register-link a { color: #8B4513; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🥖 Bakery Shop</h1>
        <p class="subtitle">Customer Login</p>
        
        <?php if ($error) echo '<div class="error">' . $error . '</div>'; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="register-link">
            New customer? <a href="register.php">Create account</a>
        </div>
    </div>
</body>
</html>