<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    include "DB_connection.php";

    // Pagination settings
    $users_per_page = 4;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $users_per_page;

    // Get total users count
    $count_query = "SELECT COUNT(DISTINCT u.id) as total 
                    FROM users u 
                    JOIN subscriptions s ON u.id = s.user_id";
    $total_users = $conn->query($count_query)->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_users / $users_per_page);

    // Modify main query to include pagination
    $query = "SELECT u.id, u.full_name, u.username,
                     s.start_date, s.end_date, s.status,
                     p.name as plan_name, p.price,
                     s.created_at as subscription_date
              FROM users u
              LEFT JOIN subscriptions s ON u.id = s.user_id
              LEFT JOIN plans p ON s.plan_id = p.id
              WHERE s.id IS NOT NULL
              GROUP BY u.id
              ORDER BY u.full_name
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $users_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by user
    $userHistory = [];
    foreach ($history as $record) {
        $userId = $record['id'];
        if (!isset($userHistory[$userId])) {
            $userHistory[$userId] = [
                'user_info' => [
                    'name' => $record['full_name'],
                    'username' => $record['username']
                ],
                'subscriptions' => []
            ];
        }
        $userHistory[$userId]['subscriptions'][] = [
            'plan_name' => $record['plan_name'],
            'price' => $record['price'],
            'start_date' => $record['start_date'],
            'end_date' => $record['end_date'],
            'status' => $record['status'],
            'subscription_date' => $record['subscription_date']
        ];
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment History | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pagination .page-link {
            color: #0d6efd;
            border: none;
            margin: 0 3px;
            border-radius: 6px;
            padding: 8px 16px;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            color: white;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
            color: #0d6efd;
        }
        .pagination .page-link:focus {
            box-shadow: none;
        }

        /* Add breadcrumb styles */
        .breadcrumb-item a {
            color: #0a58ca !important;
            text-decoration: none;
        }
        .breadcrumb-item a:hover {
            color: #0a58ca !important;
        }
    </style>
</head>
<body class="bg-white">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main style="margin-left: 250px; padding-top: 60px;">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Payment History</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payment History</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (empty($userHistory)): ?>
                <div class="alert alert-info">No payment history found.</div>
            <?php else: ?>
                <?php foreach ($userHistory as $userId => $data): ?>
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-user me-2 text-primary"></i>
                                <?= htmlspecialchars($data['user_info']['name']) ?>
                                <small class="text-muted">(@<?= htmlspecialchars($data['user_info']['username']) ?>)</small>
                            </h5>
                        </div>
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
                                            <th>Subscription Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['subscriptions'] as $sub): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($sub['plan_name']) ?></td>
                                                <td>₹<?= number_format($sub['price'], 2) ?></td>
                                                <td><?= date('M d, Y', strtotime($sub['start_date'])) ?></td>
                                                <td><?= date('M d, Y', strtotime($sub['end_date'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $sub['status'] == 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($sub['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y H:i', strtotime($sub['subscription_date'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <nav aria-label="Payment history navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page-1 ?>" aria-label="Previous">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, min($page - 2, $total_pages - 4));
                        $end_page = min($total_pages, max(5, $page + 2));
                        
                        if($start_page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                            <?php if($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if($end_page < $total_pages): ?>
                            <?php if($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page+1 ?>" aria-label="Next">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
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
