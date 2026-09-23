<?php
include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

$message = '';

// update price
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_price'])) {
    $product_id = intval($_POST['product_id']);
    $new_price = floatval($_POST['new_price']);
    
    $sql = "UPDATE products SET price = $new_price WHERE product_id = $product_id";
    if (mysqli_query($conn, $sql)) {
        $message = show_success('Price updated successfully!');
    } else {
        $message = show_error('Error updating price');
    }
}

// get all products
$sql = "SELECT p.*, c.name as category_name FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        ORDER BY c.name, p.name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Prices - Bakery System</title>
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
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #8B4513; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
        
        .price-input {
            width: 100px; padding: 8px; border: 2px solid #d4a574;
            border-radius: 6px;
        }
        .btn {
            padding: 8px 15px; background: #8B4513; color: white;
            border: none; border-radius: 6px; cursor: pointer;
        }
        .btn:hover { background: #6d360f; }
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
        <a href="set_prices.php" class="active">💰 Set Prices</a>
        <a href="notifications.php">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #8B4513;">
            <small>Breadcrumb:</small><br>
            <small>Home > Set Prices</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Set Product Prices</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Set Prices
        </div>
        
        <?php echo $message; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Current Price</th>
                    <th>New Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['category_name']; ?></td>
                    <td><?php echo format_money($row['price']); ?></td>
                    <td>
                        <form method="POST" action="" style="display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="number" step="0.01" name="new_price" class="price-input" value="<?php echo $row['price']; ?>" required>
                    </td>
                    <td>
                            <button type="submit" name="update_price" class="btn">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>