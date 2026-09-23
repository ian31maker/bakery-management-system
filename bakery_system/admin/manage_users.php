<?php
include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

$message = '';

// add new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $full_name = clean($_POST['full_name']);
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    $role = clean($_POST['role']);
    $phone = clean($_POST['phone']);
    $email = clean($_POST['email']);
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (full_name, username, password_hash, role, phone, email) 
            VALUES ('$full_name', '$username', '$hash', '$role', '$phone', '$email')";
    
    if (mysqli_query($conn, $sql)) {
        $message = show_success('User added successfully!');
    } else {
        $message = show_error('Error: ' . mysqli_error($conn));
    }
}

// delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "UPDATE users SET is_active = 0 WHERE user_id = $id";
    mysqli_query($conn, $sql);
    $message = show_success('User deactivated!');
}

// get all users
$sql = "SELECT * FROM users WHERE is_active = 1 ORDER BY role, full_name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Bakery System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI'; background: #f5e6d3; min-height: 100vh; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 250px; height: 100vh;
            background: #5D4037; color: white; padding: 20px; overflow-y: auto;
        }
        .sidebar h2 { margin-bottom: 30px; text-align: center; font-size: 22px; }
        .sidebar a {
            display: block; color: #f5e6d3; text-decoration: none;
            padding: 12px 15px; margin-bottom: 5px; border-radius: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: #8B4513; color: white; }
        .sidebar .logout { margin-top: 30px; background: #c62828; text-align: center; }
        .main { margin-left: 250px; padding: 20px; }
        .header {
            background: white; padding: 20px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { color: #5D4037; }
        .breadcrumb { margin-bottom: 20px; color: #8B4513; }
        .breadcrumb a { color: #8B4513; text-decoration: none; }
        
        .form-box {
            background: white; padding: 25px; border-radius: 10px;
            margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-box h3 { color: #5D4037; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #5D4037; font-weight: 600; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px; border: 2px solid #d4a574;
            border-radius: 8px; font-size: 14px;
        }
        .btn {
            padding: 12px 25px; background: #8B4513; color: white;
            border: none; border-radius: 8px; cursor: pointer; font-size: 14px;
        }
        .btn:hover { background: #6d360f; }
        
        table {
            width: 100%; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th {
            background: #8B4513; color: white; padding: 15px;
            text-align: left;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #f5e6d3; }
        tr:hover { background: #fafafa; }
        .role-badge {
            padding: 5px 12px; border-radius: 15px; font-size: 12px;
            font-weight: bold; text-transform: uppercase;
        }
        .role-admin { background: #ffebee; color: #c62828; }
        .role-cashier { background: #e3f2fd; color: #1565c0; }
        .role-baker { background: #e8f5e9; color: #2e7d32; }
        .role-driver { background: #fff3e0; color: #e65100; }
        .role-customer { background: #f3e5f5; color: #7b1fa2; }
        .delete-btn {
            color: #c62828; text-decoration: none; font-weight: bold;
        }
        .delete-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🥖 Bakery System</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_users.php" class="active">👥 Manage Users</a>
        <a href="all_orders.php">📦 All Orders</a>
        <a href="verify_payments.php">💳 Verify Payments</a>
        <a href="dispatch_orders.php">🚚 Dispatch Orders</a>
        <a href="view_reports.php">📊 Reports</a>
        <a href="calendar.php">📅 Calendar</a>
        <a href="set_prices.php">💰 Set Prices</a>
        <a href="notifications.php">🔔 Notifications</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #8B4513;">
            <small>Breadcrumb:</small><br>
            <small>Home > Manage Users</small>
        </div>
    </div>
    
    <div class="main">
        <div class="header">
            <h1>Manage Users</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > Manage Users
        </div>
        
        <?php echo $message; ?>
        
        <div class="form-box">
            <h3>Add New User</h3>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="cashier">Cashier</option>
                            <option value="baker">Baker</option>
                            <option value="driver">Driver</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn" style="margin-top: 15px;">Add User</button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td>
                        <span class="role-badge role-<?php echo $row['role']; ?>">
                            <?php echo $row['role']; ?>
                        </span>
                    </td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="?delete=<?php echo $row['user_id']; ?>" class="delete-btn" onclick="return confirm('Deactivate this user?')">Deactivate</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>