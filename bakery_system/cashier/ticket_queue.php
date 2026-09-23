<?php
include '../includes/functions.php';
require_role(['cashier']);
include '../includes/db_connect.php';

// take new ticket
if (isset($_GET['take'])) {
    $sql = "SELECT MAX(ticket_number) as max_num FROM tickets";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $next_num = ($row['max_num'] ?? 0) + 1;
    
    $sql = "INSERT INTO tickets (ticket_number, status) VALUES ($next_num, 'waiting')";
    mysqli_query($conn, $sql);
    header('Location: ticket_queue.php');
    exit;
}

// call next ticket
if (isset($_GET['serve'])) {
    $id = intval($_GET['serve']);
    $sql = "UPDATE tickets SET status = 'serving', served_at = NOW() WHERE ticket_id = $id";
    mysqli_query($conn, $sql);
    header('Location: ticket_queue.php');
    exit;
}

// get tickets
$sql = "SELECT * FROM tickets ORDER BY ticket_id DESC LIMIT 20";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Queue - Bakery System</title>
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
        
        .now-serving {
            background: #FF9800; color: white; padding: 40px;
            border-radius: 15px; text-align: center; margin-bottom: 30px;
        }
        .now-serving h2 { font-size: 24px; margin-bottom: 10px; }
        .now-serving .number { font-size: 80px; font-weight: bold; }
        
        .actions { margin-bottom: 30px; }
        .btn {
            padding: 15px 30px; margin-right: 15px; background: #1565C0;
            color: white; text-decoration: none; border-radius: 8px;
            font-size: 16px; font-weight: bold; display: inline-block; border: none; cursor: pointer;
        }
        .btn:hover { background: #0D47A1; }
        .btn-take { background: #4CAF50; }
        .btn-take:hover { background: #388E3C; }
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #1565C0; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        .status-waiting { color: #FF9800; font-weight: bold; }
        .status-serving { color: #1565C0; font-weight: bold; }
        .status-done { color: #4CAF50; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="record_sale.php">💰 Record Sale</a>
        <a href="ticket_queue.php" class="active">🎫 Ticket Queue</a>
        <a href="sales_history.php">📋 Sales History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #0D47A1;">
            <small>Breadcrumb:</small><br>
            <small>Home > Ticket Queue</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Walk-in Ticket Queue</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Ticket Queue
        </div>
        
        <?php
        // find currently serving
        $sql_serving = "SELECT * FROM tickets WHERE status = 'serving' ORDER BY ticket_id DESC LIMIT 1";
        $serving_result = mysqli_query($conn, $sql_serving);
        $serving = mysqli_fetch_assoc($serving_result);
        ?>
        
        <div class="now-serving">
            <h2>Now Serving</h2>
            <div class="number"><?php echo $serving ? '#' . $serving['ticket_number'] : '---'; ?></div>
        </div>
        
        <div class="actions">
            <a href="?take=1" class="btn btn-take">🎫 Issue New Ticket</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Status</th>
                    <th>Issued At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['ticket_number']; ?></td>
                    <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo date('H:i', strtotime($row['created_at'])); ?></td>
                    <td>
                        <?php if ($row['status'] == 'waiting'): ?>
                            <a href="?serve=<?php echo $row['ticket_id']; ?>" class="btn" style="padding: 8px 15px; font-size: 14px;">Call Next</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>