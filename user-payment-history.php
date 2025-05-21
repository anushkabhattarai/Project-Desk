<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';

// Get user's payment history
$query = "SELECT s.*, p.name as plan_name, p.price
          FROM subscriptions s
          JOIN plans p ON s.plan_id = p.id
          WHERE s.user_id = :user_id
          ORDER BY s.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $_SESSION['id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Payment History | Project Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main style="margin-left: 250px; padding-top: 60px;">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">My Payment History</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payment History</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (empty($history)): ?>
                <div class="alert alert-info">No payment history found.</div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plan</th>
                                        <th>Price</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Purchase Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $payment): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($payment['plan_name']) ?></td>
                                            <td>₹<?= number_format($payment['price'], 2) ?></td>
                                            <td><?= date('M d, Y', strtotime($payment['start_date'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($payment['end_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $payment['status'] == 'active' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($payment['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y H:i', strtotime($payment['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
