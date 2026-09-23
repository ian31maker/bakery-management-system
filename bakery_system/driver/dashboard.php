<?php
include '../includes/functions.php';
require_role(['driver']);
include '../includes/db_connect.php';

$driver_name = $_SESSION['full_name'];
$driver_id = $_SESSION['user_id'];

// get assigned deliveries
$sql = "SELECT COUNT(*) as total FROM orders WHERE driver_id = $driver_id AND status = 'out_for_delivery'";
$result = mysqli_query($conn, $sql);
$active_deliveries = mysqli_fetch_assoc($result)['total'];

// get completed today
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) as total FROM orders WHERE driver_id = $driver_id AND status = 'delivered' AND DATE(updated_at) = '$today'";
$result = mysqli_query($conn, $sql);
$completed_today = mysqli_fetch_assoc($result)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 250px; height: 100vh;
            background: #E65100; color: white; padding: 20px; overflow-y: auto;
        }
        .sidebar h2 { margin-bottom: 30px; text-align: center; font-size: 22px; }
        .sidebar a {
            display: block; color: #fff3e0; text-decoration: none;
            padding: 12px 15px; margin-bottom: 5px; border-radius: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: #BF360C; color: white; }
        .sidebar .logout { margin-top: 30px; background: #c62828; text-align: center; }
        .main { margin-left: 250px; padding: 20px; }
        .header {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { color: #E65100; }
        .breadcrumb { margin-bottom: 20px; color: #E65100; }
        .breadcrumb a { color: #E65100; text-decoration: none; }
        
        .cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .card {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;
        }
        .card h3 { color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .card .number { font-size: 32px; font-weight: bold; color: #E65100; }
        
        .btn {
            display: inline-block; padding: 15px 30px; margin-right: 15px;
            background: #E65100; color: white; text-decoration: none;
            border-radius: 8px; font-size: 16px; font-weight: bold;
        }
        .btn:hover { background: #BF360C; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="assigned_deliveries.php">📦 My Deliveries</a>
        <a href="delivery_history.php">📋 History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #BF360C;">
            <small>Breadcrumb:</small><br>
            <small>Home > Dashboard</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Welcome, <?php echo $driver_name; ?>!</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Dashboard
        </div>
        
        <div class="cards">
            <div class="card">
                <h3>Active Deliveries</h3>
                <div class="number"><?php echo $active_deliveries; ?></div>
            </div>
            <div class="card">
                <h3>Completed Today</h3>
                <div class="number"><?php echo $completed_today; ?></div>
            </div>
        </div>
        
        <a href="assigned_deliveries.php" class="btn">📦 View Deliveries</a>
    </div>
</body>
</html>