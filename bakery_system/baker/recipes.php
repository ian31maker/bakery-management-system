<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

// get all recipes with product and material details
$sql = "SELECT r.*, p.name as product_name, p.price, m.name as material_name, m.unit 
        FROM recipes r 
        JOIN products p ON r.product_id = p.product_id 
        JOIN raw_materials m ON r.material_id = m.material_id 
        ORDER BY p.name, m.name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipes - Bakery System</title>
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
        
        .recipe-card {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .recipe-card h3 { color: #2E7D32; margin-bottom: 10px; }
        .ingredient {
            padding: 8px 0; border-bottom: 1px solid #f5e6d3;
            display: flex; justify-content: space-between;
        }
        .price-tag {
            background: #e8f5e9; color: #2E7D32; padding: 5px 15px;
            border-radius: 15px; font-weight: bold; display: inline-block;
        }
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
        <a href="recipes.php" class="active">📖 Recipes</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Recipes</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Product Recipes</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Recipes
        </div>
        
        <?php
        $current_product = '';
        while ($row = mysqli_fetch_assoc($result)):
            if ($current_product != $row['product_name']):
                if ($current_product != '') echo '</div>';
                $current_product = $row['product_name'];
        ?>
        <div class="recipe-card">
            <h3><?php echo $row['product_name']; ?> <span class="price-tag"><?php echo format_money($row['price']); ?></span></h3>
        <?php endif; ?>
            <div class="ingredient">
                <span><?php echo $row['material_name']; ?></span>
                <span><?php echo $row['quantity_needed'] . ' ' . $row['unit']; ?></span>
            </div>
        <?php endwhile; ?>
        <?php if ($current_product != '') echo '</div>'; ?>
    </div>
</body>
</html>