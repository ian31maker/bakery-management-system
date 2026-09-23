<?php
include '../includes/functions.php';
require_role(['driver']);
include '../includes/db_connect.php';

$driver_id = $_SESSION['user_id'];

// get completed deliveries
$sql = "SELECT o.*, c.full_name as customer_name 
        FROM orders o 
        JOIN users c ON o.customer_id = c.user_id 
        WHERE o.driver_id = $driver_id AND o.status = 'delivered' 
        ORDER BY o.updated_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery History - Bakery System</title>
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
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #E65100; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="assigned_deliveries.php">📦 My Deliveries</a>
        <a href="delivery_history.php" class="active">📋 History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #BF360C;">
            <small>Breadcrumb:</small><br>
            <small>Home > History</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Delivery History</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > History
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Delivered On</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo format_money($row['total']); ?></td>
                    <td><?php 
    if ($row['updated_at']) {
        echo date('M d, Y H:i', strtotime($row['updated_at']));
    } else {
        echo 'N/A';
    }
?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>