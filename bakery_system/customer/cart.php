<?php
include '../includes/functions.php';
require_role(['customer']);
include '../includes/db_connect.php';

start_session();

// remove item
if (isset($_GET['remove'])) {
    $index = intval($_GET['remove']);
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}

// update qty
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $index => $qty) {
        if ($qty > 0) {
            $_SESSION['cart'][$index]['qty'] = intval($qty);
        } else {
            unset($_SESSION['cart'][$index]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}

// calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart - Bakery Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .navbar {
            background: #8B4513; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 800px; margin: 0 auto; padding: 30px; }
        h1 { color: #5D4037; margin-bottom: 20px; }
        
        .cart-item {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .cart-item h3 { color: #5D4037; }
        .cart-item .price { color: #8B4513; font-weight: bold; }
        .qty-input { width: 60px; padding: 8px; border: 2px solid #d4a574; border-radius: 6px; text-align: center; }
        .btn-remove { color: #c62828; text-decoration: none; font-weight: bold; }
        
        .cart-total {
            background: white; padding: 20px; border-radius: 10px;
            margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: right;
        }
        .cart-total h2 { color: #8B4513; font-size: 28px; }
        .btn-checkout {
            padding: 15px 40px; background: #4CAF50; color: white;
            text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold;
            display: inline-block; margin-top: 15px;
        }
        .btn-checkout:hover { background: #388E3C; }
        .empty { text-align: center; color: #666; padding: 50px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🥖 Bakery Shop</h2>
        <div>
            <a href="menu.php">Menu</a>
            <a href="order_history.php">My Orders</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h1>🛒 Shopping Cart</h1>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty">
                <h2>Your cart is empty</h2>
                <p><a href="menu.php" style="color: #8B4513;">Browse menu</a></p>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <div class="cart-item">
                    <div>
                        <h3><?php echo $item['name']; ?></h3>
                        <p class="price"><?php echo format_money($item['price']); ?> each</p>
                    </div>
                    <div>
                        <input type="number" name="qty[<?php echo $index; ?>]" value="<?php echo $item['qty']; ?>" class="qty-input" min="1">
                    </div>
                    <div>
                        <p class="price"><?php echo format_money($item['price'] * $item['qty']); ?></p>
                        <a href="?remove=<?php echo $index; ?>" class="btn-remove">Remove</a>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" name="update" style="padding: 10px 20px; background: #8B4513; color: white; border: none; border-radius: 6px; cursor: pointer;">Update Quantities</button>
            </form>
            
            <div class="cart-total">
                <h2>Total: <?php echo format_money($total); ?></h2>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>