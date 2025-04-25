<nav class="bg-white shadow-sm border-end" style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 999; padding-top: 70px;">
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
    
    <?php if($_SESSION['role'] == "employee") { ?>
        <!-- Employee Navigation Bar -->
        <div class="p-3">
            <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2">Main Menu</div>
            <ul class="nav flex-column gap-1" id="navList">
                <li class="nav-item">
                    <a href="index.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-tachometer me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="my_task.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-tasks me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>My Tasks</span>
                    </a>
                </li>
                
                <!-- Notes Section -->
                <li class="nav-item">
                    <a href="notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-sticky-note me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Notes</span>
                    </a>
                </li>
                
                <!-- Support Section -->
                <li class="nav-item">
                    <a href="contact-support.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-life-ring me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Contact Support</span>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2">Account</div>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-user me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center text-danger">
                        <i class="fa fa-sign-out me-3" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    <?php } else { ?>
        <!-- Admin Navigation Bar -->
        <div class="p-3">
            <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2">Main Menu</div>
            <ul class="nav flex-column gap-1" id="navList">
                <li class="nav-item">
                    <a href="index.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-tachometer me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="user.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-users me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                
                <!-- Notes Section - Always visible but will redirect to plans if needed -->
                <li class="nav-item">
                    <a href="notes.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-sticky-note me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Notes</span>
                    </a>
                </li>
                
                <li class="nav-item mt-2">
                    <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2">Tasks</div>
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
                
                <li class="nav-item mt-2">
                    <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2">Support</div>
                </li>
                <li class="nav-item">
                    <a href="admin-support.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center">
                        <i class="fa fa-life-ring me-3 text-opacity-75" aria-hidden="true"></i>
                        <span>Support Tickets</span>
                    </a>
                </li>
                
                <li class="nav-item mt-2">
                    <div class="text-uppercase text-muted small fw-semibold ms-3 mb-2">Account</div>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link rounded-3 py-2 px-3 d-flex align-items-center text-danger">
                        <i class="fa fa-sign-out me-3" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    <?php } ?>
</nav>

<script>
    // Add active class to current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentLocation = window.location.pathname;
        const navLinks = document.querySelectorAll('#navList a');
        
        navLinks.forEach(link => {
            if(link.getAttribute('href') === currentLocation.substring(currentLocation.lastIndexOf('/') + 1)) {
                link.classList.add('active');
            }
        });
    });
</script>

<style>
    .nav-link {
        color: #495057;
        transition: all 0.2s ease;
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
</style>