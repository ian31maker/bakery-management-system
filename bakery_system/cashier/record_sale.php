<?php
include '../includes/functions.php';
require_role(['cashier']);
include '../includes/db_connect.php';

$message = '';
$sale_complete = false;

// get all products
$sql = "SELECT * FROM products WHERE is_available = 1 ORDER BY name";
$products = mysqli_query($conn, $sql);

// process sale
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_sale'])) {
    $items = $_POST['items']; // array of [product_id, qty, price]
    $payment_method = clean($_POST['payment_method']);
    $amount_paid = floatval($_POST['amount_paid']);
    $ticket_id = !empty($_POST['ticket_id']) ? intval($_POST['ticket_id']) : null;
    
    // calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += floatval($item['price']) * intval($item['qty']);
    }
    
    $change = $amount_paid - $total;
    
    // insert sale
    $cashier_id = $_SESSION['user_id'];
    $ticket_sql = $ticket_id ? "'$ticket_id'" : "NULL";
    $sql = "INSERT INTO sales (ticket_id, cashier_id, total, payment_method, amount_paid, change_amount) 
            VALUES ($ticket_sql, $cashier_id, $total, '$payment_method', $amount_paid, $change)";
    
    if (mysqli_query($conn, $sql)) {
        $sale_id = mysqli_insert_id($conn);
        
        // insert sale items
        foreach ($items as $item) {
            $pid = intval($item['product_id']);
            $qty = intval($item['qty']);
            $price = floatval($item['price']);
            $subtotal = $price * $qty;
            
            $sql = "INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) 
                    VALUES ($sale_id, $pid, $qty, $price, $subtotal)";
            mysqli_query($conn, $sql);
            
            // deduct from finished products (simplified - just track, don't enforce)
        }
        
        // update ticket if used
        if ($ticket_id) {
            $sql = "UPDATE tickets SET status = 'done', served_at = NOW(), sale_id = $sale_id WHERE ticket_id = $ticket_id";
            mysqli_query($conn, $sql);
        }
        
        $message = show_success('Sale completed! Change: ' . format_money($change));
        $sale_complete = true;
        $completed_sale_id = $sale_id;
    } else {
        $message = show_error('Error: ' . mysqli_error($conn));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Sale - Bakery System</title>
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
        
        .pos-container {
            display: grid; grid-template-columns: 2fr 1fr; gap: 20px;
        }
        .products-grid {
            background: white; padding: 20px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-btn {
            padding: 15px; margin: 5px; background: #f5e6d3;
            border: 2px solid #d4a574; border-radius: 8px;
            cursor: pointer; font-size: 14px; min-width: 120px;
        }
        .product-btn:hover { background: #d4a574; }
        
        .cart-panel {
            background: white; padding: 20px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-panel h3 { color: #1565C0; margin-bottom: 15px; }
        .cart-item {
            display: flex; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #eee;
        }
        .total-row {
            font-size: 24px; font-weight: bold; color: #1565C0;
            margin-top: 15px; padding-top: 15px; border-top: 2px solid #1565C0;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px; border: 2px solid #d4a574; border-radius: 8px;
        }
        .btn-complete {
            width: 100%; padding: 15px; background: #4CAF50; color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold;
            cursor: pointer; margin-top: 15px;
        }
        .btn-complete:hover { background: #388E3C; }
        .btn-print {
            width: 100%; padding: 15px; background: #8B4513; color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold;
            cursor: pointer; margin-top: 10px; text-decoration: none; display: inline-block; text-align: center;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="record_sale.php" class="active">💰 Record Sale</a>
        <a href="ticket_queue.php">🎫 Ticket Queue</a>
        <a href="sales_history.php">📋 Sales History</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #0D47A1;">
            <small>Breadcrumb:</small><br>
            <small>Home > Record Sale</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Point of Sale</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Record Sale
        </div>
        
        <?php echo $message; ?>
        
        <?php if ($sale_complete): ?>
            <a href="print_receipt.php?sale_id=<?php echo $completed_sale_id; ?>" target="_blank" class="btn-print">🖨️ Print Receipt</a>
            <br><br>
        <?php endif; ?>
        
        <div class="pos-container">
            <div class="products-grid">
                <h3 style="color: #1565C0; margin-bottom: 15px;">Products</h3>
                <div style="display: flex; flex-wrap: wrap;">
                    <?php while ($product = mysqli_fetch_assoc($products)): ?>
                        <button class="product-btn" onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)">
                            <?php echo $product['name']; ?><br>
                            <strong><?php echo format_money($product['price']); ?></strong>
                        </button>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="cart-panel">
                <h3>Current Sale</h3>
                <form method="POST" action="" id="saleForm">
                    <div id="cartItems"></div>
                    
                    <div class="total-row">
                        Total: <span id="cartTotal">KSh 0.00</span>
                    </div>
                    
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Ticket Number (optional)</label>
                        <input type="number" name="ticket_id" placeholder="Enter ticket #">
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid" id="amountPaid" required>
                    </div>
                    
                    <button type="submit" name="complete_sale" class="btn-complete">Complete Sale</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        let cart = [];
        
        function addToCart(id, name, price) {
            let existing = cart.find(item => item.id === id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({id, name, price, qty: 1});
            }
            renderCart();
        }
        
        function renderCart() {
            let html = '';
            let total = 0;
            
            cart.forEach((item, index) => {
                let subtotal = item.price * item.qty;
                total += subtotal;
                html += `
                    <div class="cart-item">
                        <div>
                            ${item.name} x ${item.qty}
                            <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                            <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                            <input type="hidden" name="items[${index}][price]" value="${item.price}">
                        </div>
                        <div>KSh ${subtotal.toFixed(2)}</div>
                    </div>
                `;
            });
            
            document.getElementById('cartItems').innerHTML = html;
            document.getElementById('cartTotal').textContent = 'KSh ' + total.toFixed(2);
        }
    </script>
</body>
</html>