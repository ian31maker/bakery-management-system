<?php
include '../includes/functions.php';
include '../includes/db_connect.php';

if (!is_logged_in() || get_role() != 'customer') {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// verify order belongs to customer and is delivered
$sql = "SELECT * FROM orders WHERE order_id = $order_id AND customer_id = $customer_id AND status = 'delivered'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    die('Invalid order');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $comment = clean($_POST['comment']);
    
    $sql = "INSERT INTO reviews (order_id, customer_id, rating, comment) 
            VALUES ($order_id, $customer_id, $rating, '$comment')";
    
    if (mysqli_query($conn, $sql)) {
        $message = show_success('Thank you for your review!');
    } else {
        $message = show_error('Error submitting review');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Review - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .header {
            background: #8B4513; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 500px; margin: 30px auto; padding: 0 20px; }
        
        .review-box {
            background: white; padding: 30px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .review-box h2 { color: #5D4037; margin-bottom: 20px; }
        
        .stars {
            display: flex; gap: 10px; margin-bottom: 20px;
        }
        .stars input[type="radio"] { display: none; }
        .stars label {
            font-size: 30px; cursor: pointer; color: #ddd;
        }
        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label { color: #FFD700; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; font-weight: 600; }
        .form-group textarea {
            width: 100%; padding: 12px; border: 2px solid #d4a574; border-radius: 8px;
            min-height: 100px;
        }
        .btn {
            width: 100%; padding: 15px; background: #8B4513; color: white;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="menu.php">🥖 Bakery System</a>
        <div>
            <a href="order_history.php">📋 My Orders</a> | 
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php echo $message; ?>
        
        <div class="review-box">
            <h2>Leave a Review</h2>
            <p style="color: #666; margin-bottom: 20px;">Order #<?php echo $order_id; ?></p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Rating</label>
                    <div class="stars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Your Review</label>
                    <textarea name="comment" placeholder="Tell us about your experience..." required></textarea>
                </div>
                
                <button type="submit" class="btn">Submit Review</button>
            </form>
        </div>
    </div>
</body>
</html>