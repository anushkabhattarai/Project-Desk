<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// If user is admin, redirect to notes.php (admins get full access)
if ($_SESSION['role'] == 'admin') {
    header("Location: notes.php");
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
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user already has an active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = :user_id AND status = 'active' AND end_date >= CURRENT_DATE");
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->execute();
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

// If user has an active subscription, redirect to notes.php
if ($subscription) {
    header("Location: notes.php");
    exit;
}

// Fetch available plans
$stmt = $conn->prepare("SELECT * FROM plans ORDER BY price ASC");
$stmt->execute();
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle plan selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_plan'])) {
    $plan_id = (int)$_POST['plan_id'];
    
    // Verify plan exists
    $stmt = $conn->prepare("SELECT * FROM plans WHERE id = :id");
    $stmt->bindParam(':id', $plan_id);
    $stmt->execute();
    $selected_plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selected_plan) {
        // In a real application, you would process payment here
        // For this demo, we'll just create a subscription
        
        // Set subscription dates (30 days from now)
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+30 days'));
        
        // Create subscription
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status) 
                               VALUES (:user_id, :plan_id, :start_date, :end_date, 'active')");
        $stmt->bindParam(':user_id', $_SESSION['id']);
        $stmt->bindParam(':plan_id', $plan_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        if ($stmt->execute()) {
            // Redirect to notes page
            header("Location: notes.php");
            exit;
        } else {
            $error_message = "Error creating subscription. Please try again.";
        }
    } else {
        $error_message = "Invalid plan selected.";
    }
}

$title = "Select a Plan";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Khalti Styling -->
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.22.0.0.0/khalti-checkout.iffe.js"></script>
</head>
<body class="bg-light">
    <?php include "inc/header.php"; ?>
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area -->
    <main style="margin-left: 250px; padding-top: 70px;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold">Choose Your Notes Plan</h2>
                        <p class="text-muted">Select a plan that best fits your needs</p>
                    </div>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row g-4">
                        <?php foreach ($plans as $plan): ?>
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <div class="text-center mb-4">
                                            <h3 class="card-title fw-bold"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                            <div class="my-3">
                                                <span class="display-4 fw-bold">₹<?php echo number_format($plan['price'], 2); ?></span>
                                                <span class="text-muted">/month</span>
                                            </div>
                                            <p class="text-muted"><?php echo htmlspecialchars($plan['description']); ?></p>
                                        </div>
                                        
                                        <ul class="list-unstyled mb-4">
                                            <li class="mb-2">
                                                <i class="fa fa-check text-success me-2"></i>
                                                <span>Up to <?php echo $plan['note_limit']; ?> notes</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fa fa-check text-success me-2"></i>
                                                <span>Up to <?php echo $plan['private_note_limit']; ?> private notes</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fa fa-check text-success me-2"></i>
                                                <span>Share with up to <?php echo $plan['share_limit']; ?> users</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fa fa-check text-success me-2"></i>
                                                <span>Secure payment via Khalti</span>
                                            </li>
                                        </ul>
                                        
                                        <div class="text-center">
                                            <button class="btn btn-primary btn-lg w-100 payment-button" 
                                                    data-plan-id="<?php echo $plan['id']; ?>"
                                                    data-plan-name="<?php echo htmlspecialchars($plan['name']); ?>"
                                                    data-amount="<?php echo $plan['price']; ?>">
                                                Select Plan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-5">
                        <p class="text-muted">All plans include a 30-day subscription period</p>
                        <p class="text-muted">Need help? <a href="#">Contact support</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Khalti Integration Script -->
    <script>
        // Check if user is logged in
        function checkLoginStatus() {
            return fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        window.location.href = 'login.php';
                        return false;
                    }
                    return true;
                })
                .catch(error => {
                    console.error('Session check failed:', error);
                    return false;
                });
        }

        // Format expiration time
        function formatExpirationTime(expiresIn) {
            const minutes = Math.floor(expiresIn / 60);
            const seconds = expiresIn % 60;
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        document.querySelectorAll('.payment-button').forEach(button => {
            button.addEventListener('click', async function() {
                try {
                    // Check login status first
                    const isLoggedIn = await checkLoginStatus();
                    if (!isLoggedIn) {
                        return; // Will redirect to login page
                    }

                    // Disable the button to prevent double clicks
                    this.disabled = true;
                    this.innerHTML = 'Processing...';

                    // Get all required data from button attributes
                    const planId = this.dataset.planId;
                    const planName = this.dataset.planName;
                    const amount = parseInt(this.dataset.amount);

                    // Validate required parameters
                    if (!planId || !planName || isNaN(amount)) {
                        alert('Invalid plan data. Please try again.');
                        this.disabled = false;
                        this.innerHTML = 'Select Plan';
                        return;
                    }

                    // Validate minimum amount
                    if (amount < 10) {
                        alert('Amount should be greater than Rs. 10');
                        this.disabled = false;
                        this.innerHTML = 'Select Plan';
                        return;
                    }

                    // Prepare request data
                    const requestData = {
                        plan_id: planId,
                        plan_name: planName,
                        amount: amount
                    };

                    console.log('Sending payment data:', requestData);

                    const response = await fetch('initiate_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(requestData),
                        credentials: 'same-origin' // Include cookies for session
                    });

                    // Log the raw response for debugging
                    const responseText = await response.text();
                    console.log('Raw response:', responseText);

                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        console.error('Failed to parse response:', e);
                        throw new Error('Invalid response from server');
                    }

                    console.log('Parsed response:', data);

                    // Handle different response status codes
                    if (response.status === 401) {
                        // Unauthorized - redirect to login
                        window.location.href = 'login.php';
                        return;
                    }

                    if (data.success && data.payment_url) {
                        // Show expiration time if available
                        if (data.expires_in) {
                            const expirationTime = formatExpirationTime(data.expires_in);
                            const proceed = confirm(`Payment link will expire in ${expirationTime} minutes. Proceed to payment?`);
                            if (!proceed) {
                                this.disabled = false;
                                this.innerHTML = 'Select Plan';
                                return;
                            }
                        }

                        // Redirect to Khalti payment page
                        window.location.href = data.payment_url;
                    } else {
                        // Re-enable the button
                        this.disabled = false;
                        this.innerHTML = 'Select Plan';

                        // Show error message with retry option for 503 errors
                        const errorMessage = data.message || 'Failed to initiate payment. Please try again.';
                        if (response.status === 503) {
                            const retry = confirm(`${errorMessage}\n\nWould you like to try again?`);
                            if (retry) {
                                this.click(); // Retry the payment
                                return;
                            }
                        } else {
                            alert(errorMessage);
                        }

                        // Log detailed error if available
                        if (data.debug_info) {
                            console.error('Payment Error Details:', data.debug_info);
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    
                    // Re-enable the button
                    this.disabled = false;
                    this.innerHTML = 'Select Plan';
                    
                    // Show appropriate error message
                    const errorMessage = error.message.includes('Failed to fetch') 
                        ? 'Unable to connect to the payment service. Please check your internet connection and try again.'
                        : 'An unexpected error occurred. Please try again later.';
                    
                    alert(errorMessage);
                }
            });
        });
    </script>
</body>
</html> 