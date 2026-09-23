<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

$baker_name = $_SESSION['full_name'];

// get low stock count
$sql = "SELECT COUNT(*) as total FROM raw_materials WHERE quantity <= min_stock";
$result = mysqli_query($conn, $sql);
$low_stock = mysqli_fetch_assoc($result)['total'];

// get pending orders
$sql = "SELECT COUNT(*) as total FROM orders WHERE status IN ('confirmed', 'pending', 'baking')";
$result = mysqli_query($conn, $sql);
$pending_orders = mysqli_fetch_assoc($result)['total'];

// get today's production
$today = date('Y-m-d');
$sql = "SELECT COALESCE(SUM(quantity_produced), 0) as total FROM production_log WHERE production_date = '$today'";
$result = mysqli_query($conn, $sql);
$today_production = mysqli_fetch_assoc($result)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Baker Dashboard - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 250px; height: 100vh;
            background: #2E7D32; color: white; padding: 20px; overflow-y: auto;
        }
        .sidebar h2 { margin-bottom: 30px; text-align: center; font-size: 22px; }
        .sidebar a {
            display: block; color: #e8f5e9; text-decoration: none;
            padding: 12px 15px; margin-bottom: 5px; border-radius: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: #1B5E20; color: white; }
        .sidebar .logout { margin-top: 30px; background: #c62828; text-align: center; }
        .main { margin-left: 250px; padding: 20px; }
        .header {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { color: #2E7D32; }
        .breadcrumb { margin-bottom: 20px; color: #2E7D32; }
        .breadcrumb a { color: #2E7D32; text-decoration: none; }
        
        .cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .card {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;
        }
        .card h3 { color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .card .number { font-size: 32px; font-weight: bold; color: #2E7D32; }
        .card.alert .number { color: #FF9800; }
        
        .quick-actions {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .quick-actions h3 { color: #2E7D32; margin-bottom: 15px; }
        .btn {
            display: inline-block; padding: 15px 30px; margin-right: 15px; margin-bottom: 15px;
            background: #2E7D32; color: white; text-decoration: none;
            border-radius: 8px; font-size: 16px; font-weight: bold;
        }
        .btn:hover { background: #1B5E20; }
        .btn-alert { background: #FF9800; }
        .btn-alert:hover { background: #F57C00; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="view_inventory.php">📦 Inventory</a>
        <a href="update_stock.php">➕ Update Stock</a>
        <a href="pending_orders.php">📋 Pending Orders</a>
        <a href="production_log.php">🏭 Production</a>
        <a href="recipes.php">📖 Recipes</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Dashboard</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Welcome, <?php echo $baker_name; ?>!</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Dashboard
        </div>
        
        <div class="cards">
            <div class="card alert">
                <h3>Low Stock Items</h3>
                <div class="number"><?php echo $low_stock; ?></div>
            </div>
            <div class="card">
                <h3>Pending Orders</h3>
                <div class="number"><?php echo $pending_orders; ?></div>
            </div>
            <div class="card">
                <h3>Today's Production</h3>
                <div class="number"><?php echo $today_production; ?></div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <a href="view_inventory.php" class="btn">📦 Check Inventory</a>
            <a href="pending_orders.php" class="btn btn-alert">📋 View Orders</a>
            <a href="production_log.php" class="btn">🏭 Log Production</a>
        </div>
    </div>
</body>
</html>