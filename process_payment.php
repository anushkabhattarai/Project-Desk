<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom logging function
function custom_log($message) {
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $log_file = $log_dir . '/payment.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Log the start of payment processing
custom_log("Payment processing started. Request data: " . print_r($_GET, true));
custom_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    custom_log("User not logged in during payment processing");
    header("Location: login.php");
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "task_management_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    custom_log("Database connection successful");
} catch(PDOException $e) {
    custom_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Verify payment status
if (isset($_GET['status']) && $_GET['status'] === 'Completed') {
    custom_log("Payment status is Completed");
    
    try {
        // Start transaction
        $conn->beginTransaction();
        custom_log("Transaction started");
        
        // Get payment details
        $pidx = $_GET['pidx'];
        $transaction_id = $_GET['transaction_id'];
        $amount = $_GET['amount'];
        $purchase_order_id = $_GET['purchase_order_id'];
        
        custom_log("Payment details: " . print_r([
            'pidx' => $pidx,
            'transaction_id' => $transaction_id,
            'amount' => $amount,
            'purchase_order_id' => $purchase_order_id
        ], true));
        
        // Extract plan ID from purchase order ID
        preg_match('/ORDER_(\d+)_/', $purchase_order_id, $matches);
        $plan_id = isset($matches[1]) ? $matches[1] : null;
        
        custom_log("Extracted plan ID: " . $plan_id);
        
        if (!$plan_id) {
            throw new Exception("Invalid purchase order ID");
        }
        
        // Verify if payment already exists
        $stmt = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = :user_id AND plan_id = :plan_id AND status = 'active'");
        $stmt->bindParam(':user_id', $_SESSION['id']);
        $stmt->bindParam(':plan_id', $plan_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("You already have an active subscription for this plan");
        }
        
        // Calculate subscription dates
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+30 days'));
        
        custom_log("Creating subscription with dates: start=$start_date, end=$end_date");
        
        // Create subscription
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status, transaction_id) 
                               VALUES (:user_id, :plan_id, :start_date, :end_date, 'active', :transaction_id)");
        $stmt->bindParam(':user_id', $_SESSION['id']);
        $stmt->bindParam(':plan_id', $plan_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':transaction_id', $transaction_id);
        
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            custom_log("Subscription creation failed: " . print_r($error, true));
            throw new Exception("Failed to create subscription: " . $error[2]);
        }
        
        // Commit transaction
        $conn->commit();
        custom_log("Transaction committed successfully");
        
        custom_log("Subscription created successfully for user " . $_SESSION['id']);
        
        // Redirect to notes page with success status
        header("Location: notes.php?payment_status=success");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
            custom_log("Transaction rolled back due to error");
        }
        
        custom_log("Error processing payment: " . $e->getMessage());
        custom_log("Error trace: " . $e->getTraceAsString());
        
        // Show error on the page for debugging
        echo "Error: " . $e->getMessage();
        echo "<br>Please check the payment.log file in the logs directory for more details.";
        exit;
    }
} else {
    custom_log("Payment status is not Completed. Status: " . ($_GET['status'] ?? 'not set'));
    custom_log("Full GET data: " . print_r($_GET, true));
    
    // Show error on the page for debugging
    echo "Payment status is not Completed. Status: " . ($_GET['status'] ?? 'not set');
    echo "<br>Please check the payment.log file in the logs directory for more details.";
    exit;
}
?> 