<?php
// ============================================
// FILE: customer/menu.php
// JOB:  Show products with photos
// ============================================

include '../includes/functions.php';
include '../includes/db_connect.php';

// get all products with categories
$sql = "SELECT p.*, c.name as category_name FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_available = 1 
        ORDER BY c.name, p.name";
$result = mysqli_query($conn, $sql);

// start cart session
start_session();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// add to cart
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']);
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['qty']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $sql = "SELECT * FROM products WHERE product_id = $product_id";
        $prod = mysqli_fetch_assoc(mysqli_query($conn, $sql));
        $_SESSION['cart'][] = [
            'id' => $product_id,
            'name' => $prod['name'],
            'price' => $prod['price'],
            'qty' => 1
        ];
    }
    header('Location: menu.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Bakery Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .navbar {
            background: #8B4513; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .navbar .cart-icon { font-size: 20px; position: relative; }
        .cart-badge {
            position: absolute; top: -8px; right: -8px;
            background: #FF9800; color: white; border-radius: 50%;
            width: 20px; height: 20px; font-size: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px; }
        .category-title {
            color: #5D4037; font-size: 24px; margin: 30px 0 15px;
            border-bottom: 2px solid #d4a574; padding-bottom: 10px;
        }
        .products-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white; border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 180px;
            background: #f5e6d3;
            overflow: hidden;
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .product-img .emoji-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 60px;
        }
        .product-info { padding: 15px; }
        .product-info h3 { color: #5D4037; margin-bottom: 5px; }
        .product-info p { color: #666; font-size: 14px; margin-bottom: 10px; }
        .product-info .price {
            color: #8B4513; font-size: 20px; font-weight: bold; margin-bottom: 10px;
        }
        .btn-add {
            width: 100%; padding: 10px; background: #8B4513; color: white;
            border: none; border-radius: 6px; cursor: pointer; font-size: 16px;
            text-align: center; display: inline-block; text-decoration: none;
        }
        .btn-add:hover { background: #6d360f; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🥖 Bakery Shop</h1>
        <div>
            <a href="menu.php">Menu</a>
            <?php if (is_logged_in() && get_role() == 'customer'): ?>
                <a href="cart.php" class="cart-icon">🛒 Cart <?php 
                    $cart_count = array_sum(array_column($_SESSION['cart'], 'qty'));
                    if ($cart_count > 0) echo '<span class="cart-badge">' . $cart_count . '</span>';
                ?></a>
                <a href="order_history.php">My Orders</a>
                <a href="../logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <?php
        $current_category = '';
        while ($row = mysqli_fetch_assoc($result)):
            if ($current_category != $row['category_name']):
                if ($current_category != '') echo '</div>';
                $current_category = $row['category_name'];
                echo '<div class="category-title">' . $current_category . '</div>';
                echo '<div class="products-grid">';
            endif;
        ?>
            <div class="product-card">
                <div class="product-img">
                    <?php
                    // grab just the filename from whatever is in the database
                    $img_name = !empty($row['image']) ? basename($row['image']) : '';
                    $folder_url = '../assets/images/products/';
                    $folder_disk = __DIR__ . '/../assets/images/products/';
                    $exists = file_exists($folder_disk . $img_name);
                    ?>
                    
                    <?php if (!empty($img_name) && $exists): ?>
                        <img src="<?php echo $folder_url . $img_name; ?>" alt="<?php echo $row['name']; ?>">
                    <?php else: ?>
                        <div class="emoji-fallback">🥖</div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h3><?php echo $row['name']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <div class="price"><?php echo format_money($row['price']); ?></div>
                    <a href="?add=<?php echo $row['product_id']; ?>" class="btn-add">Add to Cart</a>
                </div>
            </div>
        <?php endwhile; ?>
        <?php if ($current_category != '') echo '</div>'; ?>
    </div>
</body>
</html>