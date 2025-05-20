<?php
session_start();
if (isset($_SESSION['id'])) {
    include "DB_connection.php";

    // Get user's payment history
    $query = "SELECT s.*, p.name as plan_name, p.price
              FROM subscriptions s 
              JOIN plans p ON s.plan_id = p.id 
              WHERE s.user_id = :user_id 
              ORDER BY s.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['id']]);
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Payment History | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #ffffff;
        }
        .custom-card {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.8px;
            color: #6c757d;
            padding: 1.2rem 1rem;
            background: rgba(248, 249, 250, 0.8);
        }
        .table td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            font-size: 0.95rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        .table tr:hover {
            background: rgba(248, 249, 250, 0.5);
            transition: all 0.3s ease;
        }
        .badge {
            padding: 0.6em 1.2em;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }
        .badge:hover {
            transform: scale(1.05);
        }
        .empty-state {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
        }
        .empty-state img {
            width: 180px;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        .empty-state img:hover {
            transform: scale(1.05);
        }
        .breadcrumb-item a {
            color: #0d6efd;
            transition: all 0.3s ease;
        }
        .breadcrumb-item a:hover {
            color: #0a58ca;
            text-decoration: none;
        }
        .btn-primary {
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.15);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.25);
        }
        .page-title {
            color: #000000;
            font-weight: 700;
            background: none;
            -webkit-background-clip: initial;
            background-clip: initial;
        }
    </style>
</head>
<body>
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main style="margin-left: 250px; padding-top: 60px;">
        <div class="container-fluid px-4 py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="page-title mb-2">My Payment History</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payment History</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="custom-card">
                <div class="card-body p-0">
                    <?php if ($subscriptions): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Plan</th>
                                        <th>Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th class="pe-4">Purchase Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($subscriptions as $sub): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-semibold"><?= htmlspecialchars($sub['plan_name']) ?></span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-primary">₹<?= number_format($sub['price'], 2) ?></span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($sub['start_date'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($sub['end_date'])) ?></td>
                                            <td>
                                                <span class="badge rounded-pill <?= $sub['status'] == 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                                                    <?= ucfirst($sub['status']) ?>
                                                </span>
                                            </td>
                                            <td class="pe-4 text-muted"><?= date('M d, Y H:i', strtotime($sub['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state text-center">
                            <img src="img/empty-state.svg" alt="No payment history" class="img-fluid">
                            <h5 class="fw-bold mb-3">No Payment History Found</h5>
                            <p class="text-muted mb-4">You haven't made any payments yet. Explore our plans to get started.</p>
                            <a href="plans.php" class="btn btn-primary px-4 py-2 rounded-pill">
                                <i class="fa fa-credit-card me-2"></i>View Plans
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
} else {
    header("Location: login.php");
    exit;
}
?>
