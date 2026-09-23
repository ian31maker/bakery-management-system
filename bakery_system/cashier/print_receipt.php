<?php
include '../includes/functions.php';
require_role(['cashier']);
include '../includes/db_connect.php';

$sale_id = isset($_GET['sale_id']) ? intval($_GET['sale_id']) : 0;

$sql = "SELECT s.*, u.full_name as cashier_name FROM sales s 
        JOIN users u ON s.cashier_id = u.user_id 
        WHERE s.sale_id = $sale_id";
$result = mysqli_query($conn, $sql);
$sale = mysqli_fetch_assoc($result);

if (!$sale) {
    die('Sale not found');
}

$sql = "SELECT si.*, p.name as product_name FROM sale_items si 
        JOIN products p ON si.product_id = p.product_id 
        WHERE si.sale_id = $sale_id";
$items = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo $sale_id; ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 300px;
            margin: 20px auto;
            padding: 20px;
            border: 1px dashed #ccc;
        }
        .center { text-align: center; }
        .logo { font-size: 24px; margin-bottom: 5px; }
        .shop-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .shop-info { font-size: 12px; color: #666; margin-bottom: 20px; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        .item { display: flex; justify-content: space-between; margin: 5px 0; font-size: 14px; }
        .total { font-weight: bold; font-size: 16px; margin-top: 10px; }
        .footer { margin-top: 20px; font-size: 12px; text-align: center; color: #666; }
        
        @media print {
            body { border: none; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="center">
        <div class="logo">🥖</div>
        <div class="shop-name">Bakery Management System</div>
        <div class="shop-info">Nairobi, Kenya<br>Tel: 0700000000</div>
    </div>
    
    <div class="line"></div>
    <div style="font-size: 12px;">
        Receipt #: <?php echo $sale_id; ?><br>
        Date: <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?><br>
        Cashier: <?php echo $sale['cashier_name']; ?>
    </div>
    <div class="line"></div>
    
    <?php while ($item = mysqli_fetch_assoc($items)): ?>
    <div class="item">
        <span><?php echo $item['quantity']; ?> x <?php echo $item['product_name']; ?></span>
        <span><?php echo format_money($item['subtotal']); ?></span>
    </div>
    <?php endwhile; ?>
    
    <div class="line"></div>
    <div class="item total">
        <span>TOTAL</span>
        <span><?php echo format_money($sale['total']); ?></span>
    </div>
    <div class="item">
        <span>Paid (<?php echo ucfirst($sale['payment_method']); ?>)</span>
        <span><?php echo format_money($sale['amount_paid']); ?></span>
    </div>
    <div class="item">
        <span>Change</span>
        <span><?php echo format_money($sale['change_amount']); ?></span>
    </div>
    
    <div class="line"></div>
    <div class="footer">
        Thank you for your business!<br>
        Come again soon 🥖
    </div>
    
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #8B4513; color: white; border: none; border-radius: 8px; cursor: pointer;">🖨️ Print</button>
        <a href="sales_history.php" style="padding: 10px 20px; background: #666; color: white; text-decoration: none; border-radius: 8px; margin-left: 10px;">Back</a>
    </div>
</body>
</html>