<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

// update order status
if (isset($_GET['status']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $status = clean($_GET['status']);
    
    $sql = "UPDATE orders SET status = '$status' WHERE order_id = $order_id";
    mysqli_query($conn, $sql);
    
    // if ready, notify customer
    if ($status == 'ready') {
        $sql = "INSERT INTO notifications (user_id, title, message, link) 
                SELECT customer_id, 'Order Ready', CONCAT('Your order #', order_id, ' is ready for pickup!'), 'customer/track_order.php?order_id=$order_id' 
                FROM orders WHERE order_id = $order_id";
        mysqli_query($conn, $sql);
    }
    
    header('Location: pending_orders.php');
    exit;
}

// get pending orders
$sql = "SELECT o.*, c.full_name as customer_name, c.phone 
        FROM orders o 
        JOIN users c ON o.customer_id = c.user_id 
        WHERE o.status IN ('pending', 'confirmed', 'baking') 
        AND (o.payment_status = 'paid' OR o.payment_method != 'mpesa')
        ORDER BY o.created_at ASC";
        
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Orders - Bakery System</title>
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
        
        .order-card {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-card h3 { color: #2E7D32; margin-bottom: 10px; }
        .order-info { color: #666; margin-bottom: 10px; }
        .btn {
            padding: 8px 15px; margin-right: 10px; background: #2E7D32;
            color: white; text-decoration: none; border-radius: 6px;
            display: inline-block; border: none; cursor: pointer;
        }
        .btn-baking { background: #FF9800; }
        .btn-ready { background: #4CAF50; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="view_inventory.php">📦 Inventory</a>
        <a href="update_stock.php">➕ Update Stock</a>
        <a href="pending_orders.php" class="active">📋 Pending Orders</a>
        <a href="production_log.php">🏭 Production</a>
        <a href="recipes.php">📖 Recipes</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Pending Orders</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Orders to Bake</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Pending Orders
        </div>
        
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="order-card">
            <h3>Order #<?php echo $row['order_id']; ?> - <?php echo $row['customer_name']; ?></h3>
            <div class="order-info">
                📞 <?php echo $row['phone']; ?> | 
                📅 <?php echo date('M d, Y', strtotime($row['pickup_date'] ?: $row['delivery_date'])); ?> |
                💰 <?php echo format_money($row['total']); ?> |
                Status: <strong><?php echo ucfirst($row['status']); ?></strong>
            </div>
            
            <?php if ($row['status'] == 'pending'): ?>
                <a href="?status=confirmed&order_id=<?php echo $row['order_id']; ?>" class="btn">Confirm Order</a>
            <?php elseif ($row['status'] == 'confirmed'): ?>
                <a href="?status=baking&order_id=<?php echo $row['order_id']; ?>" class="btn btn-baking">Start Baking</a>
            <?php elseif ($row['status'] == 'baking'): ?>
                <a href="?status=ready&order_id=<?php echo $row['order_id']; ?>" class="btn btn-ready">Mark Ready</a>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>