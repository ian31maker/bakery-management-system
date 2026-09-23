<?php
// ============================================
// FILE: mpesa_helper.php
// JOB:  Talk to Safaricom servers to send the STK Push prompt
// ============================================

// load the config file with passwords
require_once 'mpesa_config.php';

// --------------------------------------------
// FUNCTION: get_access_token()
// JOB:      Ask Safaricom for a temporary login ticket
// RETURNS:  A token string (like a temporary password)
// --------------------------------------------
function get_access_token() {

    // build the login URL
    $url = MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';

    // create the login password (base64 of key:secret)
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    // set up the request
    $headers = [
        'Authorization: Basic ' . $credentials,  // attach login password
        'Content-Type: application/json'         // tell Safaricom we speak JSON
    ];

    // open connection to Safaricom
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);           // where to go
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  // what headers to send
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // give us the answer back
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // skip SSL check (for testing)

    // send the request
    $response = curl_exec($curl);

    // check if curl broke
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return ['error' => 'Curl failed: ' . $error];
    }

    // close connection
    curl_close($curl);

    // turn JSON answer into PHP array
    $result = json_decode($response, true);

    // give back the access token
    if (isset($result['access_token'])) {
        return $result['access_token'];
    }

    // if something went wrong, return the error
    return ['error' => $result];
}

// --------------------------------------------
// FUNCTION: stk_push()
// JOB:      Send the M-Pesa prompt to customer's phone
// INPUT:    $phone = customer phone (format 2547XXXXXXXX)
//           $amount = how much to ask for (in KSh)
//           $order_id = your order number
// RETURNS:  Array with success or error info
// --------------------------------------------
function stk_push($phone, $amount, $order_id) {

    // STEP 1: get the temporary login ticket from Safaricom
    $token = get_access_token();

    // if getting token failed, stop here and cry
    if (is_array($token) && isset($token['error'])) {
        return ['success' => false, 'message' => 'Failed to get token: ' . json_encode($token['error'])];
    }

    // STEP 2: build the STK Push URL
    $url = MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest';

    // STEP 3: create the timestamp (format: YYYYMMDDHHMMSS)
    $timestamp = date('YmdHis');

    // STEP 4: create the password
    // password = base64( shortcode + passkey + timestamp )
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    // STEP 5: build the data to send
    $data = [
        'BusinessShortCode' => MPESA_SHORTCODE,           // your paybill/till number
        'Password'          => $password,                  // the password we just made
        'Timestamp'         => $timestamp,                 // current time
        'TransactionType'   => 'CustomerPayBillOnline',    // type of payment
        'Amount'            => round($amount),             // round to whole number (M-Pesa likes whole shillings)
        'PartyA'            => $phone,                     // customer phone number
        'PartyB'            => MPESA_SHORTCODE,             // your paybill/till number
        'PhoneNumber'       => $phone,                     // phone to send prompt to
        'CallBackURL'       => MPESA_CALLBACK_URL,        // where Safaricom tells us "paid" or "failed"
        'AccountReference'  => MPESA_ACCOUNT_REF . '-' . $order_id,  // what customer sees on phone
        'TransactionDesc'   => MPESA_TRANSACTION_DESC      // short description
    ];

    // STEP 6: set up headers
    $headers = [
        'Authorization: Bearer ' . $token,   // attach the temporary ticket
        'Content-Type: application/json'       // we are sending JSON
    ];

    // STEP 7: send the request to Safaricom
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);                          // this is a POST request
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));      // attach our data
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);               // skip SSL check (for testing)

    // send it!
    $response = curl_exec($curl);

    // check if curl broke
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return ['success' => false, 'message' => 'Curl failed: ' . $error];
    }

    // close connection
    curl_close($curl);

    // STEP 8: read the answer
    $result = json_decode($response, true);

    // STEP 9: check if Safaricom accepted our request
    // ResponseCode "0" means "yes, I will send the prompt"
    if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
        return [
            'success'       => true,
            'message'       => 'M-Pesa prompt sent! Check your phone.',
            'checkout_id'   => $result['CheckoutRequestID'],  // this ID tracks the payment
            'response'      => $result
        ];
    }

    // if ResponseCode is not 0, something went wrong
    return [
        'success' => false,
        'message' => 'M-Pesa error: ' . ($result['errorMessage'] ?? json_encode($result)),
        'response' => $result
    ];
}

// --------------------------------------------
// FUNCTION: format_phone_for_mpesa()
// JOB:      Fix phone number to 2547XXXXXXXX format
// INPUT:    $phone = raw phone number from form
// RETURNS:  Clean phone number or false if bad
// --------------------------------------------
function format_phone_for_mpesa($phone) {


    $phone = preg_replace('/[^0-9]/', '', $phone);

  
    if (strpos($phone, '0') === 0) {
        $phone = '254' . substr($phone, 1);
    }


    if (strpos($phone, '7') === 0 || strpos($phone, '1') === 0) {
        $phone = '254' . $phone;
    }

    if (preg_match('/^254[71][0-9]{8}$/', $phone)) {
        return $phone;
    }

    // if it does not match, return false (bad number)
    return false;
}

// --------------------------------------------
// FUNCTION: check_transaction_status()
// JOB:      Ask Safaricom "Did this payment actually happen?"
// INPUT:    $checkout_id = the CheckoutRequestID from STK Push
// RETURNS:  Array with success/paid status and message

function check_transaction_status($checkout_id) {

    // STEP 1: get access token
    $token = get_access_token();

    if (is_array($token) && isset($token['error'])) {
        return ['success' => false, 'paid' => false, 'message' => 'Token failed: ' . $token['error']];
    }

    // STEP 2: build the query URL
    $url = MPESA_BASE_URL . '/mpesa/stkpushquery/v1/query';

    // STEP 3: create timestamp and password
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    // STEP 4: build data
    $data = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'CheckoutRequestID' => $checkout_id
    ];

    // STEP 5: send request
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Check if the request itself failed
    if ($response === false) {
        return ['success' => false, 'paid' => false, 'message' => 'API request failed: ' . $curl_error];
    }

    // STEP 6: read response
    $result = json_decode($response, true);

    if (!is_array($result)) {
        return ['success' => false, 'paid' => false, 'message' => 'Invalid API response. HTTP status: ' . $http_code];
    }

    // Check if payment was successful
    // ResultCode 0 = paid
    if (isset($result['ResultCode']) && $result['ResultCode'] == '0') {
        return [
            'success' => true,
            'paid'    => true,
            'message' => 'Payment confirmed! ' . $result['ResultDesc'],
            'response' => $result
        ];
    }

    // ResultCode 1032 = cancelled by user
    // ResultCode 1037 = timeout (user didn't enter PIN)
    // ResultCode 1 = other failure
    $error_code = $result['ResultCode'] ?? 'unknown';
    $error_msg = $result['ResultDesc'] ?? json_encode($result);

    return [
        'success' => true,  // API call worked
        'paid'    => false, // but payment did not succeed
        'message' => "ResultCode $error_code: $error_msg",
        'response' => $result
    ];
}
?>