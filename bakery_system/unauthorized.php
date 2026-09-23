<?php
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #ffebee;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .box {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1 { color: #c62828; font-size: 60px; margin-bottom: 10px; }
        p { color: #666; margin-bottom: 20px; }
        a {
            display: inline-block;
            padding: 12px 30px;
            background: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>🚫</h1>
        <h2>Access Denied</h2>
        <p>You do not have permission to view this page.</p>
        <a href="index.php">Back to Login</a>
    </div>
</body>
</html>