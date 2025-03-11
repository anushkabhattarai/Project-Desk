<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --bg-light: #f8f9ff;
            --border-color: #e9ecef;
            --sidebar-bg: #2A2F3C;
            --sidebar-width: 240px;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            padding: 1rem;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }

        .sidebar-header span {
            color: var(--primary-color);
        }

        #navList {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #navList li {
            margin-bottom: 0.5rem;
        }

        #navList li a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #navList li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        #navList li.active a,
        #navList li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: 800px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title a {
            font-size: 0.875rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Form Styles */
        .add-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
            outline: none;
        }

        .password-requirements {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.75rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .password-requirements ul {
            padding-left: 1.5rem;
            margin-bottom: 0;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
        }

        .btn-add-user {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add-user:hover {
            background: #5254cc;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Project<span>Desk</span></h1>
        </div>
        <ul id="navList">
            <li>
                <a href="index.php">
                    <i class="fa fa-dashboard"></i>
                    Dashboard
                </a>
            </li>
            <li class="active">
                <a href="user.php">
                    <i class="fa fa-users"></i>
                    Manage Users
                </a>
            </li>
            <li>
                <a href="create_task.php">
                    <i class="fa fa-plus"></i>
                    Create Task
                </a>
            </li>
            <li>
                <a href="tasks.php">
                    <i class="fa fa-tasks"></i>
                    All Tasks
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fa fa-sign-out"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h4 class="page-title">
                Add New User
                <a href="user.php">
                    <i class="fa fa-arrow-left"></i> Back to Users
                </a>
            </h4>
        </div>

        <div class="add-form">
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger">
                    <?php echo stripcslashes($_GET['error']); ?>
                </div>
            <?php } ?>

            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success">
                    <?php echo stripcslashes($_GET['success']); ?>
                </div>
            <?php } ?>

            <form method="POST" action="app/add-user.php">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" 
                           name="full_name" 
                           class="form-control" 
                           placeholder="Enter full name">
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" 
                           name="user_name" 
                           class="form-control" 
                           placeholder="Enter username">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter password">
                    <div class="password-requirements">
                        <div class="mb-2">Password must:</div>
                        <ul>
                            <li>Be 8-12 characters long</li>
                            <li>Include at least one uppercase letter</li>
                            <li>Include at least one lowercase letter</li>
                            <li>Include at least one number</li>
                            <li>Include at least one special character</li>
                        </ul>
                    </div>
                </div>

                <button type="submit" class="btn-add-user">
                    <i class="fa fa-plus"></i> Add User
                </button>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(2)");
        active.classList.add("active");
    </script>
</body>
</html>
<?php } else { 
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}
?>