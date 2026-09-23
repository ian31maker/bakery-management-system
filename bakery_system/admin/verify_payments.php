<?php
include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';
include '../includes/mpesa_helper.php';

$message = '';
$api_result = null;

// API CHECK: Ask Safaricom if payment happened
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_api'])) {
    $order_id = intval($_POST['order_id']);

    $sql = "SELECT mpesa_checkout_id FROM orders WHERE order_id = $order_id AND payment_method = 'mpesa' AND payment_status = 'pending'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row && !empty($row['mpesa_checkout_id'])) {
        $checkout_id = $row['mpesa_checkout_id'];
        $api_result = check_transaction_status($checkout_id);

        if ($api_result['success'] && $api_result['paid']) {
            $sql = "UPDATE orders
                    SET payment_status = 'paid',
                        status = 'confirmed',
                        mpesa_result = 'Confirmed by M-Pesa API'
                    WHERE order_id = $order_id AND payment_status = 'pending'";

            if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
                $notif_link = 'all_orders.php?order_id=' . $order_id;
                $sql = "INSERT INTO notifications (user_id, title, message, link)
                        VALUES (1, 'New Paid Order', 'Order #$order_id confirmed and ready for baking', '$notif_link')";
                mysqli_query($conn, $sql);
                $message = show_success('✅ API Check: Order #' . $order_id . ' is PAID! Order confirmed. (' . $api_result['message'] . ')');
            } else {
                $message = show_error('Order could not be confirmed. It may already have been processed.');
            }
        } else {
            $message = show_error('❌ API Check: Order #' . $order_id . ' NOT paid. The order remains pending. (' . $api_result['message'] . ')');
        }
    } else {
        $message = show_error('No checkout ID found for this pending M-Pesa order.');
    }
}

// MANUAL CONFIRM: admin confirms by hand, no receipt code required
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['manual_confirm'])) {
    $order_id = intval($_POST['order_id']);

    $sql = "UPDATE orders
            SET payment_status = 'paid',
                status = 'confirmed',
                mpesa_result = 'Manually confirmed by admin'
            WHERE order_id = $order_id AND payment_method = 'mpesa' AND payment_status = 'pending'";

    if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
        $notif_link = 'all_orders.php?order_id=' . $order_id;
        $sql = "INSERT INTO notifications (user_id, title, message, link)
                VALUES (1, 'New Paid Order', 'Order #$order_id manually confirmed and ready for baking', '$notif_link')";
        mysqli_query($conn, $sql);
        $message = show_success('✅ Order #' . $order_id . ' manually confirmed.');
    } else {
        $message = show_error('Order could not be confirmed. It may already have been processed.');
    }
}

// REMOVE: delete an order that never got paid. Only allowed while
// payment_status is still 'pending' - the same guard the delete button
// on All Orders uses - so a paid order can never be removed this way.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_unpaid'])) {
    $order_id = intval($_POST['order_id']);

    $sql = "DELETE FROM orders WHERE order_id = $order_id AND payment_method = 'mpesa' AND payment_status = 'pending'";
    if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
        $message = show_success('Order #' . $order_id . ' removed.');
    } else {
        $message = show_error('Order could not be removed. It may already have been processed.');
    }
}

// get all pending M-Pesa orders
$sql = "SELECT o.*, u.full_name, u.phone
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        WHERE o.payment_method = 'mpesa' AND o.payment_status = 'pending'
        ORDER BY o.order_id DESC";
$orders = mysqli_query($conn, $sql);

// get confirmed orders (for reference)
$sql2 = "SELECT o.*, u.full_name, u.phone
          FROM orders o
          JOIN users u ON o.customer_id = u.user_id
          WHERE o.payment_method = 'mpesa' AND o.payment_status = 'paid'
          ORDER BY o.order_id DESC LIMIT 10";
$confirmed = mysqli_query($conn, $sql2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Payments - Bakery System</title>
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

        .info-box {
            background: #fff8e1; padding: 15px; border-radius: 8px;
            margin-bottom: 20px; border-left: 4px solid #ff9800;
        }
        .info-box p { font-size: 14px; color: #333; margin-bottom: 8px; }
        .info-box ol { margin-left: 20px; font-size: 14px; }

        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th {
            background: #8B4513; color: white; padding: 15px;
            text-align: left;
        }
        td {
            padding: 15px; border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        tr:hover { background: #fafafa; }

        .btn-check {
            background: #2196F3; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            font-weight: bold; font-size: 13px; margin-right: 8px;
        }
        .btn-check:hover { background: #1976D2; }

        .btn-confirm {
            background: #4caf50; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            font-weight: bold; font-size: 13px;
        }
        .btn-confirm:hover { background: #388e3c; }

        .btn-remove {
            background: #c62828; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            font-weight: bold; font-size: 13px;
        }
        .btn-remove:hover { background: #8e0000; }

        .badge {
            display: inline-block; padding: 5px 12px;
            border-radius: 12px; font-size: 12px; font-weight: bold;
        }
        .badge-pending { background: #fff3e0; color: #e65100; }
        .badge-paid { background: #e8f5e9; color: #2e7d32; }

        .empty {
            text-align: center; padding: 40px; color: #666;
            background: white; border-radius: 10px;
        }

        .section { margin-bottom: 30px; }
        .section-title {
            color: #8B4513; font-size: 18px; margin-bottom: 15px;
            border-bottom: 2px solid #d4a574; padding-bottom: 8px;
        }

        .api-result {
            background: #e3f2fd; padding: 10px; border-radius: 6px;
            margin-top: 10px; font-size: 13px; color: #1565c0;
            border-left: 3px solid #2196F3;
        }
        .api-result.paid {
            background: #e8f5e9; color: #2e7d32;
            border-left: 3px solid #4CAF50;
        }
        .api-result.failed {
            background: #ffebee; color: #c62828;
            border-left: 3px solid #F44336;
        }

        .btn-group {
            display: flex; gap: 8px; flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="all_orders.php">📦 All Orders</a>
        <a href="verify_payments.php" class="active">💳 Verify Payments</a>
        <a href="dispatch_orders.php">🚚 Dispatch Orders</a>
        <a href="view_reports.php">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="notifications.php">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>💳 Verify M-Pesa Payments</h1>
        </div>

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Verify Payments
        </div>

        

        <?php echo $message; ?>

        <div class="section">
            <div class="section-title">Pending Payments (Need Your Confirmation)</div>
            <?php if (mysqli_num_rows($orders) > 0): ?>
            <table>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td>KSh <?php echo number_format($row['total'], 2); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td><span class="badge badge-pending">Pending</span></td>
                    <td>
                        <div class="btn-group">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="check_api" class="btn-check">🔍 Check via API</button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Manually confirm order #<?php echo $row['order_id']; ?> as paid?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="manual_confirm" class="btn-confirm">Confirm</button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Remove order #<?php echo $row['order_id']; ?>? This cannot be undone.');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="remove_unpaid" class="btn-remove">Remove</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php else: ?>
            <div class="empty">
                <h3>No pending payments</h3>
                <p>All M-Pesa orders are confirmed or no orders yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-title">Recently Confirmed</div>
            <?php if (mysqli_num_rows($confirmed) > 0): ?>
            <table>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($confirmed)): ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                    <td>KSh <?php echo number_format($row['total'], 2); ?></td>
                    <td><span class="badge badge-paid">Paid</span></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php else: ?>
            <div class="empty">
                <p>No confirmed payments yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <p style="margin-top: 20px;">
            <a href="dashboard.php" style="color: #8B4513;">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>