<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Support.php";
    include "app/Model/User.php";
    
    // Check if ticket ID exists
    if (!isset($_GET['id'])) {
        header("Location: admin-support.php");
        exit();
    }
    
    $ticket_id = $_GET['id'];
    
    // Get ticket details
    $ticket = get_ticket_by_id($conn, $ticket_id);
    
    // Check if ticket exists
    if (!$ticket) {
        header("Location: admin-support.php?error=Ticket not found");
        exit();
    }
    
    // Get all replies for this ticket
    $replies = get_ticket_replies($conn, $ticket_id);
    
    // Get ticket creator info
    $ticket_user = get_user_by_id($conn, $ticket['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Ticket Details | Project Desk</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-container {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .message {
            border-radius: 1rem;
            max-width: 80%;
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .message.user-message {
            background-color: #f0f2ff;
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
        }
        
        .message.admin-message {
            background-color: #e9ecef;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            position: absolute;
            bottom: -1.2rem;
        }
        
        .user-message .message-time {
            left: 0;
        }
        
        .admin-message .message-time {
            right: 0;
        }
    </style>
</head>
<body class="bg-white">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area with margin-left to account for sidebar width -->
    <main style="margin-left: 250px; padding-top: 70px;">
        <div class="container-fluid px-4 py-3">
            <!-- Header Area -->
            <div class="mb-4">
                <h4 class="mb-1">Support Ticket #<?= $ticket_id ?></h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="admin-support.php" class="text-decoration-none">Support Tickets</a></li>
                        <li class="breadcrumb-item active">Ticket #<?= $ticket_id ?></li>
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
                <!-- Ticket and User Information -->
                <div class="col-md-4 mb-4">
                    <!-- Ticket Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-info-circle me-2 text-primary"></i>Ticket Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-<?= $ticket['status'] == 'open' ? 'success' : 'secondary' ?> rounded-circle p-1 me-2"></div>
                                    <h6 class="mb-0"><?= ucfirst($ticket['status']) ?></h6>
                                </div>
                                
                                <div>
                                    <?php if ($ticket['status'] == 'open') { ?>
                                        <a href="app/resolve-ticket.php?id=<?= $ticket_id ?>" class="btn btn-sm btn-success">
                                            <i class="fa fa-check-circle me-1"></i> Mark as Resolved
                                        </a>
                                    <?php } else { ?>
                                        <a href="app/reopen-ticket.php?id=<?= $ticket_id ?>" class="btn btn-sm btn-warning">
                                            <i class="fa fa-refresh me-1"></i> Reopen Ticket
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-3"><?= htmlspecialchars($ticket['subject']) ?></h5>
                            
                            <div class="d-flex align-items-center mb-3 text-muted">
                                <i class="fa fa-calendar me-2"></i>
                                <span>Created: <?= date('M d, Y h:i A', strtotime($ticket['created_at'])) ?></span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3 text-muted">
                                <i class="fa fa-clock-o me-2"></i>
                                <span>Last Updated: <?= date('M d, Y h:i A', strtotime($ticket['updated_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Information -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-user me-2 text-primary"></i>User Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="img/user.png" class="rounded-circle shadow-sm border" width="80" height="80" alt="User Profile">
                            </div>
                            
                            <h5 class="text-center mb-3"><?= $ticket_user['full_name'] ?></h5>
                            
                            <div class="d-flex align-items-center mb-3">
                                <i class="fa fa-user me-3 text-muted"></i>
                                <div>
                                    <div class="text-muted small">Username</div>
                                    <div>@<?= $ticket_user['username'] ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <i class="fa fa-id-badge me-3 text-muted"></i>
                                <div>
                                    <div class="text-muted small">Role</div>
                                    <div><?= ucfirst($ticket_user['role']) ?></div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <i class="fa fa-calendar me-3 text-muted"></i>
                                <div>
                                    <div class="text-muted small">Member Since</div>
                                    <div><?= date('M d, Y', strtotime($ticket_user['created_at'])) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Conversation Thread -->
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-comments me-2 text-primary"></i>Conversation
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chat-container px-2 py-3 mb-4">
                                <?php if (count($replies) > 0) { 
                                    foreach ($replies as $reply) {
                                        $is_user_message = $reply['role'] == 'user';
                                        $msg_class = $is_user_message ? 'user-message' : 'admin-message';
                                        $user_name = $is_user_message ? $ticket_user['username'] : 'Support Team';
                                ?>
                                    <div class="message <?= $msg_class ?> p-3">
                                        <div class="message-content">
                                            <?= nl2br(htmlspecialchars($reply['message'])) ?>
                                        </div>
                                        <small class="message-time">
                                            <?= date('M d, Y h:i A', strtotime($reply['created_at'])) ?>
                                            by <?= $user_name ?>
                                        </small>
                                    </div>
                                <?php }
                                } else { ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fa fa-comments-o fa-3x mb-3"></i>
                                        <p>No messages yet</p>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <!-- Reply Form -->
                            <form method="POST" action="app/add-admin-reply.php">
                                <div class="mb-3">
                                    <label for="message" class="form-label">Reply to User</label>
                                    <textarea class="form-control" 
                                              id="message" 
                                              name="message" 
                                              rows="4" 
                                              placeholder="Type your reply here..."
                                              required></textarea>
                                </div>
                                
                                <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
                                
                                <div class="d-flex justify-content-between">
                                    <a href="admin-support.php" class="btn btn-outline-secondary">
                                        <i class="fa fa-arrow-left me-2"></i>Back to Tickets
                                    </a>
                                    <button type="submit" class="btn btn-primary" style="background-color: #1a237e; border-color: #1a237e;">
                                        <i class="fa fa-paper-plane me-2"></i>Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        // Scroll chat to bottom
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
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