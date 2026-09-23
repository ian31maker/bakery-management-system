<?php

include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

// clean inputs safely
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$message = '';

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    $check_sql = "SELECT status FROM orders WHERE order_id = $delete_id";
    $check_result = mysqli_query($conn, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $order_to_delete = mysqli_fetch_assoc($check_result);

        if ($order_to_delete['status'] == 'pending') {
        
            $sql = "DELETE FROM orders WHERE order_id = $delete_id AND status = 'pending'";
            if (mysqli_query($conn, $sql)) {
                $message = show_success("Order #$delete_id deleted.");
            } else {
                $message = show_error('Error deleting order: ' . mysqli_error($conn));
            }
        } else {
            $message = show_error("Order #$delete_id can't be deleted - it's already " . $order_to_delete['status'] . ".");
        }
    } else {
        $message = show_error("Order #$delete_id not found.");
    }
}

$sql = "SELECT o.*, c.full_name as customer_name, d.full_name as driver_name 
        FROM orders o 
        LEFT JOIN users c ON o.customer_id = c.user_id 
        LEFT JOIN users d ON o.driver_id = d.user_id 
        WHERE 1=1";

if ($status_filter) {
    $sql .= " AND o.status = '$status_filter'";
}
if ($search) {
    $sql .= " AND (c.full_name LIKE '%$search%' OR o.order_id LIKE '%$search%')";
}

$sql .= " ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders - Bakery System</title>
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
            display: flex; gap: 15px; flex-wrap: wrap; align-items: end;
        }
        .filters input, .filters select {
            padding: 10px; border: 2px solid #d4a574; border-radius: 8px;
        }
        .btn {
            padding: 10px 20px; background: #8B4513; color: white;
            border: none; border-radius: 8px; cursor: pointer;
        }
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #8B4513; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
        
        .status {
            padding: 5px 12px; border-radius: 15px; font-size: 12px;
            font-weight: bold; text-transform: uppercase;
        }
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-confirmed { background: #e3f2fd; color: #1565c0; }
        .status-baking { background: #fff8e1; color: #f9a825; }
        .status-ready { background: #e8f5e9; color: #2e7d32; }
        .status-dispatched { background: #f3e5f5; color: #7b1fa2; }
        .status-delivered { background: #e0f2f1; color: #00695c; }
        .status-cancelled { background: #ffebee; color: #c62828; }

        /* action button styles */
        .action-btn {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            margin-right: 4px;
            margin-bottom: 2px;
        }
        .btn-verify { background: #4CAF50; }
        .btn-verify:hover { background: #388E3C; }
        .btn-dispatch { background: #FF9800; }
        .btn-dispatch:hover { background: #F57C00; }
        .btn-track { background: #9C27B0; }
        .btn-track:hover { background: #7B1FA2; }
        .btn-view { background: #2196F3; }
        .btn-view:hover { background: #1976D2; }
        .btn-delete { background: #c62828; }
        .btn-delete:hover { background: #8e0000; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="all_orders.php" class="active">📦 All Orders</a>
        <a href="verify_payments.php">💳 Verify Payments</a>
        <a href="dispatch_orders.php">🚚 Dispatch Orders</a>
        <a href="view_reports.php">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="notifications.php">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #8B4513;">
            <small>Breadcrumb:</small><br>
            <small>Home > All Orders</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>All Orders</h1>
        </div>

        <?php echo $message; ?>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > All Orders
        </div>
        
        <div class="filters">
            <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label>Status</label><br>
                    <select name="status">
                        <option value="">All</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="baking" <?php echo $status_filter == 'baking' ? 'selected' : ''; ?>>Baking</option>
                        <option value="ready" <?php echo $status_filter == 'ready' ? 'selected' : ''; ?>>Ready</option>
                        <option value="dispatched" <?php echo $status_filter == 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                        <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label>Search</label><br>
                    <input type="text" name="search" placeholder="Customer name or order ID" value="<?php echo $search; ?>">
                </div>
                <button type="submit" class="btn">Filter</button>
                <a href="all_orders.php" class="btn" style="text-decoration: none; background: #666;">Clear</a>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Driver</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['customer_name']; ?></td>
                    <td><?php echo ucfirst($row['order_type']); ?></td>
                    <td><?php echo format_money($row['total']); ?></td>
                    <td>
                        <span class="status status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo ucfirst($row['payment_status']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td><?php echo $row['driver_name'] ? $row['driver_name'] : 'N/A'; ?></td>
                    <td>
                        <!-- verify payment button -->
                        <?php if ($row['payment_status'] == 'pending' && $row['payment_method'] == 'mpesa'): ?>
                            <a href="verify_payments.php" class="action-btn btn-verify">Verify Payment</a>
                        <?php endif; ?>

                        <!-- dispatch button -->
                        <?php if ($row['status'] == 'ready'): ?>
                            <a href="dispatch_orders.php" class="action-btn btn-dispatch">Dispatch</a>
                        <?php endif; ?>

                        <!-- track button -->
                        <?php if ($row['status'] == 'dispatched'): ?>
                            <a href="dispatch_orders.php" class="action-btn btn-track">Track</a>
                        <?php endif; ?>

                        <!-- delete button - only shown while the order is still unconfirmed -->
                        <?php if ($row['status'] == 'pending'): ?>
                            <a href="all_orders.php?delete=<?php echo $row['order_id']; ?>"
                               class="action-btn btn-delete"
                               onclick="return confirm('Delete unconfirmed order #<?php echo $row['order_id']; ?>? This cannot be undone.');">
                               Delete
                            </a>
                        <?php endif; ?>

                        <!-- view button always visible -->
                        <a href="all_orders.php" class="action-btn btn-view">View</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>