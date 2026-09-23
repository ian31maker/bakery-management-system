<?php
include '../includes/functions.php';
require_role(['admin', 'baker']);
include '../includes/db_connect.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="inventory_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Material', 'Quantity', 'Unit', 'Min Stock', 'Status', 'Supplier']);

$sql = "SELECT m.*, s.name as supplier_name FROM raw_materials m 
        LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id 
        ORDER BY m.name";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $status = $row['quantity'] <= $row['min_stock'] ? 'LOW STOCK' : 'OK';
    fputcsv($output, [
        $row['name'],
        $row['quantity'],
        $row['unit'],
        $row['min_stock'],
        $status,
        $row['supplier_name'] ?: 'N/A'
    ]);
}

fclose($output);
exit;
?>