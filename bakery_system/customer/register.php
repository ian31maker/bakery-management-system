<?php
// ============================================
// FILE: customer/register.php
// JOB:  Let new customers create an account
// ============================================

include '../includes/functions.php';
include '../includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    
    // grab and clean inputs
    $full_name = clean($_POST['full_name']);
    $username  = clean($_POST['username']);
    $phone     = clean($_POST['phone']);
    $address   = clean($_POST['address']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    
    // check for empty fields
    if (empty($full_name) || empty($username) || empty($phone) || empty($password)) {
        $error = 'Please fill in all required fields';
    }
    // check passwords match
    elseif ($password != $confirm) {
        $error = 'Passwords do not match';
    }
    // check username is not taken
    else {
        $check = "SELECT user_id FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Username already taken. Please choose another.';
        } else {
            // hash the password safely
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // insert new customer
            $sql = "INSERT INTO users (full_name, username, phone, address, password_hash, role, is_active) 
                    VALUES ('$full_name', '$username', '$phone', '$address', '$hash', 'customer', 1)";
            
            if (mysqli_query($conn, $sql)) {
                $success = 'Account created! You can now login.';
            } else {
                $error = 'Something went wrong: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Bakery Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI'; background: linear-gradient(135deg, #f5e6d3 0%, #d4a574 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center;
        }
        .box {
            background: white; padding: 40px; border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); width: 100%; max-width: 450px;
        }
        .box h1 { color: #8B4513; text-align: center; margin-bottom: 10px; }
        .box p.subtitle { text-align: center; color: #666; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #5D4037; font-weight: 600; font-size: 14px; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 12px; border: 2px solid #d4a574; border-radius: 8px; font-size: 15px;
        }
        .btn {
            width: 100%; padding: 14px; background: #8B4513; color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;
        }
        .btn:hover { background: #6d360f; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .links { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .links a { color: #8B4513; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🥖 Bakery Shop</h1>
        <p class="subtitle">Create your customer account</p>
        
        <?php if ($error) echo '<div class="error">' . $error . '</div>'; ?>
        <?php if ($success) echo '<div class="success">' . $success . '</div>'; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" placeholder="e.g. John Doe" required>
            </div>
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone" placeholder="e.g. 0712345678" required>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="address" rows="2" placeholder="Your address (optional)"></textarea>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" placeholder="Create password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="Repeat password" required>
            </div>
            
            <button type="submit" name="register" class="btn">Create Account</button>
        </form>
        
        <div class="links">
            Already have an account? <a href="../login.php">Login here</a>
        </div>
    </div>
</body>
</html>