<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";
    include "app/Model/Support.php";
    
    // Get user's tickets
    $tickets = get_user_support_tickets($conn, $_SESSION['id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Support | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-white">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area with margin-left to account for sidebar width -->
    <main style="margin-left: 250px; padding-top: 60px;">
        <div class="container-fluid px-4 py-3">
            <!-- Header Area -->
            <div class="mb-4">
                <h4 class="mb-1">Contact Support</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Contact Support</li>
                    </ol>
                </nav>
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

            <div class="row">
                <!-- New Support Ticket Form -->
                <div class="col-md-5 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-question-circle me-2 text-primary"></i>Submit a New Support Request
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="app/create-support-ticket.php">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="subject" 
                                           name="subject" 
                                           placeholder="Enter a brief subject for your issue"
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" 
                                              id="message" 
                                              name="message" 
                                              rows="5" 
                                              placeholder="Describe your issue in detail"
                                              required></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" style="background-color: #1a237e; border-color: #1a237e;">
                                        <i class="fa fa-paper-plane me-2"></i>Submit Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Previous Support Tickets -->
                <div class="col-md-7 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-history me-2 text-primary"></i>Your Support Tickets
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($tickets && count($tickets) > 0) { ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Subject</th>
                                                <th>Created</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tickets as $index => $ticket) { ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                                    <td><?= date('M d, Y', strtotime($ticket['created_at'])) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $ticket['status'] == 'open' ? 'success' : 'secondary' ?>">
                                                            <?= ucfirst($ticket['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="view-ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="text-center p-4">
                                    <img src="img/empty-state.svg" alt="No tickets found" style="width: 150px; opacity: 0.5;" class="mb-3">
                                    <h6 class="text-muted">No support tickets found</h6>
                                    <p class="text-muted small">Your previous support requests will appear here</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
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
                if (item.getAttribute('href') === 'contact-support.php') {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
<?php } else { 
   $em = "You must login first";
   header("Location: login.php?error=$em");
   exit();
}
?> 