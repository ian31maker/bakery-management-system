<?php
include '../includes/functions.php';
require_role(['admin']);
include '../includes/db_connect.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// header row
fputcsv($output, ['Sale ID', 'Cashier', 'Total', 'Payment Method', 'Amount Paid', 'Change', 'Date']);

// data rows
$sql = "SELECT s.*, u.full_name as cashier_name FROM sales s 
        JOIN users u ON s.cashier_id = u.user_id 
        ORDER BY s.sale_date DESC";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['sale_id'],
        $row['cashier_name'],
        $row['total'],
        $row['payment_method'],
        $row['amount_paid'],
        $row['change_amount'],
        $row['sale_date']
    ]);
}

fclose($output);
exit;
?>