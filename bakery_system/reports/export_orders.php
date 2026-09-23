<?php
include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Order ID', 'Customer', 'Type', 'Total', 'Status', 'Payment', 'Date']);

$sql = "SELECT o.*, c.full_name as customer_name FROM orders o 
        JOIN users c ON o.customer_id = c.user_id 
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['order_id'],
        $row['customer_name'],
        $row['order_type'],
        $row['total'],
        $row['status'],
        $row['payment_status'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
?>