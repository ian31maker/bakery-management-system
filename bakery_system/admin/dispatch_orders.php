<?php
// ============================================
// FILE: admin/dispatch_orders.php
// JOB:  Admin assigns ready orders to drivers
// ============================================

include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

$message = '';

// assign driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_driver'])) {
    $order_id = intval($_POST['order_id']);
    $driver_id = intval($_POST['driver_id']);

    if ($driver_id <= 0) {
        $message = show_error('Please select a driver first.');
    } else {
        $sql = "UPDATE orders 
                SET driver_id = $driver_id,
                    status = 'dispatched'
                WHERE order_id = $order_id";

        if (mysqli_query($conn, $sql)) {
            // get driver name
            $driver_sql = "SELECT full_name FROM users WHERE user_id = $driver_id";
            $driver_result = mysqli_query($conn, $driver_sql);
            $driver = mysqli_fetch_assoc($driver_result);

            // notify driver with direct link to the order
            $driver_link = '../driver/assigned_deliveries.php?order_id=' . $order_id;
            $sql = "INSERT INTO notifications (user_id, title, message, link) 
                    VALUES ($driver_id, 'New Delivery', 'You have been assigned order #$order_id', '$driver_link')";
            mysqli_query($conn, $sql);

            // notify customer with direct link to track order
            $customer_link = '../customer/track_order.php?order_id=' . $order_id;
            $sql = "INSERT INTO notifications (user_id, title, message, link) 
                    SELECT customer_id, 'Order Dispatched', CONCAT('Your order #', $order_id, ' is on the way!'), '$customer_link'
                    FROM orders WHERE order_id = $order_id";
            mysqli_query($conn, $sql);

            $message = show_success('✅ Order #' . $order_id . ' assigned to ' . $driver['full_name'] . '!');
        } else {
            $message = show_error('Error: ' . mysqli_error($conn));
        }
    }
}

// get ready orders
$sql = "SELECT o.*, u.full_name as customer_name, u.phone, u.address 
        FROM orders o 
        JOIN users u ON o.customer_id = u.user_id 
        WHERE o.status = 'ready' AND (o.driver_id IS NULL OR o.driver_id = 0)
        ORDER BY o.order_id DESC";
$ready_orders = mysqli_query($conn, $sql);

// get available drivers
$driver_sql = "SELECT user_id, full_name, phone FROM users WHERE role = 'driver'";
$drivers = mysqli_query($conn, $driver_sql);

// get dispatched/out_for_delivery orders
$sql2 = "SELECT o.*, u.full_name as customer_name, u.phone, d.full_name as driver_name 
         FROM orders o 
         JOIN users u ON o.customer_id = u.user_id 
         LEFT JOIN users d ON o.driver_id = d.user_id
         WHERE o.status IN ('dispatched', 'out_for_delivery')
         ORDER BY o.order_id DESC";
$dispatched = mysqli_query($conn, $sql2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dispatch Orders - Bakery System</title>
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

        .order-card {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-card h3 { color: #2E7D32; margin-bottom: 10px; }
        .info { color: #666; margin-bottom: 8px; font-size: 14px; }
        .info strong { color: #333; }

        select {
            padding: 10px; border: 2px solid #d4a574; border-radius: 6px;
            margin-right: 10px; min-width: 200px; font-size: 14px;
        }
        .btn-assign {
            background: #8B4513; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            font-weight: bold; font-size: 14px;
        }
        .btn-assign:hover { background: #6d360f; }

        .empty {
            text-align: center; padding: 40px; color: #666;
            background: white; border-radius: 10px;
        }

        .section { margin-bottom: 30px; }
        .section-title {
            color: #8B4513; font-size: 18px; margin-bottom: 15px;
            border-bottom: 2px solid #d4a574; padding-bottom: 8px;
        }

        .driver-badge {
            display: inline-block; background: #e8f5e9; color: #2e7d32;
            padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;
        }
        .status-out { 
            display: inline-block; background: #fff3e0; color: #e65100;
            padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="all_orders.php">📦 All Orders</a>
        <a href="verify_payments.php">💳 Verify Payments</a>
        <a href="dispatch_orders.php" class="active">🚚 Dispatch Orders</a>
        <a href="view_reports.php">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="notifications.php">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>🚚 Dispatch Orders to Drivers</h1>
        </div>

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Dispatch Orders
        </div>

        <?php echo $message; ?>

        <div class="section">
            <div class="section-title">Ready for Delivery (Baker marked as Ready)</div>
            <?php if (mysqli_num_rows($ready_orders) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($ready_orders)): ?>
                <div class="order-card">
                    <h3>Order #<?php echo $row['order_id']; ?> - <?php echo $row['customer_name']; ?></h3>
                    <div class="info">📞 <strong>Phone:</strong> <?php echo $row['phone']; ?></div>
                    <div class="info">📍 <strong>Address:</strong> <?php echo $row['delivery_address'] ?? $row['address'] ?? 'Pickup at bakery'; ?></div>
                    <div class="info">💰 <strong>Amount:</strong> KSh <?php echo number_format($row['total'], 2); ?></div>

                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <select name="driver_id" required>
                            <option value="">Select Driver...</option>
                            <?php 
                            mysqli_data_seek($drivers, 0);
                            while ($d = mysqli_fetch_assoc($drivers)): 
                            ?>
                            <option value="<?php echo $d['user_id']; ?>">
                                <?php echo $d['full_name']; ?> (<?php echo $d['phone']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="assign_driver" class="btn-assign">Assign Driver</button>
                    </form>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty">
                    <h3>No orders ready for delivery</h3>
                    <p>Wait for the Baker to mark orders as "Ready".</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-title">Currently Out for Delivery</div>
            <?php if (mysqli_num_rows($dispatched) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($dispatched)): ?>
                <div class="order-card">
                    <h3>Order #<?php echo $row['order_id']; ?> - <?php echo $row['customer_name']; ?></h3>
                    <div class="info">📞 <?php echo $row['phone']; ?> | 💰 KSh <?php echo number_format($row['total'], 2); ?></div>
                    <div class="info">
                        🚚 <strong>Driver:</strong> <span class="driver-badge"><?php echo $row['driver_name'] ?? 'Not found'; ?></span>
                        <?php if ($row['status'] == 'out_for_delivery'): ?>
                        <span class="status-out">Out for Delivery</span>
                        <?php elseif ($row['status'] == 'dispatched'): ?>
                        <span class="driver-badge" style="background:#e3f2fd;color:#1565c0;">Dispatched</span>
                        <?php else: ?>
                        <span style="color:red;">Status: <?php echo $row['status']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty">
                    <p>No orders currently dispatched.</p>
                </div>
            <?php endif; ?>
        </div>

        <p style="margin-top: 20px;">
            <a href="dashboard.php" style="color: #8B4513;">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>