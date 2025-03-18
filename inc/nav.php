<nav class="shadow-sm" style="width: 250px; min-height: 100vh; background-color: #1A237E;">
    <!-- User Profile Section -->
    <div class="p-4 text-center border-bottom border-light">
        <img src="img/user.png" class="rounded-circle mb-3" width="80" height="80" alt="User Profile">
        <h6 class="mb-0 text-white">@<?=$_SESSION['username']?></h6>
    </div>
    
    <?php if($_SESSION['role'] == "employee") { ?>
        <!-- Employee Navigation Bar -->
        <div class="p-3">
            <ul class="nav flex-column" id="navList">
                <li class="nav-item mb-2">
                    <a href="index.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-tachometer me-2" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="my_task.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-tasks me-2" aria-hidden="true"></i>
                        <span>My Task</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="profile.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-user me-2" aria-hidden="true"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="notifications.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-bell me-2" aria-hidden="true"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-sign-out me-2" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    <?php } else { ?>
        <!-- Admin Navigation Bar -->
        <div class="p-3">
            <ul class="nav flex-column" id="navList">
                <li class="nav-item mb-2">
                    <a href="index.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-tachometer me-2" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="user.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-users me-2" aria-hidden="true"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="create_task.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-plus me-2" aria-hidden="true"></i>
                        <span>Create Task</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="tasks.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-tasks me-2" aria-hidden="true"></i>
                        <span>All Tasks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-white rounded py-2 px-3">
                        <i class="fa fa-sign-out me-2" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    <?php } ?>
</nav>

<style>
    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
    }
    .nav-link i {
        color: white;
    }
</style>