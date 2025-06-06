<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Set default values for session variables if not set
$_SESSION['username'] = $_SESSION['username'] ?? 'User';
$_SESSION['role'] = $_SESSION['role'] ?? 'employee';
?>

<nav class="bg-white shadow-sm border-end" style="width: 250px; height: calc(100vh - 60px); position: fixed; left: 0; top: 60px; z-index: 999; display: flex; flex-direction: column;">
    <!-- User Profile Section -->
    <div class="p-4 text-center border-bottom">
        <div class="position-relative d-inline-block mb-3">
            <img src="img/user.png" class="rounded-circle shadow-sm border border-2 border-light" width="80" height="80" alt="User Profile">
            <span class="position-absolute bottom-0 end-0 bg-<?php echo $_SESSION['role'] == 'admin' ? 'primary' : 'success'; ?> p-1 rounded-circle">
                <span class="visually-hidden">User status</span>
            </span>
        </div>
        <h6 class="mb-1 fw-semibold">@<?=$_SESSION['username']?></h6>
        <span class="badge bg-light text-secondary rounded-pill"><?=ucfirst($_SESSION['role'])?></span>
    </div>
    
    <div class="nav-scroll-container" style="flex: 1; overflow-y: auto; overflow-x: hidden;">
        <?php if($_SESSION['role'] == "employee") { ?>
            <!-- Employee Navigation Bar -->
            <div class="p-3">
                <!-- Main Menu Section -->
                <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Main Menu</div>
                <ul class="nav flex-column gap-1" id="navList">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-tachometer me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                       <a href="task_calendar.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-calendar me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Calendar</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_task.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-tasks me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>My Tasks</span>
                        </a>
                    </li>
                    
                    <!-- Notes Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2 d-flex align-items-center justify-content-between">
                            <span style="font-size: 0.7rem;">Notes</span>
                            <?php
                            try {
                                $userId = $_SESSION['id'];
                                $planQuery = "SELECT p.name FROM subscriptions s 
                                              JOIN plans p ON s.plan_id = p.id 
                                              WHERE s.user_id = :userId 
                                              AND s.status = 'active' 
                                              AND CURRENT_DATE BETWEEN s.start_date AND s.end_date";
                                
                                $stmt = $conn->prepare($planQuery);
                                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                                $stmt->execute();
                                $plan = $stmt->fetch(PDO::FETCH_ASSOC);

                                if($plan && $plan['name'] === 'Basic Plan') {
                                    echo '<img src="/Project_Desk/img/basic.png" alt="Basic Plan" style="width: 30px; height: 30px; margin-left: 10px;">';
                                }
                            } catch (Exception $e) {
                                error_log("Plan check error: " . $e->getMessage());
                            }
                            ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-sticky-note me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>All Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="private_notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-lock me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Private Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="shared_notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-share-alt me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Shared Notes</span>
                        </a>
                    </li>
                    
                    <!-- Users Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Users</div>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-user me-3 text-opacity-75" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Profile</span>
                        </a>
                    </li>

                    <!-- Support Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Support</div>
                    </li>
                    <li class="nav-item">
                        <a href="contact-support.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-life-ring me-3 text-opacity-75" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Contact Support</span>
                        </a>
                    </li>

                    <!-- Logout Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Account</div>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center text-danger">
                            <i class="fa fa-sign-out me-3" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        <?php } else { ?>
            <!-- Admin Navigation Bar -->
            <div class="p-3">
                <!-- Main Menu Section -->
                <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Main Menu</div>
                <ul class="nav flex-column gap-1" id="navList">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-tachometer me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Users Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Users</div>
                    </li>
                    <li class="nav-item">
                        <a href="user.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-users me-3 text-opacity-75" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Manage Users</span>
                        </a>
                    </li>

                    <!-- Subscriptions Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Subscriptions</div>
                    </li>
                    <li class="nav-item">
                        <a href="manage-subscriptions.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-credit-card me-3 text-opacity-75" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Active Subscriptions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-plans.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-cog me-3 text-opacity-75" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Manage Plans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="payment-history.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-history me-3 text-opacity-75" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Payment History</span>
                        </a>
                    </li>
                    
                    <!-- Notes Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2 d-flex align-items-center justify-content-between">
                            <span style="font-size: 0.7rem;">Notes</span>
                            <?php
                            try {
                                $userId = $_SESSION['id'];
                                $planQuery = "SELECT p.name FROM subscriptions s 
                                              JOIN plans p ON s.plan_id = p.id 
                                              WHERE s.user_id = :userId 
                                              AND s.status = 'active' 
                                              AND CURRENT_DATE BETWEEN s.start_date AND s.end_date";
                                
                                $stmt = $conn->prepare($planQuery);
                                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                                $stmt->execute();
                                $plan = $stmt->fetch(PDO::FETCH_ASSOC);

                                if($plan && $plan['name'] === 'Basic Plan') {
                                    echo '<img src="/Project_Desk/img/basic.png" alt="Basic Plan" style="width: 30px; height: 30px; margin-left: 10px;">';
                                }
                            } catch (Exception $e) {
                                error_log("Plan check error: " . $e->getMessage());
                            }
                            ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-sticky-note me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="shared_notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-share-alt me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Shared Notes</span>
                        </a>
                    </li>
                    
                    <!-- Tasks Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Tasks</div>
                    </li>
                    <li class="nav-item">
                        <a href="create_task.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-plus me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Create Task</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="tasks.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-tasks me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>All Tasks</span>
                        </a>
                    </li>
                    
                    <!-- Support Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Support</div>
                    </li>
                    <li class="nav-item">
                        <a href="admin-support.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                            <i class="fa fa-life-ring me-3 text-opacity-75" aria-hidden="true"></i>
                            <span>Support Tickets</span>
                        </a>
                    </li>
                    
                    <!-- Logout Section -->
                    <li class="nav-item mt-2">
                        <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2" style="font-size: 0.7rem;">Account</div>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center text-danger">
                            <i class="fa fa-sign-out me-3" aria-hidden="true"></i>
                            <span style="font-size: 0.85rem;">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        <?php } ?>
    </div>
</nav>

<script>
    // Add active class to current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentLocation = window.location.pathname;
        const currentPage = currentLocation.split('/').pop();
        const navLinks = document.querySelectorAll('#navList a');
        
        navLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === currentPage) {
                link.classList.add('active');
            }
        });

        // Add rotation animation for chevron icon
        const notesCollapse = document.getElementById('notesCollapse');
        const notesChevron = document.getElementById('notesChevron');
        
        notesCollapse.addEventListener('show.bs.collapse', function () {
            notesChevron.style.transform = 'rotate(180deg)';
        });
        
        notesCollapse.addEventListener('hide.bs.collapse', function () {
            notesChevron.style.transform = 'rotate(0deg)';
        });

        // Add smooth scroll to navigation links
        document.querySelectorAll('#navList a').forEach(link => {
            link.addEventListener('click', function(e) {
                const navContainer = document.querySelector('.nav-scroll-container');
                const targetPosition = this.offsetTop;
                navContainer.scrollTo({
                    top: targetPosition - 100,
                    behavior: 'smooth'
                });
            });
        });

        // Add toggle functionality
        function toggleNav() {
            document.querySelector('nav').classList.toggle('show');
        }
    });
</script>

<style>
    .nav-link {
        color: #495057;
        transition: all 0.2s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .nav-link:hover {
        background-color: rgba(13, 110, 253, 0.05);
        color: #0d6efd;
    }
    .nav-link.active {
        background-color: #0d6efd;
        color: white;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.15);
    }
    .nav-link.active i {
        color: white !important;
    }

    /* Note interface styles */
    .note-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .note-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        background-color: rgba(0, 123, 255, 0.02);
    }

    .note-actions {
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .note-card:hover .note-actions {
        opacity: 1;
    }

    /* Animation for new notes */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .new-note {
        animation: slideIn 0.3s ease forwards;
    }

    /* Responsive styles */
    @media (max-width: 991.98px) {
        nav {
            width: 0px !important;
            overflow: hidden;
        }
        body {
            margin-left: 0 !important;
        }
    }

    .transition-transform {
        transition: transform 0.3s ease;
    }

    /* Enhanced scrollbar and navigation styles */
    .nav-scroll-container {
        height: 100%;
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.2) transparent;
    }

    .nav-scroll-container::-webkit-scrollbar {
        width: 5px;
    }

    .nav-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .nav-scroll-container::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.2);
        border-radius: 20px;
        transition: background-color 0.3s;
    }

    .nav-scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0,0,0,0.3);
    }
</style>