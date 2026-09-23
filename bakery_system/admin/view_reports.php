<?php
include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

$period = isset($_GET['period']) ? clean($_GET['period']) : 'monthly';

if ($period == 'daily') {
    $sql = "SELECT DATE(sale_date) as period, SUM(total) as sales, COUNT(*) as count 
            FROM sales GROUP BY DATE(sale_date) ORDER BY period DESC LIMIT 30";
} elseif ($period == 'weekly') {
    $sql = "SELECT CONCAT(YEAR(sale_date), '-W', WEEK(sale_date)) as period, 
            SUM(total) as sales, COUNT(*) as count 
            FROM sales GROUP BY YEAR(sale_date), WEEK(sale_date) ORDER BY period DESC LIMIT 12";
} else {
    $sql = "SELECT DATE_FORMAT(sale_date, '%Y-%m') as period, 
            SUM(total) as sales, COUNT(*) as count 
            FROM sales GROUP BY DATE_FORMAT(sale_date, '%Y-%m') ORDER BY period DESC LIMIT 12";
}

$result = mysqli_query($conn, $sql);

// get inventory usage
$sql2 = "SELECT m.name, SUM(pm.quantity_used) as total_used 
         FROM production_materials pm 
         JOIN raw_materials m ON pm.material_id = m.material_id 
         GROUP BY pm.material_id ORDER BY total_used DESC";
$inventory_result = mysqli_query($conn, $sql2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 250px; height: 100vh;
            background: #5D4037; color: white; padding: 20px; overflow-y: auto;
        }
        .sidebar h2 { margin-bottom: 30px; text-align: center; font-size: 22px; }
        .sidebar a {
            display: block; color: #f5e6d3; text-decoration: none;
            padding: 12px 15px; margin-bottom: 5px; border-radius: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: #8B4513; color: white; }
        .sidebar .logout { margin-top: 30px; background: #c62828; text-align: center; }
        .main { margin-left: 250px; padding: 20px; }
        .header {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { color: #5D4037; }
        .breadcrumb { margin-bottom: 20px; color: #8B4513; }
        .breadcrumb a { color: #8B4513; text-decoration: none; }
        
        .filters {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 10px 20px; background: #8B4513; color: white;
            border: none; border-radius: 8px; cursor: pointer; margin-right: 10px;
            text-decoration: none; display: inline-block;
        }
        .btn:hover { background: #6d360f; }
        .btn-export { background: #4CAF50; }
        .btn-export:hover { background: #388E3C; }
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse; margin-bottom: 20px;
        }
        th { background: #8B4513; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="all_orders.php">📦 All Orders</a>
        <a href="verify_payments.php">💳 Verify Payments</a>
        <a href="dispatch_orders.php">🚚 Dispatch Orders</a>
        <a href="view_reports.php" class="active">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="notifications.php">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #8B4513;">
            <small>Breadcrumb:</small><br>
            <small>Home > Reports</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Sales Reports</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Reports
        </div>
        
        <div class="filters">
            <a href="?period=daily" class="btn <?php echo $period == 'daily' ? 'active' : ''; ?>">Daily</a>
            <a href="?period=weekly" class="btn <?php echo $period == 'weekly' ? 'active' : ''; ?>">Weekly</a>
            <a href="?period=monthly" class="btn <?php echo $period == 'monthly' ? 'active' : ''; ?>">Monthly</a>
            <a href="../reports/export_sales.php" class="btn btn-export">📥 Export CSV</a>
        </div>
        
        <h3 style="color: #5D4037; margin-bottom: 15px;">Sales Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Total Sales</th>
                    <th>Number of Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['period']; ?></td>
                    <td><?php echo format_money($row['sales']); ?></td>
                    <td><?php echo $row['count']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h3 style="color: #5D4037; margin-bottom: 15px;">Inventory Usage</h3>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Total Used</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($inventory_result)): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['total_used'] . ' units'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>