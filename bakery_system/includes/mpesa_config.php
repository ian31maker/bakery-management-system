<?php
// ============================================
// FILE: mpesa_config.php
// JOB:  Store your M-Pesa Daraja API passwords
// ============================================

// turn on error display so you see what broke
// remove this line after you finish testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// your Safaricom app details
// get these from https://developer.safaricom.co.ke
define('MPESA_CONSUMER_KEY',    'RAajS7XkjUC5deSlO58OAQ9NbjVEDtqKZLf2fvsdEGecLghG');      // paste your consumer key
define('MPESA_CONSUMER_SECRET', 'Af3RrGmTaghKZAvyUyCqixhfpuFBcMx4PH6g8zEwHGgrI9WPhd8iiSRRqd3XHgNW');   // paste your consumer secret

// your business details
// for sandbox testing use 174379 (Safaricom test paybill)
define('MPESA_SHORTCODE',       174379);          // your paybill or till number
define('MPESA_PASSKEY',         'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');            // Safaricom gave you this

// where M-Pesa sends the "paid" message after customer pays
// MUST be a live URL, localhost will NOT work
// example: https://yourdomain.com/mpesa_callback.php
define('MPESA_CALLBACK_URL', 'https://httpbin.org/post');
// Safaricom API endpoints
// use sandbox for testing, production for real money
// sandbox URL:
define('MPESA_BASE_URL',        'https://sandbox.safaricom.co.ke');
// production URL (switch to this when ready):
// define('MPESA_BASE_URL',     'https://api.safaricom.co.ke');

// the account reference shown on customer phone
// keep it short, e.g. BAKERY or your shop name
define('MPESA_ACCOUNT_REF',     'BAKERY');

// the text shown below amount on customer phone
define('MPESA_TRANSACTION_DESC', 'Payment for bakery order');
?>