<?php
// ============================================
// FILE: admin/dashboard.php
// JOB:  Admin main dashboard with stats, charts, and notification bell
// ============================================

include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

$admin_name = $_SESSION['full_name'];
$admin_id = $_SESSION['user_id'];

// get total sales
$sql = "SELECT COALESCE(SUM(total), 0) as total FROM sales";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_sales = $row['total'];

// get total orders
$sql = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_orders = $row['total'];

// get total expenses
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_expenses = $row['total'];

// calculate profit
$profit = $total_sales - $total_expenses;

// get unread notifications
$sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = $admin_id AND is_read = 0";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$unread_notif = $row['total'];

// count pending M-Pesa payments
$pending_sql = "SELECT COUNT(*) as total FROM orders WHERE payment_method = 'mpesa' AND payment_status = 'pending'";
$pending_result = mysqli_query($conn, $pending_sql);
$pending_row = mysqli_fetch_assoc($pending_result);
$pending_payments = $pending_row['total'] ?? 0;

// count ready orders waiting for driver
$ready_sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'ready' AND (driver_id IS NULL OR driver_id = 0)";
$ready_result = mysqli_query($conn, $ready_sql);
$ready_row = mysqli_fetch_assoc($ready_result);
$ready_orders = $ready_row['total'] ?? 0;

// count dispatched orders
$dispatched_sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'dispatched'";
$dispatched_result = mysqli_query($conn, $dispatched_sql);
$dispatched_row = mysqli_fetch_assoc($dispatched_result);
$dispatched_orders = $dispatched_row['total'] ?? 0;

// get daily sales for chart (last 7 days)
$sql = "SELECT DATE(sale_date) as date, SUM(total) as total 
        FROM sales 
        WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(sale_date)
        ORDER BY date";
$result = mysqli_query($conn, $sql);
$daily_labels = [];
$daily_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $daily_labels[] = date('M d', strtotime($row['date']));
    $daily_data[] = $row['total'];
}

// get top selling products
$sql = "SELECT p.name, SUM(si.quantity) as qty 
        FROM sale_items si 
        JOIN products p ON si.product_id = p.product_id 
        GROUP BY si.product_id 
        ORDER BY qty DESC 
        LIMIT 5";
$result = mysqli_query($conn, $sql);
$product_labels = [];
$product_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $product_labels[] = $row['name'];
    $product_data[] = $row['qty'];
}

// get order status counts
$sql = "SELECT status, COUNT(*) as total FROM orders GROUP BY status";
$result = mysqli_query($conn, $sql);
$status_labels = [];
$status_data = [];
$status_colors = [];
$color_map = [
    'pending' => '#FFC107',
    'confirmed' => '#2196F3',
    'baking' => '#FF9800',
    'ready' => '#4CAF50',
    'dispatched' => '#9C27B0',
    'out_for_delivery' => '#E65100',
    'delivered' => '#00BCD4',
    'cancelled' => '#F44336'
];
while ($row = mysqli_fetch_assoc($result)) {
    $status_labels[] = ucfirst(str_replace('_', ' ', $row['status']));
    $status_data[] = $row['total'];
    $status_colors[] = $color_map[$row['status']] ?? '#999';
}

// get recent notifications for dropdown
$notif_sql = "SELECT * FROM notifications WHERE user_id = $admin_id ORDER BY created_at DESC LIMIT 8";
$notif_result = mysqli_query($conn, $notif_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5e6d3;
            min-height: 100vh;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: #5D4037;
            color: white;
            padding: 20px;
            overflow-y: auto;
        }
        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
            font-size: 22px;
        }
        .sidebar a {
            display: block;
            color: #f5e6d3;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: 0.3s;
            position: relative;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #8B4513;
            color: white;
        }
        .sidebar .logout {
            margin-top: 30px;
            background: #c62828;
            text-align: center;
        }
        .sidebar .badge-count {
            background: #ff9800;
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            margin-left: 8px;
        }
        .main {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #5D4037;
        }
        /* notification bell with dropdown */
        .notif-wrapper {
            position: relative;
        }
        .notif-bell {
            position: relative;
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
            user-select: none;
            display: inline-block;
        }
        .notif-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #c62828;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .notif-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
            border: 1px solid #eee;
        }
        .notif-dropdown.show {
            display: block;
        }
        .notif-dropdown h4 {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #5D4037;
            font-size: 14px;
            margin: 0;
        }
        .notif-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: #333;
            font-size: 13px;
            transition: 0.2s;
        }
        .notif-item:hover {
            background: #fafafa;
        }
        .notif-item.unread {
            background: #fff8e1;
            border-left: 3px solid #ff9800;
        }
        .notif-item strong {
            color: #5D4037;
        }
        .notif-item small {
            color: #999;
            font-size: 11px;
        }
        .notif-dropdown .view-all {
            padding: 12px;
            text-align: center;
            color: #8B4513;
            text-decoration: none;
            display: block;
            font-size: 13px;
            border-top: 1px solid #eee;
            font-weight: bold;
        }
        .notif-dropdown .view-all:hover {
            background: #fafafa;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h3 {
            color: #8B4513;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .card .number {
            font-size: 32px;
            font-weight: bold;
            color: #5D4037;
        }
        .charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chart-box h3 {
            color: #5D4037;
            margin-bottom: 20px;
        }
        .quick-links {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .quick-links h3 {
            color: #5D4037;
            margin-bottom: 15px;
        }
        .quick-links a {
            display: inline-block;
            padding: 10px 20px;
            background: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: 0.3s;
        }
        .quick-links a:hover {
            background: #6d360f;
        }
        .quick-link-card {
            display: block;
            background: #fafafa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-decoration: none;
            color: #333;
            border-left: 4px solid #8B4513;
            transition: 0.3s;
        }
        .quick-link-card:hover {
            background: #f5e6d3;
            transform: translateX(5px);
        }
        .quick-link-card h4 {
            color: #5D4037;
            margin-bottom: 5px;
        }
        .quick-link-card p {
            color: #666;
            font-size: 13px;
        }
        .quick-link-card .badge {
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            float: right;
        }
        .breadcrumb {
            margin-bottom: 20px;
            color: #8B4513;
        }
        .breadcrumb a {
            color: #8B4513;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="all_orders.php">📦 All Orders</a>
        <a href="verify_payments.php">
            💳 Verify Payments
            <?php if($pending_payments > 0): ?><span class="badge-count"><?php echo $pending_payments; ?></span><?php endif; ?>
        </a>
        <a href="dispatch_orders.php">
            🚚 Dispatch Orders
            <?php if($ready_orders > 0): ?><span class="badge-count"><?php echo $ready_orders; ?></span><?php endif; ?>
        </a>
        <a href="view_reports.php">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>Welcome, <?php echo $admin_name; ?>!</h1>
            <div class="notif-wrapper">
                <div class="notif-bell" onclick="toggleNotifs(event)">
                    🔔
                    <?php if ($unread_notif > 0): ?>
                    <span class="notif-badge"><?php echo $unread_notif; ?></span>
                    <?php endif; ?>
                </div>
                <div class="notif-dropdown" id="notifDropdown">
                    <h4>🔔 Notifications</h4>
                    <?php if (mysqli_num_rows($notif_result) > 0): ?>
                        <?php while ($n = mysqli_fetch_assoc($notif_result)): ?>
                        <!-- clicking goes to notifications.php which redirects to actual issue -->
                        <a href="notifications.php?notif_id=<?php echo $n['notif_id']; ?>" 
                           class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                            <strong><?php echo $n['title']; ?></strong><br>
                            <?php echo $n['message']; ?><br>
                            <small><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></small>
                        </a>
                        <?php endwhile; ?>
                        <a href="notifications.php" class="view-all">View All Notifications →</a>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #999;">No notifications</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Dashboard
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Sales</h3>
                <div class="number"><?php echo format_money($total_sales); ?></div>
            </div>
            <div class="card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $total_orders; ?></div>
            </div>
            <div class="card">
                <h3>Total Expenses</h3>
                <div class="number"><?php echo format_money($total_expenses); ?></div>
            </div>
            <div class="card">
                <h3>Profit</h3>
                <div class="number" style="color: <?php echo $profit >= 0 ? '#4CAF50' : '#F44336'; ?>">
                    <?php echo format_money($profit); ?>
                </div>
            </div>
            <div class="card" style="border-top: 4px solid #ff9800;">
                <h3>Pending M-Pesa</h3>
                <div class="number" style="color: #ff9800;"><?php echo $pending_payments; ?></div>
            </div>
            <div class="card" style="border-top: 4px solid #4CAF50;">
                <h3>Ready for Dispatch</h3>
                <div class="number" style="color: #4CAF50;"><?php echo $ready_orders; ?></div>
            </div>
            <div class="card" style="border-top: 4px solid #9C27B0;">
                <h3>Out for Delivery</h3>
                <div class="number" style="color: #9C27B0;"><?php echo $dispatched_orders; ?></div>
            </div>
        </div>

        <div class="charts">
            <div class="chart-box">
                <h3>Daily Sales (Last 7 Days)</h3>
                <canvas id="dailyChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>Top Selling Products</h3>
                <canvas id="productChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>Order Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="quick-links">
            <h3>⚡ Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                <a href="verify_payments.php" class="quick-link-card">
                    <span class="badge"><?php echo $pending_payments; ?></span>
                    <h4>💳 Verify M-Pesa Payments</h4>
                    <p>Check which customers paid and confirm orders before the Baker sees them.</p>
                </a>
                <a href="dispatch_orders.php" class="quick-link-card">
                    <span class="badge" style="background: #4CAF50;"><?php echo $ready_orders; ?></span>
                    <h4>🚚 Dispatch Orders</h4>
                    <p>Assign drivers to orders that the Baker marked as "Ready".</p>
                </a>
                <a href="all_orders.php" class="quick-link-card">
                    <h4>📦 View All Orders</h4>
                    <p>See every order, filter by status, and manage them.</p>
                </a>
                <a href="manage_users.php" class="quick-link-card">
                    <h4>👥 Manage Staff</h4>
                    <p>Add or edit Bakers, Drivers, Cashiers, and Customers.</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleNotifs(event) {
            event.stopPropagation();
            var dropdown = document.getElementById('notifDropdown');
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            var dropdown = document.getElementById('notifDropdown');
            var bell = document.querySelector('.notif-bell');
            if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($daily_labels); ?>,
                datasets: [{
                    label: 'Sales (KSh)',
                    data: <?php echo json_encode($daily_data); ?>,
                    backgroundColor: '#8B4513'
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        new Chart(document.getElementById('productChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($product_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($product_data); ?>,
                    backgroundColor: ['#8B4513', '#D4A574', '#5D4037', '#A0522D', '#CD853F']
                }]
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: <?php echo json_encode($status_colors); ?>
                }]
            }
        });
    </script>
</body>
</html>