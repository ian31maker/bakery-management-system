<?php
include '../includes/functions.php';
require_role(['cashier']);
include '../includes/db_connect.php';

$cashier_name = $_SESSION['full_name'];

// get today's sales
$today = date('Y-m-d');
$sql = "SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as count FROM sales WHERE DATE(sale_date) = '$today'";
$result = mysqli_query($conn, $sql);
$today_sales = mysqli_fetch_assoc($result);

// get pending tickets
$sql = "SELECT COUNT(*) as total FROM tickets WHERE status = 'waiting'";
$result = mysqli_query($conn, $sql);
$pending_tickets = mysqli_fetch_assoc($result)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Dashboard - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 250px; height: 100vh;
            background: #1565C0; color: white; padding: 20px; overflow-y: auto;
        }
        .sidebar h2 { margin-bottom: 30px; text-align: center; font-size: 22px; }
        .sidebar a {
            display: block; color: #e3f2fd; text-decoration: none;
            padding: 12px 15px; margin-bottom: 5px; border-radius: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: #0D47A1; color: white; }
        .sidebar .logout { margin-top: 30px; background: #c62828; text-align: center; }
        .main { margin-left: 250px; padding: 20px; }
        .header {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { color: #1565C0; }
        .breadcrumb { margin-bottom: 20px; color: #1565C0; }
        .breadcrumb a { color: #1565C0; text-decoration: none; }
        
        .cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .card {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;
        }
        .card h3 { color: #666; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .card .number { font-size: 32px; font-weight: bold; color: #1565C0; }
        
        .quick-actions {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .quick-actions h3 { color: #1565C0; margin-bottom: 15px; }
        .btn {
            display: inline-block; padding: 15px 30px; margin-right: 15px; margin-bottom: 15px;
            background: #1565C0; color: white; text-decoration: none;
            border-radius: 8px; font-size: 16px; font-weight: bold;
        }
        .btn:hover { background: #0D47A1; }
        .btn-ticket { background: #FF9800; }
        .btn-ticket:hover { background: #F57C00; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="record_sale.php">💰 Record Sale</a>
        <a href="ticket_queue.php">🎫 Ticket Queue</a>
        <a href="sales_history.php">📋 Sales History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #0D47A1;">
            <small>Breadcrumb:</small><br>
            <small>Home > Dashboard</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Welcome, <?php echo $cashier_name; ?>!</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Dashboard
        </div>
        
        <div class="cards">
            <div class="card">
                <h3>Today's Sales</h3>
                <div class="number"><?php echo format_money($today_sales['total']); ?></div>
            </div>
            <div class="card">
                <h3>Sales Count</h3>
                <div class="number"><?php echo $today_sales['count']; ?></div>
            </div>
            <div class="card">
                <h3>Pending Tickets</h3>
                <div class="number" style="color: #FF9800;"><?php echo $pending_tickets; ?></div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <a href="record_sale.php" class="btn">🛒 New Sale</a>
            <a href="ticket_queue.php" class="btn btn-ticket">🎫 Ticket Queue</a>
            <a href="sales_history.php" class="btn">📋 View History</a>
        </div>
    </div>
</body>
</html>