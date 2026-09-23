<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// get orders for this month
$sql = "SELECT order_id, customer_id, order_type, status, pickup_date, delivery_date 
        FROM orders 
        WHERE (MONTH(pickup_date) = $month OR MONTH(delivery_date) = $month)
        AND (YEAR(pickup_date) = $year OR YEAR(delivery_date) = $year)
        AND status IN ('confirmed', 'baking', 'ready')
        ORDER BY pickup_date, delivery_date";
$result = mysqli_query($conn, $sql);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['pickup_date'] ? $row['pickup_date'] : ($row['delivery_date'] ? $row['delivery_date'] : null);
if ($date) {
    $events[$date][] = $row;
}
    $events[$date][] = $row;
}

// calendar logic
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$start_day = date('w', $first_day);
$month_name = date('F Y', $first_day);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar - Bakery System</title>
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
        
        .calendar-nav {
            background: white; padding: 15px; border-radius: 10px;
            margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;
        }
        .btn {
            padding: 10px 20px; background: #2E7D32; color: white;
            border: none; border-radius: 8px; cursor: pointer; text-decoration: none;
        }
        
        .calendar {
            background: white; border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .calendar-header {
            display: grid; grid-template-columns: repeat(7, 1fr);
            background: #2E7D32; color: white; text-align: center; padding: 15px;
        }
        .calendar-days {
            display: grid; grid-template-columns: repeat(7, 1fr);
        }
        .day {
            min-height: 100px; padding: 10px; border: 1px solid #f5e6d3;
        }
        .day-number { font-weight: bold; color: #2E7D32; margin-bottom: 5px; }
        .event {
            font-size: 11px; padding: 3px 6px; border-radius: 4px;
            margin-bottom: 3px; background: #e8f5e9; color: #2E7D32;
        }
        .empty-day { background: #fafafa; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="view_inventory.php">📦 Inventory</a>
        <a href="update_stock.php">➕ Update Stock</a>
        <a href="pending_orders.php">📋 Pending Orders</a>
        <a href="production_log.php">🏭 Production</a>
        <a href="recipes.php">📖 Recipes</a>
        <a href="calendar.php" class="active">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Calendar</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Baking Schedule</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Calendar
        </div>
        
        <div class="calendar-nav">
            <a href="?month=<?php echo $month - 1 <= 0 ? 12 : $month - 1; ?>&year=<?php echo $month - 1 <= 0 ? $year - 1 : $year; ?>" class="btn">← Previous</a>
            <h2 style="color: #2E7D32;"><?php echo $month_name; ?></h2>
            <a href="?month=<?php echo $month + 1 > 12 ? 1 : $month + 1; ?>&year=<?php echo $month + 1 > 12 ? $year + 1 : $year; ?>" class="btn">Next →</a>
        </div>
        
        <div class="calendar">
            <div class="calendar-header">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div class="calendar-days">
                <?php
                for ($i = 0; $i < $start_day; $i++) {
                    echo '<div class="day empty-day"></div>';
                }
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    echo '<div class="day">';
                    echo '<div class="day-number">' . $day . '</div>';
                    if (isset($events[$date])) {
                        foreach ($events[$date] as $event) {
                            echo '<div class="event">#' . $event['order_id'] . ' - ' . ucfirst($event['status']) . '</div>';
                        }
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>