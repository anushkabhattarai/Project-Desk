<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    include "DB_connection.php";

    // Search parameter
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Pagination settings
    $items_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    // Base query with search
    $base_query = "FROM subscriptions s 
                   JOIN users u ON s.user_id = u.id 
                   JOIN plans p ON s.plan_id = p.id 
                   WHERE s.status = 'active'";
    
    // Add search condition if search term exists
    if (!empty($search)) {
        $base_query .= " AND (u.full_name LIKE :search OR u.username LIKE :search)";
    }

    // Count total results
    $count_query = "SELECT COUNT(*) as total " . $base_query;
    $stmt = $conn->prepare($count_query);
    if (!empty($search)) {
        $search_term = "%$search%";
        $stmt->bindParam(':search', $search_term);
    }
    $stmt->execute();
    $total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_items / $items_per_page);

    // Main query with pagination
    $query = "SELECT s.*, u.username, u.full_name, p.name as plan_name, p.price " . 
             $base_query . " ORDER BY s.created_at DESC LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($query);
    if (!empty($search)) {
        $stmt->bindParam(':search', $search_term);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Subscriptions | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-box {
            max-width: 300px;
            position: relative;
        }
        .search-box input {
            padding-left: 35px;
        }
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
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
    </style>
</head>
<body class="bg-white">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main style="margin-left: 250px; padding-top: 60px;">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Active Subscriptions</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Subscriptions</li>
                        </ol>
                    </nav>
                </div>
                
                <!-- Add search box -->
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search users..." 
                           value="<?= htmlspecialchars($search) ?>"
                           id="searchInput"
                           autofocus>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>User</th>
                                    <th>Plan</th>
                                    <th>Price</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($subscriptions): ?>
                                    <?php foreach($subscriptions as $sub): ?>
                                        <?php 
                                            $days_left = (strtotime($sub['end_date']) - time()) / (60 * 60 * 24);
                                            $status_class = $days_left <= 5 ? 'warning' : 'success';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="img/user.png" class="rounded-circle" width="32" height="32" alt="">
                                                    <div class="ms-3">
                                                        <h6 class="mb-0"><?= htmlspecialchars($sub['full_name']) ?></h6>
                                                        <small class="text-muted">@<?= htmlspecialchars($sub['username']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($sub['plan_name']) ?></td>
                                            <td>₹<?= number_format($sub['price'], 2) ?></td>
                                            <td><?= date('M d, Y', strtotime($sub['start_date'])) ?></td>
                                            <td><?= date('M d, Y', strtotime($sub['end_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $status_class ?>">
                                                    <?= ucfirst($sub['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($days_left > 0): ?>
                                                    <span class="badge bg-<?= $status_class ?>-subtle text-<?= $status_class ?>">
                                                        <?= floor($days_left) ?> days left
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        Expired
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <img src="img/empty-state.svg" alt="No subscriptions" style="width: 120px; opacity: 0.5;" class="mb-3">
                                            <h6 class="text-muted">No active subscriptions found</h6>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Subscription navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, min($page - 2, $total_pages - 4));
                        $end_page = min($total_pages, max(5, $page + 2));
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>">
                                    <?= $total_pages ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
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
    <script>
        // Improved search functionality with debouncing
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value;
            
            // Add debouncing to prevent too many requests
            searchTimeout = setTimeout(() => {
                window.location.href = 'manage-subscriptions.php?page=1&search=' + encodeURIComponent(searchTerm);
            }, 500); // Wait 500ms after user stops typing
        });

        // Keep focus on search input after page reload
        window.addEventListener('load', () => {
            searchInput.focus();
            // Place cursor at the end of the text
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);
        });
    </script>
</body>
</html>
<?php 
} else {
    header("Location: login.php");
    exit;
}
?>
