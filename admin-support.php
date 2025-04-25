<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Support.php";
    include "app/Model/User.php";
    
    // Get filter values
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Get tickets based on filters
    $tickets = get_all_support_tickets($conn, $status_filter, $search);
    
    // Count tickets by status
    $open_count = count_tickets_by_status($conn, 'open');
    $resolved_count = count_tickets_by_status($conn, 'resolved');
    $all_count = $open_count + $resolved_count;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Support Tickets | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-white">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area with margin-left to account for sidebar width -->
    <main style="margin-left: 250px; padding-top: 70px;">
        <div class="container-fluid px-4 py-3">
            <!-- Header Area -->
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Support Tickets</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item active">Support Tickets</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo stripcslashes($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo stripcslashes($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3 bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fa fa-ticket text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">All Tickets</h6>
                                <h4 class="mb-0"><?= $all_count ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3 bg-success bg-opacity-10 p-3 rounded">
                                <i class="fa fa-comments text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Open Tickets</h6>
                                <h4 class="mb-0"><?= $open_count ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3 bg-info bg-opacity-10 p-3 rounded">
                                <i class="fa fa-check-circle text-info fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Resolved Tickets</h6>
                                <h4 class="mb-0"><?= $resolved_count ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters and Search -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status Filter</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Tickets</option>
                                <option value="open" <?= $status_filter == 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="resolved" <?= $status_filter == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search by subject or username" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" style="background-color: #1a237e; border-color: #1a237e;">
                                <i class="fa fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Support Tickets Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-life-ring me-2 text-primary"></i>Support Tickets
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($tickets && count($tickets) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Subject</th>
                                        <th>User</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 0;
                                    foreach ($tickets as $ticket) { 
                                        $user = get_user_by_id($conn, $ticket['user_id']);
                                    ?>
                                        <tr>
                                            <td><?= ++$i ?></td>
                                            <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="img/user.png" class="rounded-circle me-2" width="32" height="32">
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold"><?= $user['full_name'] ?></h6>
                                                        <small class="text-muted">@<?= $user['username'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($ticket['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $ticket['status'] == 'open' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($ticket['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="admin-view-ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                                <?php if ($ticket['status'] == 'open') { ?>
                                                    <a href="app/resolve-ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fa fa-check"></i> Resolve
                                                    </a>
                                                <?php } else { ?>
                                                    <a href="app/reopen-ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="fa fa-refresh"></i> Reopen
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="text-center p-5">
                            <img src="img/empty-state.svg" alt="No tickets found" style="width: 200px; opacity: 0.5;" class="mb-3">
                            <h5 class="text-muted">No support tickets found</h5>
                            <p class="text-muted">No tickets match your current filters</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        // Make support link active in the nav
        document.addEventListener('DOMContentLoaded', function() {
            var navItems = document.querySelectorAll('#navList a');
            navItems.forEach(function(item) {
                if (item.getAttribute('href') === 'admin-support.php') {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
<?php } else { 
   $em = "You don't have permission to access this page";
   header("Location: login.php?error=$em");
   exit();
}
?> 