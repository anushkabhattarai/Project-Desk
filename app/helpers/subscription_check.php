<?php
function hasActiveSubscription($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM subscriptions 
                           WHERE user_id = ? 
                           AND status = 'active' 
                           AND end_date >= CURRENT_DATE");
    $stmt->execute([$user_id]);
    return $stmt->fetch() !== false;
}

function redirectToPlans($highlight = '') {
    $url = 'plans.php';
    if ($highlight) {
        $url .= '?highlight=' . urlencode($highlight);
    }
    header("Location: " . $url);
    exit;
}
