<?php
include '../includes/functions.php';
require_role(['admin', 'cashier', 'baker', 'driver']);
include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];

// get unread count
$sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0";
$result = mysqli_query($conn, $sql);
$count = mysqli_fetch_assoc($result)['total'];

// mark as read if requested
if (isset($_GET['mark_read'])) {
    $notif_id = intval($_GET['mark_read']);
    $sql = "UPDATE notifications SET is_read = 1 WHERE notif_id = $notif_id AND user_id = $user_id";
    mysqli_query($conn, $sql);
}

// get notifications
$sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'count' => $count,
    'notifications' => $notifications
]);
exit;
?>