<?php

include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

$admin_id = $_SESSION['user_id'];

// delete single notification
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM notifications WHERE notif_id = $delete_id AND user_id = $admin_id";
    mysqli_query($conn, $sql);
    header('Location: notifications.php');
    exit;
}

// delete all read notifications
if (isset($_GET['delete_all_read'])) {
    $sql = "DELETE FROM notifications WHERE user_id = $admin_id AND is_read = 1";
    mysqli_query($conn, $sql);
    header('Location: notifications.php');
    exit;
}

// mark single as read and redirect to actual issue page
if (isset($_GET['notif_id'])) {
    $notif_id = intval($_GET['notif_id']);
    
    // mark as read
    $sql = "UPDATE notifications SET is_read = 1 WHERE notif_id = $notif_id AND user_id = $admin_id";
    mysqli_query($conn, $sql);
    
    // get the link and redirect to actual issue
    $link_sql = "SELECT link FROM notifications WHERE notif_id = $notif_id AND user_id = $admin_id";
    $link_result = mysqli_query($conn, $link_sql);
    $link_row = mysqli_fetch_assoc($link_result);
    
    if ($link_row && !empty($link_row['link'])) {
        header('Location: ' . $link_row['link']);
    } else {
        header('Location: notifications.php');
    }
    exit;
}

// mark all as read
if (isset($_GET['mark_all_read'])) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = $admin_id AND is_read = 0";
    mysqli_query($conn, $sql);
    header('Location: notifications.php');
    exit;
}

// get all notifications
$sql = "SELECT * FROM notifications WHERE user_id = $admin_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// count read notifications (for delete all button)
$read_sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = $admin_id AND is_read = 1";
$read_result = mysqli_query($conn, $read_sql);
$read_row = mysqli_fetch_assoc($read_result);
$read_count = $read_row['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Bakery System</title>
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

        .notif-card {
            background: white; padding: 15px 20px; border-radius: 8px;
            margin-bottom: 10px; box-shadow: 0 1px 5px rgba(0,0,0,0.08);
            display: flex; justify-content: space-between; align-items: center;
        }
        .notif-card.unread {
            border-left: 4px solid #ff9800; background: #fff8e1;
        }
        .notif-card a { color: #333; text-decoration: none; }
        .notif-card a:hover { color: #8B4513; }
        .notif-card strong { color: #5D4037; }
        .notif-card small { color: #999; font-size: 12px; }

        .btn-group {
            display: flex; gap: 8px;
        }
        .btn-mark {
            background: #8B4513; color: white; padding: 6px 12px;
            border: none; border-radius: 6px; cursor: pointer;
            text-decoration: none; font-size: 12px;
        }
        .btn-mark:hover { background: #6d360f; }
        .btn-delete {
            background: #c62828; color: white; padding: 6px 12px;
            border: none; border-radius: 6px; cursor: pointer;
            text-decoration: none; font-size: 12px;
        }
        .btn-delete:hover { background: #b71c1c; }
        .btn-mark-all {
            background: #4CAF50; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            text-decoration: none; font-size: 14px; font-weight: bold;
        }
        .btn-delete-all {
            background: #c62828; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            text-decoration: none; font-size: 14px; font-weight: bold;
        }
        .empty {
            text-align: center; padding: 40px; color: #666;
            background: white; border-radius: 10px;
        }
        .action-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
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
        <a href="dispatch_orders.php">🚚 Dispatch Orders</a>
        <a href="view_reports.php">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="notifications.php" class="active">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>🔔 All Notifications</h1>
        </div>

        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Notifications
        </div>

        <div class="action-bar">
            <div>
                <a href="?mark_all_read=1" class="btn-mark-all">✓ Mark All as Read</a>
                <?php if ($read_count > 0): ?>
                <a href="?delete_all_read=1" class="btn-delete-all" onclick="return confirm('Delete all <?php echo $read_count; ?> read notifications?')">🗑️ Delete All Read (<?php echo $read_count; ?>)</a>
                <?php endif; ?>
            </div>
            <div style="color: #666; font-size: 14px;">
                <?php echo mysqli_num_rows($result); ?> total notifications
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="notif-card <?php echo $row['is_read'] ? '' : 'unread'; ?>">
                <div>
                    <a href="?notif_id=<?php echo $row['notif_id']; ?>">
                        <strong><?php echo $row['title']; ?></strong> — 
                        <?php echo $row['message']; ?><br>
                        <small><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                    </a>
                </div>
                <div class="btn-group">
                    <?php if (!$row['is_read']): ?>
                    <a href="?notif_id=<?php echo $row['notif_id']; ?>" class="btn-mark">Mark Read</a>
                    <?php endif; ?>
                    <a href="?delete_id=<?php echo $row['notif_id']; ?>" class="btn-delete" onclick="return confirm('Delete this notification?')">Delete</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">
                <h3>No notifications</h3>
                <p>You're all caught up!</p>
            </div>
        <?php endif; ?>

        <p style="margin-top: 20px;">
            <a href="dashboard.php" style="color: #8B4513;">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>