<?php
// ============================================
// FILE: driver/assigned_deliveries.php
// JOB:  Show driver ALL orders assigned to them
// BULLETPROOF: Shows any order where driver_id matches
// ============================================

include '../includes/functions.php';
require_role(['driver']);
include '../includes/db_connect.php';

$driver_id = $_SESSION['user_id'];

// update status
if (isset($_GET['status']) && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $status = clean($_GET['status']);

    $sql = "UPDATE orders SET status = '$status' WHERE order_id = $order_id AND driver_id = $driver_id";
    mysqli_query($conn, $sql);

    if ($status == 'out_for_delivery') {
        $sql = "INSERT INTO notifications (user_id, title, message, link) 
                SELECT customer_id, 'Order On The Way', CONCAT('Your order #', $order_id, ' is out for delivery!'), 'customer/track_order.php?order_id=$order_id' 
                FROM orders WHERE order_id = $order_id";
        mysqli_query($conn, $sql);
    }

    if ($status == 'delivered') {
        $sql = "INSERT INTO notifications (user_id, title, message, link) 
                SELECT customer_id, 'Order Delivered', CONCAT('Your order #', $order_id, ' has been delivered!'), 'customer/track_order.php?order_id=$order_id' 
                FROM orders WHERE order_id = $order_id";
        mysqli_query($conn, $sql);
    }

    header('Location: assigned_deliveries.php');
    exit;
}

// BULLETPROOF: Show ALL orders assigned to this driver, any status
$sql = "SELECT o.*, c.full_name as customer_name, c.phone as customer_phone, c.address as customer_address 
        FROM orders o 
        JOIN users c ON o.customer_id = c.user_id 
        WHERE o.driver_id = $driver_id 
        AND o.status != 'delivered'
        ORDER BY o.order_id DESC";
$result = mysqli_query($conn, $sql);
$active_count = mysqli_num_rows($result);

// get completed deliveries
$sql2 = "SELECT o.*, c.full_name as customer_name, c.phone 
         FROM orders o 
         JOIN users c ON o.customer_id = c.user_id 
         WHERE o.driver_id = $driver_id 
         AND o.status = 'delivered'
         ORDER BY o.order_id DESC LIMIT 10";
$completed = mysqli_query($conn, $sql2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Deliveries - Bakery System</title>
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

        .debug-box {
            background: #fff3e0; border: 2px solid #ff9800;
            padding: 15px; border-radius: 8px; margin-bottom: 20px;
            font-size: 13px;
        }
        .debug-box h4 { color: #e65100; margin-bottom: 10px; }
        .debug-box p { margin-bottom: 5px; }

        .delivery-card {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .delivery-card h3 { 
            color: #E65100; margin-bottom: 15px; 
            border-bottom: 2px solid #ffe0b2; padding-bottom: 10px;
        }
        .contact-box {
            background: #fff8e1; padding: 15px; border-radius: 8px;
            margin-bottom: 15px; border-left: 4px solid #ff9800;
        }
        .contact-box .label {
            font-size: 12px; color: #666; text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 5px;
        }
        .contact-box .value {
            font-size: 18px; font-weight: bold; color: #333;
        }
        .contact-box a { color: #E65100; text-decoration: none; }
        .contact-box a:hover { text-decoration: underline; }
        .contact-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
            margin-bottom: 15px;
        }
        @media (max-width: 700px) {
            .contact-grid { grid-template-columns: 1fr; }
        }
        .items-box {
            background: #fafafa; padding: 15px; border-radius: 8px;
            margin-bottom: 15px;
        }
        .items-box h4 {
            color: #5D4037; margin-bottom: 10px; font-size: 14px;
        }
        .item-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .item-row:last-child { border-bottom: none; }
        .actions {
            display: flex; gap: 10px; flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px; text-decoration: none; border-radius: 8px;
            display: inline-block; border: none; cursor: pointer;
            font-weight: bold; font-size: 14px;
        }
        .btn-start {
            background: #FF9800; color: white;
        }
        .btn-start:hover { background: #f57c00; }
        .btn-delivered {
            background: #4CAF50; color: white;
        }
        .btn-delivered:hover { background: #388e3c; }
        .btn-call {
            background: #2196F3; color: white;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-call:hover { background: #1976D2; }
        .btn-map {
            background: #8B4513; color: white;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-map:hover { background: #6d360f; }
        .status-badge {
            display: inline-block; padding: 6px 14px;
            border-radius: 12px; font-size: 12px; font-weight: bold;
            margin-bottom: 15px;
        }
        .status-dispatched { background: #e3f2fd; color: #1565c0; }
        .status-out { background: #fff3e0; color: #e65100; }
        .status-ready { background: #e8f5e9; color: #2e7d32; }
        .status-baking { background: #fff8e1; color: #e65100; }
        .status-confirmed { background: #e3f2fd; color: #1565c0; }
        .status-pending { background: #ffebee; color: #c62828; }
        .status-unknown { background: #f5f5f5; color: #999; }

        .empty {
            text-align: center; padding: 60px; color: #666;
            background: white; border-radius: 10px;
        }
        .empty h3 { margin-bottom: 10px; }
        .section { margin-bottom: 30px; }
        .section-title {
            color: #E65100; font-size: 18px; margin-bottom: 15px;
            border-bottom: 2px solid #ffe0b2; padding-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="assigned_deliveries.php" class="active">📦 My Deliveries</a>
        <a href="delivery_history.php">📋 History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>🚚 My Deliveries</h1>
        </div>

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > My Deliveries
        </div>

    

        <?php if ($active_count > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $order_id = $row['order_id'];
                $items_sql = "SELECT oi.*, p.name 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.product_id 
                              WHERE oi.order_id = $order_id";
                $items_result = mysqli_query($conn, $items_sql);

                $delivery_address = !empty($row['delivery_address']) ? $row['delivery_address'] : $row['customer_address'];
                $display_address = !empty($delivery_address) ? $delivery_address : 'No address provided - call customer';

                $display_date = $row['delivery_date'] ?? $row['pickup_date'] ?? 'Not scheduled';
                if ($display_date != 'Not scheduled') {
                    $display_date = date('M d, Y', strtotime($display_date));
                }

                // determine status badge class
                $status_class = 'status-unknown';
                $status_text = ucfirst(str_replace('_', ' ', $row['status']));
                if ($row['status'] == 'dispatched') { $status_class = 'status-dispatched'; }
                elseif ($row['status'] == 'out_for_delivery') { $status_class = 'status-out'; }
                elseif ($row['status'] == 'ready') { $status_class = 'status-ready'; }
                elseif ($row['status'] == 'baking') { $status_class = 'status-baking'; }
                elseif ($row['status'] == 'confirmed') { $status_class = 'status-confirmed'; }
                elseif ($row['status'] == 'pending') { $status_class = 'status-pending'; }
            ?>
            <div class="delivery-card">
                <h3>Order #<?php echo $order_id; ?> - <?php echo $row['customer_name']; ?></h3>

                <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo $status_text; ?>
                </span>

                <div class="contact-grid">
                    <div class="contact-box">
                        <div class="label">📞 Customer Phone</div>
                        <div class="value">
                            <a href="tel:<?php echo $row['customer_phone']; ?>">
                                <?php echo $row['customer_phone']; ?>
                            </a>
                        </div>
                    </div>
                    <div class="contact-box">
                        <div class="label">📍 Delivery Address</div>
                        <div class="value">
                            <?php if (!empty($delivery_address)): ?>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($delivery_address); ?>" target="_blank">
                                    <?php echo $display_address; ?> ↗
                                </a>
                            <?php else: ?>
                                <?php echo $display_address; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="items-box">
                    <h4>🧾 Order Items (<?php echo mysqli_num_rows($items_result); ?> items)</h4>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                    <div class="item-row">
                        <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                        <span>KSh <?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                    <?php endwhile; ?>
                    <div class="item-row" style="border-top: 2px solid #d4a574; margin-top: 10px; padding-top: 10px; font-weight: bold;">
                        <span>Total</span>
                        <span>KSh <?php echo number_format($row['total'], 2); ?></span>
                    </div>
                </div>

                <div style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    📅 <strong>Date:</strong> <?php echo $display_date; ?> | 
                    💰 <strong>Total:</strong> KSh <?php echo number_format($row['total'], 2); ?>
                </div>

                <div class="actions">
                    <a href="tel:<?php echo $row['customer_phone']; ?>" class="btn btn-call">
                        📞 Call Customer
                    </a>

                    <?php if (!empty($delivery_address)): ?>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($delivery_address); ?>" target="_blank" class="btn btn-map">
                        🗺️ Get Directions
                    </a>
                    <?php endif; ?>

                    <?php if ($row['status'] == 'dispatched' || $row['status'] == 'ready'): ?>
                        <a href="?status=out_for_delivery&order_id=<?php echo $order_id; ?>" class="btn btn-start">
                            🚚 Start Delivery
                        </a>
                    <?php elseif ($row['status'] == 'out_for_delivery'): ?>
                        <a href="?status=delivered&order_id=<?php echo $order_id; ?>" class="btn btn-delivered">
                            ✅ Mark Delivered
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">
                <h3>📦 No Active Deliveries</h3>
                <p>Wait for the admin to assign you an order.</p>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-title">Recently Completed</div>
            <?php if (mysqli_num_rows($completed) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($completed)): ?>
                <div class="delivery-card" style="opacity: 0.7;">
                    <h3>Order #<?php echo $row['order_id']; ?> - <?php echo $row['customer_name']; ?></h3>
                    <div class="info">📞 <?php echo $row['phone']; ?> | 💰 KSh <?php echo number_format($row['total'], 2); ?></div>
                    <div class="info" style="color: #4CAF50; font-weight: bold;">✅ Delivered</div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty">
                    <p>No completed deliveries yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>