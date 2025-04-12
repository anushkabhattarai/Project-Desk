<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Check if we have payment data in session
if (!isset($_SESSION['payment_data'])) {
    header("Location: plans.php");
    exit;
}

// Get the pidx from Khalti's response
$pidx = $_GET['pidx'] ?? null;
$payment_data = $_SESSION['payment_data'];

if (!$pidx || $pidx !== $payment_data['pidx']) {
    header("Location: plans.php?error=invalid_payment");
    exit;
}

// Initialize cURL for payment verification
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://dev.khalti.com/api/v2/epayment/lookup/' . $pidx,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Key 5bf2afad915247d1a28055fb7aaee102',
        'Content-Type: application/json'
    ]
]);

// Execute request and get response
$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);

// Debug logging
error_log('Khalti lookup response: ' . $response);
error_log('Khalti lookup status: ' . $status_code);

curl_close($ch);

// Check for cURL errors
if ($err) {
    error_log('Curl error in lookup: ' . $err);
    header("Location: plans.php?error=verification_failed&reason=connection_error");
    exit;
}

// Parse response
$response_data = json_decode($response, true);

if ($status_code == 200 && isset($response_data['status']) && $response_data['status'] === 'Completed') {
    // Verify the amount matches
    if (isset($response_data['total_amount']) && $response_data['total_amount'] == $payment_data['amount']) {
        // Payment verified successfully, create subscription
        
        // Database connection
        $host = "localhost";
        $username = "root";
        $password = "";
        $database = "task_management_db";

        try {
            $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set subscription dates (30 days from now)
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime('+30 days'));
            
            // Create subscription
            $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status, transaction_id) 
                                  VALUES (:user_id, :plan_id, :start_date, :end_date, 'active', :transaction_id)");
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->bindParam(':plan_id', $payment_data['plan_id']);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':transaction_id', $pidx);
            
            if ($stmt->execute()) {
                // Clear payment data from session
                unset($_SESSION['payment_data']);
                header("Location: notes.php?success=subscription_created");
            } else {
                header("Location: plans.php?error=subscription_failed");
            }
            
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            header("Location: plans.php?error=database_error");
        }
    } else {
        error_log('Payment amount mismatch. Expected: ' . $payment_data['amount'] . ', Got: ' . ($response_data['total_amount'] ?? 'unknown'));
        header("Location: plans.php?error=payment_amount_mismatch");
    }
} else {
    error_log('Payment verification failed. Response: ' . print_r($response_data, true));
    $error_reason = isset($response_data['detail']) ? '&reason=' . urlencode($response_data['detail']) : '';
    header("Location: plans.php?error=payment_incomplete" . $error_reason);
}
exit;
?> 