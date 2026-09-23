<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

// get all raw materials
$sql = "SELECT m.*, s.name as supplier_name FROM raw_materials m 
        LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id 
        ORDER BY m.name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - Bakery System</title>
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
        
        .alert-box {
            background: #fff3e0; border-left: 4px solid #FF9800;
            padding: 15px; margin-bottom: 20px; border-radius: 5px;
        }
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #2E7D32; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
        .low { color: #c62828; font-weight: bold; }
        .ok { color: #2E7D32; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="view_inventory.php" class="active">📦 Inventory</a>
        <a href="update_stock.php">➕ Update Stock</a>
        <a href="pending_orders.php">📋 Pending Orders</a>
        <a href="production_log.php">🏭 Production</a>
        <a href="recipes.php">📖 Recipes</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Inventory</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Raw Materials Inventory</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Inventory
        </div>
        
        <div class="alert-box">
            ⚠️ Items in <strong>red</strong> are below minimum stock level and need restocking.
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Min Stock</th>
                    <th>Status</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $is_low = $row['quantity'] <= $row['min_stock'];
                ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td class="<?php echo $is_low ? 'low' : 'ok'; ?>">
                        <?php echo number_format($row['quantity'], 2); ?>
                    </td>
                    <td><?php echo $row['unit']; ?></td>
                    <td><?php echo number_format($row['min_stock'], 2); ?></td>
                    <td class="<?php echo $is_low ? 'low' : 'ok'; ?>">
                        <?php echo $is_low ? '🔴 LOW STOCK' : '✅ OK'; ?>
                    </td>
                    <td><?php echo $row['supplier_name'] ? $row['supplier_name'] : 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>