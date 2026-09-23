<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

$message = '';

// log production
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['log_production'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $notes = clean($_POST['notes']);
    $produced_by = $_SESSION['user_id'];
    
    // insert production log
    $sql = "INSERT INTO production_log (product_id, quantity_produced, produced_by, production_date, notes) 
            VALUES ($product_id, $quantity, $produced_by, CURDATE(), '$notes')";
    
    if (mysqli_query($conn, $sql)) {
        $production_id = mysqli_insert_id($conn);
        
        // deduct materials based on recipe
        $sql = "SELECT * FROM recipes WHERE product_id = $product_id";
        $recipes = mysqli_query($conn, $sql);
        
        while ($recipe = mysqli_fetch_assoc($recipes)) {
            $material_id = $recipe['material_id'];
            $qty_needed = $recipe['quantity_needed'] * $quantity;
            
            // insert production materials used
            $sql = "INSERT INTO production_materials (production_id, material_id, quantity_used) 
                    VALUES ($production_id, $material_id, $qty_needed)";
            mysqli_query($conn, $sql);
            
            // deduct from inventory
            $sql = "UPDATE raw_materials SET quantity = quantity - $qty_needed WHERE material_id = $material_id";
            mysqli_query($conn, $sql);
        }
        
        $message = show_success('Production logged and materials deducted!');
    } else {
        $message = show_error('Error: ' . mysqli_error($conn));
    }
}

// get production history
$sql = "SELECT pl.*, p.name as product_name FROM production_log pl 
        JOIN products p ON pl.product_id = p.product_id 
        ORDER BY pl.production_date DESC LIMIT 20";
$history = mysqli_query($conn, $sql);

$products = mysqli_query($conn, "SELECT * FROM products WHERE category_id IN (1,2,3,4) ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Production Log - Bakery System</title>
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
        
        .form-box {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin-bottom: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 2px solid #d4a574; border-radius: 8px;
        }
        .btn {
            padding: 12px 25px; background: #2E7D32; color: white;
            border: none; border-radius: 8px; cursor: pointer; font-size: 16px;
        }
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th { background: #2E7D32; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="view_inventory.php">📦 Inventory</a>
        <a href="update_stock.php">➕ Update Stock</a>
        <a href="pending_orders.php">📋 Pending Orders</a>
        <a href="production_log.php" class="active">🏭 Production</a>
        <a href="recipes.php">📖 Recipes</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Production</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Production Log</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Production
        </div>
        
        <?php echo $message; ?>
        
        <div class="form-box">
            <h3 style="color: #2E7D32; margin-bottom: 15px;">Log New Production</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Product</label>
                    <select name="product_id" required>
                        <option value="">Select product</option>
                        <?php while ($p = mysqli_fetch_assoc($products)): ?>
                            <option value="<?php echo $p['product_id']; ?>"><?php echo $p['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity Produced</label>
                    <input type="number" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="2" placeholder="Batch details, quality notes..."></textarea>
                </div>
                <button type="submit" name="log_production" class="btn">Log Production</button>
            </form>
        </div>
        
        <h3 style="color: #2E7D32; margin-bottom: 15px;">Recent Production</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($history)): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($row['production_date'])); ?></td>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><?php echo $row['quantity_produced']; ?></td>
                    <td><?php echo $row['notes']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>