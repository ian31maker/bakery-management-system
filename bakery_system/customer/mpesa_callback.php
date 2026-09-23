<?php
// ============================================
// FILE: mpesa_callback.php
// JOB:  Safaricom sends payment result here after customer enters PIN
//       This file updates your database to say "paid" or "failed"
// ============================================

// load database connection
require_once '../includes/db_connect.php';

// read the raw JSON that Safaricom sent us
$json = file_get_contents('php://input');

// save the raw data to a log file so we can debug
// this creates a file called mpesa_log.txt in the same folder
file_put_contents('mpesa_log.txt', date('Y-m-d H:i:s') . "\n" . $json . "\n\n", FILE_APPEND);

// turn the JSON into PHP array
$data = json_decode($json, true);

// Safaricom wraps the result inside "Body" -> "stkCallback"
if (isset($data['Body']['stkCallback'])) {

    $callback = $data['Body']['stkCallback'];

    // get the checkout ID (this matches the one we got when we sent the prompt)
    $checkout_id = $callback['CheckoutRequestID'];

    // get the result code
    // 0 = success (customer paid)
    // anything else = failed (cancelled, wrong PIN, no money, etc.)
    $result_code = $callback['ResultCode'];
    $result_desc = $callback['ResultDesc'];

    // find the order in our database using the checkout ID
    // we need to add a column "mpesa_checkout_id" to orders table
    $sql = "SELECT order_id FROM orders WHERE mpesa_checkout_id = '$checkout_id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        $order_id = $order['order_id'];

        if ($result_code == 0) {
            // ----------------------------------------
            // CUSTOMER PAID SUCCESSFULLY
            // ----------------------------------------
            $sql = "UPDATE orders 
                    SET payment_status = 'paid', 
                        status = 'confirmed',
                        mpesa_result = '$result_desc'
                    WHERE order_id = $order_id";
            mysqli_query($conn, $sql);

            // notify admin that payment came in
            $sql = "INSERT INTO notifications (user_id, title, message, link) 
                    VALUES (1, 'Payment Received', 'Order #$order_id paid via M-Pesa', 'admin/all_orders.php?order_id=$order_id')";
            mysqli_query($conn, $sql);

        } else {
            // ----------------------------------------
            // PAYMENT FAILED (cancelled, wrong PIN, timeout, etc.)
            // ----------------------------------------
            $sql = "UPDATE orders 
                    SET payment_status = 'failed',
                        mpesa_result = '$result_desc'
                    WHERE order_id = $order_id";
            mysqli_query($conn, $sql);
        }
    }
}

// Safaricom expects a simple JSON reply saying "OK I got your message"
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
?>