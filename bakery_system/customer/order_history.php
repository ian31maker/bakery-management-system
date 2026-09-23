<?php
include '../includes/functions.php';
include '../includes/db_connect.php';

if (!is_logged_in() || get_role() != 'customer') {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE customer_id = $customer_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .header {
            background: #8B4513; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        
        .order-card {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-card h3 { color: #5D4037; margin-bottom: 10px; }
        .order-info { color: #666; margin-bottom: 10px; }
        .status {
            display: inline-block; padding: 5px 15px; border-radius: 15px;
            font-size: 12px; font-weight: bold; text-transform: uppercase;
        }
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-delivered { background: #e8f5e9; color: #2e7d32; }
        .status-ready { background: #e3f2fd; color: #1565c0; }
        .btn {
            padding: 8px 20px; background: #8B4513; color: white;
            text-decoration: none; border-radius: 6px; font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="menu.php">🥖 Bakery System</a>
        <div>
            <a href="menu.php">🍞 Menu</a> | 
            <a href="cart.php">🛒 Cart</a> | 
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h2 style="color: #5D4037; margin-bottom: 20px;">My Orders</h2>
        
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="order-card">
            <h3>Order #<?php echo $row['order_id']; ?> 
                <span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
            </h3>
            <div class="order-info">
                📅 <?php echo date('M d, Y', strtotime($row['created_at'])); ?> | 
                💰 <?php echo format_money($row['total']); ?> | 
                <?php echo ucfirst($row['order_type']); ?>
            </div>
            <a href="track_order.php?order_id=<?php echo $row['order_id']; ?>" class="btn">Track Order</a>
            
            <?php if ($row['status'] == 'delivered' && !review_exists($conn, $row['order_id'], $customer_id)): ?>
            <a href="leave_review.php?order_id=<?php echo $row['order_id']; ?>" class="btn" style="background: #4CAF50;">Leave Review</a>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
function review_exists($conn, $order_id, $customer_id) {
    $sql = "SELECT * FROM reviews WHERE order_id = $order_id AND customer_id = $customer_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}
?>