<?php
// ============================================
// FILE: customer/track_order.php
// JOB:  Customer tracks their order status
// BULLETPROOF: Handles ANY status value
// ============================================

include '../includes/functions.php';
require_role(['customer']);
include '../includes/db_connect.php';

$customer_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// get the specific order
if ($order_id > 0) {
    $sql = "SELECT o.*, d.full_name as driver_name, d.phone as driver_phone 
            FROM orders o 
            LEFT JOIN users d ON o.driver_id = d.user_id 
            WHERE o.order_id = $order_id AND o.customer_id = $customer_id";
} else {
    $sql = "SELECT o.*, d.full_name as driver_name, d.phone as driver_phone 
            FROM orders o 
            LEFT JOIN users d ON o.driver_id = d.user_id 
            WHERE o.customer_id = $customer_id 
            ORDER BY o.order_id DESC LIMIT 1";
}

$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

// get order items
if ($order) {
    $oid = $order['order_id'];
    $items_sql = "SELECT oi.*, p.name 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.product_id 
                  WHERE oi.order_id = $oid";
    $items = mysqli_query($conn, $items_sql);
}

// ALL possible stages - handles ANY status
$stages = [
    'pending' => ['label' => 'Order Placed', 'icon' => '📝', 'desc' => 'Waiting for payment confirmation', 'color' => '#FFC107'],
    'confirmed' => ['label' => 'Payment Confirmed', 'icon' => '💳', 'desc' => 'Admin confirmed your payment', 'color' => '#2196F3'],
    'baking' => ['label' => 'Baking', 'icon' => '👨‍🍳', 'desc' => 'Baker is preparing your order', 'color' => '#FF9800'],
    'ready' => ['label' => 'Ready', 'icon' => '✅', 'desc' => 'Your order is ready for pickup/delivery', 'color' => '#4CAF50'],
    'dispatched' => ['label' => 'Dispatched', 'icon' => '🚚', 'desc' => 'Driver assigned, preparing for delivery', 'color' => '#9C27B0'],
    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => '🛵', 'desc' => 'Driver is on the way!', 'color' => '#E65100'],
    'delivered' => ['label' => 'Delivered', 'icon' => '🎉', 'desc' => 'Order delivered successfully', 'color' => '#00BCD4'],
];

function get_stage_number($status) {
    $order = ['pending', 'confirmed', 'baking', 'ready', 'dispatched', 'out_for_delivery', 'delivered'];
    $idx = array_search($status, $order);
    return ($idx === false) ? 0 : $idx;
}

// Get stage info - handles unknown status
$raw_status = $order['status'] ?? 'unknown';
$stage_info = $stages[$raw_status] ?? ['label' => 'Status: ' . $raw_status, 'icon' => '❓', 'desc' => 'Status: ' . $raw_status, 'color' => '#999'];
$current_stage = get_stage_number($raw_status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Order - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; }
        h2 { color: #5D4037; margin-bottom: 20px; }

        .order-box {
            background: white; padding: 25px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-box h3 { color: #5D4037; margin-bottom: 15px; }

        .detail-row {
            display: flex; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #eee;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #666; }
        .detail-value { color: #333; font-weight: bold; }

        .progress-container {
            background: white; padding: 25px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .progress-bar {
            display: flex; justify-content: space-between; margin-bottom: 10px;
        }
        .stage {
            text-align: center; flex: 1; position: relative;
        }
        .stage-icon {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 8px; font-size: 18px;
            background: #e0e0e0; color: #999;
        }
        .stage.active .stage-icon {
            background: #8B4513; color: white;
        }
        .stage.completed .stage-icon {
            background: #4CAF50; color: white;
        }
        .stage-label {
            font-size: 11px; color: #999;
        }
        .stage.active .stage-label {
            color: #8B4513; font-weight: bold;
        }
        .stage.completed .stage-label {
            color: #4CAF50;
        }

        .current-status {
            background: #fff8e1; padding: 15px; border-radius: 8px;
            border-left: 4px solid #ff9800; margin-top: 15px;
        }
        .current-status h4 { color: #e65100; margin-bottom: 5px; }
        .current-status p { color: #666; font-size: 14px; }

        .driver-info {
            background: #e3f2fd; padding: 15px; border-radius: 8px;
            border-left: 4px solid #2196F3; margin-top: 15px;
        }
        .driver-info h4 { color: #1565c0; margin-bottom: 5px; }
        .driver-info p { color: #333; font-size: 14px; }

        .raw-status {
            background: #ffebee; padding: 10px; border-radius: 6px;
            margin-top: 10px; font-size: 13px; color: #c62828;
        }

        .empty {
            text-align: center; padding: 40px; color: #666;
            background: white; border-radius: 10px;
        }
        .btn-back {
            display: inline-block; padding: 10px 20px;
            background: #8B4513; color: white; text-decoration: none;
            border-radius: 8px; margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📦 Track Your Order</h2>

        <?php if ($order): ?>

        <div class="order-box">
            <h3>Order #<?php echo $order['order_id']; ?></h3>
            <div class="detail-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value">KSh <?php echo number_format($order['total'], 2); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Order Type</span>
                <span class="detail-value"><?php echo ucfirst($order['order_type']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date</span>
                <span class="detail-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment</span>
                <span class="detail-value"><?php echo ucfirst($order['payment_status']); ?></span>
            </div>
        </div>

        <div class="progress-container">
            <h3 style="color: #5D4037; margin-bottom: 20px;">Order Progress</h3>
            <div class="progress-bar">
                <?php 
                $stage_keys = array_keys($stages);
                foreach ($stage_keys as $i => $key): 
                    $s = $stages[$key];
                    $class = '';
                    if ($i < $current_stage) $class = 'completed';
                    elseif ($i == $current_stage) $class = 'active';
                ?>
                <div class="stage <?php echo $class; ?>">
                    <div class="stage-icon"><?php echo $s['icon']; ?></div>
                    <div class="stage-label"><?php echo $s['label']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="current-status">
                <h4>Current Status: <?php echo $stage_info['label']; ?></h4>
                <p><?php echo $stage_info['desc']; ?></p>
            </div>

            <?php if ($order['driver_name'] && in_array($raw_status, ['dispatched', 'out_for_delivery'])): ?>
            <div class="driver-info">
                <h4>🚚 Your Driver</h4>
                <p><strong>Name:</strong> <?php echo $order['driver_name']; ?></p>
                <p><strong>Phone:</strong> <?php echo $order['driver_phone']; ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="order-box">
            <h3>Order Items</h3>
            <?php while ($item = mysqli_fetch_assoc($items)): ?>
            <div class="detail-row">
                <span class="detail-label"><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                <span class="detail-value">KSh <?php echo number_format($item['subtotal'], 2); ?></span>
            </div>
            <?php endwhile; ?>
        </div>

        <?php else: ?>
        <div class="empty">
            <h3>No order found</h3>
            <p>You don't have any orders yet.</p>
        </div>
        <?php endif; ?>

        <a href="order_history.php" class="btn-back">← Back to Orders</a>
    </div>
</body>
</html>