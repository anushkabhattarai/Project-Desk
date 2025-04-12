<?php
session_start();
header('Content-Type: application/json');

// Enable error logging
error_log('Payment initiation started at ' . date('Y-m-d H:i:s'));

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    error_log('Unauthorized access attempt - No session ID');
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'You must be logged in to initiate a payment',
        'debug_info' => [
            'session_exists' => isset($_SESSION),
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// Get and decode the POST data
$raw_data = file_get_contents('php://input');
error_log('Received raw data: ' . $raw_data);

// Check if raw data is empty
if (empty($raw_data)) {
    error_log('Empty request body received');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No data received in request',
        'debug_info' => [
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
            'request_method' => $_SERVER['REQUEST_METHOD']
        ]
    ]);
    exit;
}

// Try to decode JSON
$data = json_decode($raw_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data received',
        'debug_info' => [
            'json_error' => json_last_error_msg(),
            'raw_data' => $raw_data
        ]
    ]);
    exit;
}

// Debug logging for request data
error_log('Decoded payment request: ' . print_r($data, true));

// Validate required parameters
if (!isset($data['amount']) || !isset($data['plan_id']) || !isset($data['plan_name'])) {
    error_log('Missing required parameters. Received data: ' . print_r($data, true));
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required parameters',
        'debug_info' => [
            'received' => $data,
            'missing_fields' => array_diff(['amount', 'plan_id', 'plan_name'], array_keys($data)),
            'raw_data' => $raw_data
        ]
    ]);
    exit;
}

// Validate data types
if (!is_numeric($data['amount']) || !is_numeric($data['plan_id']) || !is_string($data['plan_name'])) {
    error_log('Invalid parameter types. Received data: ' . print_r($data, true));
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameter types',
        'debug_info' => [
            'received' => $data,
            'expected_types' => [
                'amount' => 'numeric',
                'plan_id' => 'numeric',
                'plan_name' => 'string'
            ],
            'actual_types' => [
                'amount' => gettype($data['amount']),
                'plan_id' => gettype($data['plan_id']),
                'plan_name' => gettype($data['plan_name'])
            ]
        ]
    ]);
    exit;
}

// Convert amount to paisa (1 NPR = 100 paisa)
$amount_in_paisa = intval($data['amount']) * 100;

// Validate minimum amount (1000 paisa = Rs. 10)
if ($amount_in_paisa < 1000) {
    error_log('Amount below minimum: ' . $amount_in_paisa . ' paisa');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Amount should be greater than Rs. 10 (1000 paisa)',
        'debug_info' => [
            'amount_received' => $amount_in_paisa,
            'minimum_required' => 1000
        ]
    ]);
    exit;
}

// Generate unique order ID
$order_id = 'ORDER_' . $_SESSION['id'] . '_' . time() . '_' . uniqid();

// Prepare the payload with all optional fields
$payload = array(
    'return_url' => 'http://localhost/Project_Desk/process_payment.php',
    'website_url' => 'http://localhost/Project_Desk',
    'amount' => strval($amount_in_paisa),
    'purchase_order_id' => $order_id,
    'purchase_order_name' => $data['plan_name'],
    'customer_info' => array(
        'name' => $_SESSION['full_name'] ?? 'Customer',
        'email' => $_SESSION['email'] ?? 'test@test.com',
        'phone' => '9800000001'
    ),
    'amount_breakdown' => array(
        array(
            'label' => 'Plan Price',
            'amount' => $amount_in_paisa
        )
    ),
    'product_details' => array(
        array(
            'identity' => strval($data['plan_id']),
            'name' => $data['plan_name'],
            'total_price' => $amount_in_paisa,
            'quantity' => 1,
            'unit_price' => $amount_in_paisa
        )
    ),
    'merchant_name' => 'Project Desk',
    'merchant_username' => $_SESSION['username'] ?? 'project_desk_user',
    'merchant_extra' => json_encode([
        'plan_id' => $data['plan_id'],
        'user_id' => $_SESSION['id'],
        'order_id' => $order_id,
        'plan_name' => $data['plan_name'],
        'amount' => $amount_in_paisa
    ])
);

// Log the final payload for debugging
$log_file = 'payment_debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Payment Payload:\n" . 
    json_encode($payload, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Initialize cURL for payment
$ch = curl_init();

// Set SSL verification options
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

curl_setopt_array($ch, array(
    CURLOPT_URL => 'https://dev.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => array(
        'Authorization: Key 5bf2afad915247d1a28055fb7aaee102',
        'Content-Type: application/json'
    )
));

// Execute the request
$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);

// Log response for debugging
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Response:\n" . 
    "Status: $status_code\nBody: $response\nError: $err\n\n", FILE_APPEND);

curl_close($ch);

// Handle specific error cases
if ($status_code === 503) {
    error_log('Payment service temporarily unavailable (503)');
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'Payment service is temporarily unavailable. Please try again in a few minutes.',
        'debug_info' => [
            'status_code' => $status_code,
            'error' => $err,
            'order_id' => $order_id
        ]
    ]);
    exit;
}

if ($err) {
    error_log('Curl error: ' . $err);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to connect to payment service. Please try again.',
        'debug_info' => [
            'curl_error' => $err,
            'order_id' => $order_id
        ]
    ]);
    exit;
}

// Parse response and check for JSON decode errors
$response_data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON Decode Error: ' . json_last_error_msg());
    error_log('Raw Response: ' . $response);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process payment service response. Please try again.',
        'debug_info' => [
            'json_error' => json_last_error_msg(),
            'raw_response' => $response,
            'status_code' => $status_code
        ]
    ]);
    exit;
}

if ($status_code == 200 && isset($response_data['payment_url'])) {
    // Store payment data in session
    $_SESSION['payment_data'] = [
        'pidx' => $response_data['pidx'],
        'plan_id' => $data['plan_id'],
        'amount' => $amount_in_paisa,
        'order_id' => $order_id,
        'expires_at' => $response_data['expires_at'] ?? null,
        'expires_in' => $response_data['expires_in'] ?? null
    ];
    
    // Log successful initiation
    error_log("Payment initiated successfully. PIDX: {$response_data['pidx']}, Order ID: $order_id");
    
    // Return success with payment URL
    echo json_encode([
        'success' => true,
        'payment_url' => $response_data['payment_url'],
        'expires_at' => $response_data['expires_at'] ?? null,
        'expires_in' => $response_data['expires_in'] ?? null
    ]);
} else {
    // Log failure details
    error_log('Payment initiation failed. Status: ' . $status_code . ', Response: ' . $response);
    
    $error_message = isset($response_data['detail']) ? $response_data['detail'] : 'Payment initiation failed';
    http_response_code($status_code ?: 503);
    echo json_encode([
        'success' => false,
        'message' => $error_message,
        'debug_info' => [
            'status_code' => $status_code,
            'response' => $response_data,
            'curl_error' => $err,
            'order_id' => $order_id
        ]
    ]);
}
?> 