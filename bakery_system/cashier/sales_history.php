<?php
include '../includes/functions.php';
require_role(['cashier']);
include '../includes/db_connect.php';

// get sales by this cashier
$cashier_id = $_SESSION['user_id'];
$sql = "SELECT s.*, t.ticket_number FROM sales s 
        LEFT JOIN tickets t ON s.ticket_id = t.ticket_id 
        WHERE s.cashier_id = $cashier_id 
        ORDER BY s.sale_date DESC LIMIT 50";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales History - Bakery System</title>
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
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #1565C0; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
        .btn {
            padding: 8px 15px; background: #8B4513; color: white;
            text-decoration: none; border-radius: 6px; font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="record_sale.php">💰 Record Sale</a>
        <a href="ticket_queue.php">🎫 Ticket Queue</a>
        <a href="sales_history.php" class="active">📋 Sales History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #0D47A1;">
            <small>Breadcrumb:</small><br>
            <small>Home > Sales History</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>My Sales History</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Sales History
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Sale #</th>
                    <th>Ticket</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['sale_id']; ?></td>
                    <td><?php echo $row['ticket_number'] ? '#' . $row['ticket_number'] : 'Walk-in'; ?></td>
                    <td><?php echo format_money($row['total']); ?></td>
                    <td><?php echo ucfirst($row['payment_method']); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($row['sale_date'])); ?></td>
                    <td>
                        <a href="print_receipt.php?sale_id=<?php echo $row['sale_id']; ?>" target="_blank" class="btn">🖨️ Receipt</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>