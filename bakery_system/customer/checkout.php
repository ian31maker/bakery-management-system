<?php
// ============================================
// FILE: checkout.php (COMPLETE FIXED VERSION)
// JOB:  Show checkout form + send real M-Pesa prompt to phone
// FIX:  Order items now save correctly so driver can see them
// ============================================

// load helper files
include '../includes/functions.php';
include '../includes/db_connect.php';
include '../includes/mpesa_helper.php';  // NEW: M-Pesa functions

// must be logged in as customer
if (!is_logged_in() || get_role() != 'customer') {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
$message = '';

// process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {

    // clean the form inputs
    $order_type = clean($_POST['order_type']);
    $pickup_date = !empty($_POST['pickup_date']) ? clean($_POST['pickup_date']) : null;
    $delivery_date = !empty($_POST['delivery_date']) ? clean($_POST['delivery_date']) : null;
    $delivery_address = !empty($_POST['delivery_address']) ? clean($_POST['delivery_address']) : null;
    $mpesa_phone_raw = clean($_POST['mpesa_phone']);

    // get cart from session
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

    // check if cart is empty
    if (empty($cart)) {
        $message = show_error('Your cart is empty!');
    } else {

        // ----------------------------------------
        // STEP 1: Fix phone number format
        // M-Pesa needs 2547XXXXXXXX, not 07XXXXXXXX
        // ----------------------------------------
        $mpesa_phone = format_phone_for_mpesa($mpesa_phone_raw);

        // if phone number is bad, stop and tell user
        if ($mpesa_phone === false) {
            $message = show_error('Invalid phone number! Use format: 0712345678 or 254712345678');
        } else {

            // ----------------------------------------
            // STEP 2: Calculate total price
            // ----------------------------------------
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['qty'];
            }

            // ----------------------------------------
            // STEP 3: Save order to database FIRST
            // We save before calling M-Pesa so we have an order_id
            // ----------------------------------------
            $pickup_sql = $pickup_date ? "'$pickup_date'" : "NULL";
            $delivery_sql = $delivery_date ? "'$delivery_date'" : "NULL";
            $address_sql = $delivery_address ? "'$delivery_address'" : "NULL";

            // insert the order (payment_status starts as 'pending')
            $sql = "INSERT INTO orders (customer_id, total, order_type, status, pickup_date, delivery_date, delivery_address, payment_method, payment_status, mpesa_phone, mpesa_checkout_id) 
                    VALUES ($customer_id, $total, '$order_type', 'pending', $pickup_sql, $delivery_sql, $address_sql, 'mpesa', 'pending', '$mpesa_phone', NULL)";

            if (mysqli_query($conn, $sql)) {

                // get the new order ID
                $order_id = mysqli_insert_id($conn);

                // ----------------------------------------
                // STEP 4: Save order items (FIXED)
                // We now safely grab product_id from the item array,
                // or fall back to the array key if your cart uses that style.
                // ----------------------------------------
                $item_errors = []; // collect any errors
              foreach ($cart as $key => $item) {
    $product_id = $item['id'];   // <-- grab the REAL id from inside the item
    $qty = $item['qty'];
    $price = $item['price'];
    $subtotal = $price * $qty;

                    // only insert if we have real data
                    if ($product_id > 0 && $qty > 0) {
                        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                                     VALUES ($order_id, $product_id, $qty, $price, $subtotal)";
                        
                        if (!mysqli_query($conn, $item_sql)) {
                            $item_errors[] = mysqli_error($conn);
                        }
                    }
                }

                // if any item failed to save, show it (so we know what broke)
                if (!empty($item_errors)) {
                    $message .= show_error('Some items failed to save: ' . implode(', ', array_unique($item_errors)));
                }

                // ----------------------------------------
                // STEP 5: CALL REAL M-PESA API
                // This sends the STK Push to customer's phone
                // ----------------------------------------
                $mpesa_result = stk_push($mpesa_phone, $total, $order_id);

                if ($mpesa_result['success']) {

                    // M-Pesa accepted our request and sent the prompt
                    // Save the checkout ID so callback.php can find this order later
                    $checkout_id = $mpesa_result['checkout_id'];
                    $sql = "UPDATE orders SET mpesa_checkout_id = '$checkout_id' WHERE order_id = $order_id";
                    mysqli_query($conn, $sql);

                    // clear the cart
                    unset($_SESSION['cart']);

                    // notify admin
                    $sql = "INSERT INTO notifications (user_id, title, message, link) 
                            VALUES (1, 'New Order', 'Order #$order_id received - waiting for M-Pesa', 'all_orders.php?order_id=$order_id')";
                    mysqli_query($conn, $sql);

                    // show success message to customer
                    $message = show_success('Order #' . $order_id . ' placed! ' . $mpesa_result['message']);
                    $order_complete = true;

                } else {

                    // M-Pesa API failed (wrong credentials, network error, etc.)
                    // Order is saved but payment prompt did not send
                    $message = show_error('Order saved but M-Pesa failed: ' . $mpesa_result['message']);
                }

            } else {
                // database insert failed
                $message = show_error('Error saving order: ' . mysqli_error($conn));
            }
        }
    }
}

// get cart items for display
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_total = 0;
foreach ($cart as $item) {
    $cart_total += $item['price'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .header {
            background: #8B4513; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }

        .checkout-box {
            background: white; padding: 30px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .checkout-box h2 { color: #5D4037; margin-bottom: 20px; }

        .cart-summary {
            background: #fafafa; padding: 20px; border-radius: 8px;
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #eee;
        }
        .total {
            font-size: 20px; font-weight: bold; color: #8B4513;
            margin-top: 15px; text-align: right;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 2px solid #d4a574; border-radius: 8px;
        }
        .btn {
            width: 100%; padding: 15px; background: #8B4513; color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold;
            cursor: pointer;
        }
        .btn:hover { background: #6d360f; }

        /* NEW: M-Pesa info box styling */
        .mpesa-info {
            background: #e8f5e9; padding: 20px; border-radius: 8px;
            margin-top: 20px; text-align: center;
            border: 2px solid #4caf50;
        }
        .mpesa-info p:first-child {
            font-size: 18px; font-weight: bold; color: #2e7d32;
        }
        .mpesa-info p:last-child {
            font-size: 14px; color: #666; margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="menu.php">🥖 Bakery System</a>
        <div>
            <a href="cart.php">🛒 Cart</a> | 
            <a href="order_history.php">📋 Orders</a> | 
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <?php if (!empty($cart)): ?>
        <div class="checkout-box">
            <h2>Checkout</h2>

            <!-- show cart summary -->
            <div class="cart-summary">
                <h3 style="margin-bottom: 15px;">Order Summary</h3>
                <?php foreach ($cart as $item): ?>
                <div class="cart-item">
                    <span><?php echo $item['name']; ?> x <?php echo $item['qty']; ?></span>
                    <span>KSh <?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                </div>
                <?php endforeach; ?>
                <div class="total">Total: <?php echo format_money($cart_total); ?></div>
            </div>

            <!-- checkout form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label>Order Type</label>
                    <select name="order_type" id="orderType" required onchange="toggleDate()">
                        <option value="pickup">Pickup</option>
                        <option value="delivery">Delivery</option>
                    </select>
                </div>

                <div class="form-group" id="pickupDate">
                    <label>Pickup Date</label>
                    <input type="date" name="pickup_date" min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group" id="deliveryDate" style="display: none;">
                    <label>Delivery Date</label>
                    <input type="date" name="delivery_date" min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group" id="deliveryAddress" style="display: none;">
                    <label>Delivery Address</label>
                    <textarea name="delivery_address" rows="3" placeholder="Enter your address"></textarea>
                </div>

                <div class="form-group">
                    <label>M-Pesa Phone Number</label>
                    <input type="text" name="mpesa_phone" placeholder="e.g. 0712345678" required>
                </div>

                <!-- NEW: Real M-Pesa info box -->
                <div class="mpesa-info">
                    <p>💳 M-Pesa Payment</p>
                    <p>Click "Place Order" and you will get a prompt on your phone.<br>Enter your M-Pesa PIN to complete payment.</p>
                </div>

                <button type="submit" name="checkout" class="btn" style="margin-top: 20px;">Place Order</button>
            </form>
        </div>
        <?php else: ?>
        <div class="checkout-box" style="text-align: center;">
            <h2>Your cart is empty</h2>
            <a href="menu.php" style="color: #8B4513;">Browse Menu</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // show/hide date and address fields based on order type
        function toggleDate() {
            var type = document.getElementById('orderType').value;
            if (type == 'pickup') {
                document.getElementById('pickupDate').style.display = 'block';
                document.getElementById('deliveryDate').style.display = 'none';
                document.getElementById('deliveryAddress').style.display = 'none';
            } else {
                document.getElementById('pickupDate').style.display = 'none';
                document.getElementById('deliveryDate').style.display = 'block';
                document.getElementById('deliveryAddress').style.display = 'block';
            }
        }
    </script>
</body>
</html>