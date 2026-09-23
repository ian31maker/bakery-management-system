<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "bakery_db";
$conn = mysqli_connect($host, $username, $password, $database);

$users = [
    ['admin', 'admin123'],
    ['cashier', 'cashier123'],
    ['baker', 'baker123'],
    ['driver', 'driver123'],
    ['alice', 'customer123'],
    ['bob', 'customer123']
];

foreach ($users as $user) {
    $username = $user[0];
    $password = $user[1];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password_hash = '$hash' WHERE username = '$username'";
    mysqli_query($conn, $sql);
    echo "Updated $username<br>";
}

echo "<br>Done! <a href='index.php'>Go to Login</a>";
?>