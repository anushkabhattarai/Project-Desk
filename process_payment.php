<?php
// At the top of the script for debugging
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple logging function
function log_payment($message) {
    $log_file = __DIR__ . '/payment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(
        $log_file, 
        "[$timestamp] $message" . PHP_EOL,
        FILE_APPEND
    );
}

log_payment("=============== NEW PAYMENT CALLBACK ===============");
log_payment("FULL URL: " . $_SERVER['REQUEST_URI']);

// Validate URL structure
$expected_base_url = "/Project_Desk/process_payment.php";
if (strpos($_SERVER['REQUEST_URI'], $expected_base_url) === false) {
    log_payment("ERROR: Invalid URL structure. Expected base URL: $expected_base_url");
    echo "<h2>Payment Processing Error</h2>";
    echo "<p>Invalid payment URL. Please contact support.</p>";
    echo "<p><a href='plans.php'>Return to Plans</a></p>";
    exit;
}

// Validate URL parameters
$required_params = [
    'pidx' => 'Payment ID',
    'transaction_id' => 'Transaction ID',
    'amount' => 'Amount',
    'status' => 'Status',
    'purchase_order_id' => 'Purchase Order ID',
    'merchant_extra' => 'Merchant Extra Data'
];

$missing_params = [];
$invalid_params = [];


foreach ($required_params as $param => $description) {
    if (!isset($_GET[$param])) {
        $missing_params[] = $description;
        log_payment("Missing required parameter: $param ($description)");
    } else {
        // Validate parameter values
        switch ($param) {
            case 'amount':
                if (!is_numeric($_GET[$param])) {
                    $invalid_params[] = "$description must be numeric";
                    log_payment("Invalid amount: " . $_GET[$param]);
                }
                break;
            case 'status':
                if (!in_array($_GET[$param], ['Completed', 'Pending', 'Failed'])) {
                    $invalid_params[] = "$description must be one of: Completed, Pending, Failed";
                    log_payment("Invalid status: " . $_GET[$param]);
                }
                break;
            case 'merchant_extra':
                $decoded = json_decode(urldecode($_GET[$param]), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $invalid_params[] = "$description must be valid JSON";
                    log_payment("Invalid merchant_extra JSON: " . json_last_error_msg());
                }
                break;
        }
    }
}

if (!empty($missing_params) || !empty($invalid_params)) {
    $error_message = [];
    if (!empty($missing_params)) {
        $error_message[] = "Missing parameters: " . implode(', ', $missing_params);
    }
    if (!empty($invalid_params)) {
        $error_message[] = "Invalid parameters: " . implode(', ', $invalid_params);
    }
    
    log_payment("ERROR: " . implode('; ', $error_message));
    echo "<h2>Payment Processing Error</h2>";
    echo "<p>" . implode('<br>', $error_message) . "</p>";
    echo "<p><a href='plans.php'>Return to Plans</a></p>";
    exit;
}

// Log successful parameter validation
log_payment("All URL parameters validated successfully");
log_payment("Payment ID (pidx): " . $_GET['pidx']);
log_payment("Transaction ID: " . $_GET['transaction_id']);
log_payment("Amount: " . $_GET['amount']);
log_payment("Status: " . $_GET['status']);


// Decode and validate merchant_extra
if (isset($_GET['merchant_extra'])) {
    $merchant_extra = json_decode(urldecode($_GET['merchant_extra']), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_payment("ERROR: Invalid merchant_extra JSON: " . json_last_error_msg());
        echo "<h2>Payment Processing Error</h2>";
        echo "<p>Invalid payment data. Please contact support.</p>";
        echo "<p><a href='plans.php'>Return to Plans</a></p>";
        exit;
    }
    
    log_payment("Decoded merchant_extra: " . print_r($merchant_extra, true));

    
    // Validate required merchant_extra fields
    $required_extra = ['plan_id', 'user_id', 'order_id', 'plan_name', 'amount'];
    $missing_extra = [];
    
    foreach ($required_extra as $field) {
        if (!isset($merchant_extra[$field])) {
            $missing_extra[] = $field;
            log_payment("Missing required merchant_extra field: $field");
        } else {
            log_payment("Merchant extra field $field: " . $merchant_extra[$field]);
        }
    }

    
    if (!empty($missing_extra)) {
        log_payment("ERROR: Missing required merchant_extra fields: " . implode(', ', $missing_extra));
        echo "<h2>Payment Processing Error</h2>";
        echo "<p>Invalid payment data. Please contact support.</p>";
        echo "<p><a href='plans.php'>Return to Plans</a></p>";
        exit;
    }
}

// Basic user check
if (!isset($_SESSION['id'])) {
    log_payment("ERROR: No user session found");
    echo "<h1>Error: You must be logged in</h1>";
    echo "<p><a href='login.php'>Log in</a> to continue.</p>";
    exit;
}


// Check if we received the necessary parameters
if (isset($_GET['pidx'])) {
    $pidx = $_GET['pidx'];
    log_payment("PIDX received: " . $pidx);
    
    // Verify payment using Khalti lookup API
    $payload = array(
        'pidx' => $pidx
    );
    
    log_payment("Verifying payment with Khalti lookup API");
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://dev.khalti.com/api/v2/epayment/lookup/',
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
        ),
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0
    ));
    
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    
    curl_close($ch);
    
    log_payment("Khalti lookup response: " . $response);
    log_payment("Khalti lookup status code: " . $status_code);
    
    if ($err) {
        log_payment("Curl error during lookup: " . $err);
        echo "<h2>Payment Verification Error</h2>";
        echo "<p>Could not verify payment with Khalti. Please try again or contact support.</p>";
        echo "<p>Error: " . htmlspecialchars($err) . "</p>";
        echo "<p><a href='plans.php'>Return to Plans</a></p>";
        exit;
    }

    
    
    $lookup_data = json_decode($response, true);
    if (!$lookup_data) {
        log_payment("Failed to decode lookup response");
        echo "<h2>Payment Verification Error</h2>";
        echo "<p>Invalid response from payment gateway. Please try again or contact support.</p>";
        echo "<p><a href='plans.php'>Return to Plans</a></p>";
        exit;
    }

    if (isset($lookup_data['status'])) {
        $status = $lookup_data['status'];
        log_payment("Payment lookup status: " . $status);
        
        switch ($status) {
            case 'Completed':
                log_payment("Payment verification successful. Status: Completed");
                
                // Extract transaction details from lookup
                $transaction_id = $lookup_data['transaction_id'];
                $amount = $lookup_data['total_amount'];
                
                log_payment("Verified payment details: Transaction ID: $transaction_id, Amount: $amount");
                
                // Get plan ID from merchant_extra if available
                $plan_id = null;
                if (isset($_GET['merchant_extra'])) {
                    try {
                        $merchant_extra = json_decode(urldecode($_GET['merchant_extra']), true);
                        log_payment("Merchant extra data: " . print_r($merchant_extra, true));
                        if (isset($merchant_extra['plan_id'])) {
                            $plan_id = $merchant_extra['plan_id'];
                            log_payment("Got plan ID from merchant_extra: " . $plan_id);
                        }
                    } catch (Exception $e) {
                        log_payment("Error parsing merchant_extra: " . $e->getMessage());
                    }
                }
                
                // Fallback: try to get plan ID from purchase_order_id
                if (!$plan_id && isset($_GET['purchase_order_id'])) {
                    preg_match('/ORDER_(\d+)_/', $_GET['purchase_order_id'], $matches);
                    if (isset($matches[1])) {
                        $plan_id = $matches[1];
                        log_payment("Got plan ID from purchase_order_id: " . $plan_id);
                    }
                }
                
                if (!$plan_id) {
                    log_payment("ERROR: Could not determine plan ID");
                    echo "<h2>Payment Processing Error</h2>";
                    echo "<p>Could not determine which plan you purchased. Please contact support.</p>";
                    echo "<p><a href='plans.php'>Return to Plans</a></p>";
                    exit;
                }
                
                try {

                    
                    // Connect to database
                    $host = "localhost";
                    $username = "root";
                    $password = "";
                    $database = "task_management_db";
                    
                    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    log_payment("Database connection successful");
                    
                    // Check if subscription already exists
                    $stmt = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = :user_id AND plan_id = :plan_id AND status = 'active'");
                    $stmt->bindParam(':user_id', $_SESSION['id']);
                    $stmt->bindParam(':plan_id', $plan_id);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        log_payment("User already has an active subscription for this plan");
                        $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/notes.php?payment_status=already_subscribed";
                        header("Location: " . $redirect_url);
                        echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                        echo "<h2>Subscription Already Active</h2>";
                        echo "<p>You already have an active subscription for this plan.</p>";
                        echo "<p>Redirecting to your notes... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                        exit;
                    }
                    
                    // Start transaction
                    $conn->beginTransaction();
                    
                    // Create subscription
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d', strtotime('+30 days'));
                    
                    log_payment("Creating subscription: User ID: {$_SESSION['id']}, Plan ID: $plan_id, Trans ID: $transaction_id");
                    
                    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) 
                                           VALUES (:user_id, :plan_id, :start_date, :end_date, 'active')");
                    $stmt->bindParam(':user_id', $_SESSION['id']);
                    $stmt->bindParam(':plan_id', $plan_id);
                    $stmt->bindParam(':start_date', $start_date);
                    $stmt->bindParam(':end_date', $end_date);
                    
                    if ($stmt->execute()) {
                        $conn->commit();
                        log_payment("Subscription created successfully!");
                        
                        // Clear all buffers and redirect
                        while (ob_get_level()) ob_end_clean();
                        
                        log_payment("REDIRECTING TO NOTES PAGE NOW");
                        $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/notes.php?payment_status=success";
                        header("Location: " . $redirect_url);
                        echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                        echo "<h2>Payment Successful!</h2>";
                        echo "<p>Your subscription has been activated.</p>";
                        echo "<p>Redirecting to your notes... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                        exit;
                    } else {
                        throw new Exception("Error executing SQL statement");
                    }
                } catch (Exception $e) {
                    if (isset($conn) && $conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    
                    log_payment("ERROR: " . $e->getMessage());
                    echo "<h2>Payment Processing Error</h2>";
                    echo "<p>There was an error processing your payment.</p>";
                    echo "<p><a href='plans.php'>Return to Plans</a></p>";
                }
                break;
                
            case 'Pending':
                log_payment("Payment is pending. Hold service until verification.");
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=pending";
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Pending</h2>";
                echo "<p>Your payment is being processed and is currently pending.</p>";
                echo "<p>Please contact Khalti support for assistance.</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
                
            case 'Refunded':
                log_payment("Payment was refunded. No service should be provided.");
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=refunded";
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Refunded</h2>";
                echo "<p>This transaction has been refunded.</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
                
            case 'Expired':
                log_payment("Payment link expired. No payment was made.");
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=expired";
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Expired</h2>";
                echo "<p>The payment link has expired. Please try again with a new payment.</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
                
            case 'User canceled':
                log_payment("User canceled the payment.");
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=canceled";
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Canceled</h2>";
                echo "<p>You canceled this payment. Please try again if you wish to subscribe.</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
                
            case 'Partially Refunded':
                log_payment("Payment was partially refunded.");
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=partially_refunded";
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Partially Refunded</h2>";
                echo "<p>This transaction has been partially refunded.</p>";
                echo "<p>Please contact support for assistance with your subscription.</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
                
            case 'Initiated':
                log_payment("Payment has been initiated but not completed.");
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=initiated";
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Initiated</h2>";
                echo "<p>Your payment has been initiated but not yet completed.</p>";
                echo "<p>Please complete the payment process with Khalti.</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
                
            default:
                log_payment("Unknown payment status: " . $status);
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=unknown&error=" . urlencode($status);
                header("Location: " . $redirect_url);
                echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
                echo "<h2>Payment Status Unknown</h2>";
                echo "<p>The payment status is unknown. Please contact support.</p>";
                echo "<p>Status: " . htmlspecialchars($status) . "</p>";
                echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
                exit;
        }
    } else {
        log_payment("No status field in lookup response");
        $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/Project_Desk/plans.php?payment_status=error&error=" . urlencode("Could not determine payment status");
        header("Location: " . $redirect_url);
        echo "<meta http-equiv='refresh' content='0;url=$redirect_url'>";
        echo "<h2>Payment Verification Error</h2>";
        echo "<p>Could not determine payment status. Please contact support.</p>";
        echo "<p>Redirecting back to plans... <a href='$redirect_url'>Click here</a> if you're not redirected automatically.</p>";
        exit;
    }
} else {
    // No PIDX parameter found
    log_payment("No PIDX parameter found in URL");
    
    // Fallback to direct GET parameters if available
    if (isset($_GET['status']) && $_GET['status'] === 'Completed' && isset($_GET['transaction_id'])) {
        log_payment("Fallback: Using direct URL parameters. Status is Completed.");
        
        // Show fallback message but advise using proper verification
        echo "<h2>Payment Received</h2>";
        echo "<p>Your payment appears to be successful, but could not be verified with Khalti.</p>";
        echo "<p>Please contact support if your subscription is not activated.</p>";
        echo "<p><a href='notes.php'>Continue to Notes</a></p>";
        exit;
    } else {
        log_payment("Invalid payment parameters");
        echo "<h2>Invalid Payment Request</h2>";
        echo "<p>Missing required payment parameters.</p>";
        echo "<p><a href='plans.php'>Return to Plans</a></p>";
        exit;
    }
}
?> 