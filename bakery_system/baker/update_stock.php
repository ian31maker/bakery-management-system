<?php
include '../includes/functions.php';
require_role(['baker']);
include '../includes/db_connect.php';

$message = '';

// add stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_stock'])) {
    $material_id = intval($_POST['material_id']);
    $quantity = floatval($_POST['quantity']);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $unit_cost = floatval($_POST['unit_cost']);
    $notes = clean($_POST['notes']);
    $added_by = $_SESSION['user_id'];
    
    // insert log
    $supplier_sql = $supplier_id ? "'$supplier_id'" : "NULL";
    $sql = "INSERT INTO inventory_log (material_id, supplier_id, quantity_added, unit_cost, date_added, added_by, notes) 
            VALUES ($material_id, $supplier_sql, $quantity, $unit_cost, CURDATE(), $added_by, '$notes')";
    
    if (mysqli_query($conn, $sql)) {
        // update material quantity
        $sql = "UPDATE raw_materials SET quantity = quantity + $quantity WHERE material_id = $material_id";
        mysqli_query($conn, $sql);
        $message = show_success('Stock added successfully!');
    } else {
        $message = show_error('Error: ' . mysqli_error($conn));
    }
}

// get materials and suppliers
$materials = mysqli_query($conn, "SELECT * FROM raw_materials ORDER BY name");
$suppliers = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Stock - Bakery System</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px;
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
        .btn:hover { background: #1B5E20; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="view_inventory.php">📦 Inventory</a>
        <a href="update_stock.php" class="active">➕ Update Stock</a>
        <a href="pending_orders.php">📋 Pending Orders</a>
        <a href="production_log.php">🏭 Production</a>
        <a href="recipes.php">📖 Recipes</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #1B5E20;">
            <small>Breadcrumb:</small><br>
            <small>Home > Update Stock</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Add Stock</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Update Stock
        </div>
        
        <?php echo $message; ?>
        
        <div class="form-box">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Material</label>
                    <select name="material_id" required>
                        <option value="">Select material</option>
                        <?php while ($m = mysqli_fetch_assoc($materials)): ?>
                            <option value="<?php echo $m['material_id']; ?>"><?php echo $m['name']; ?> (Current: <?php echo $m['quantity'] . ' ' . $m['unit']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity Added</label>
                    <input type="number" step="0.01" name="quantity" required>
                </div>
                <div class="form-group">
                    <label>Supplier (optional)</label>
                    <select name="supplier_id">
                        <option value="">Select supplier</option>
                        <?php while ($s = mysqli_fetch_assoc($suppliers)): ?>
                            <option value="<?php echo $s['supplier_id']; ?>"><?php echo $s['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Unit Cost (KSh)</label>
                    <input type="number" step="0.01" name="unit_cost" value="0">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Delivery details, batch number, etc."></textarea>
                </div>
                <button type="submit" name="add_stock" class="btn">Add Stock</button>
            </form>
        </div>
    </div>
</body>
</html>