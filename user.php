<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    $users = get_all_users($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
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
            margin: 0;
        }

        .btn-add {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background: #5254cc;
            color: white;
            transform: translateY(-1px);
        }

        .users-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table > :not(caption) > * > * {
            padding: 1rem;
        }

        .badge-role {
            background: #e0e7ff;
            color: #4338ca;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-edit {
            background: #e0e7ff;
            color: #4338ca;
        }

        .btn-delete {
            background: #fee2e2;
            color: #b91c1c;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            margin-top: 2rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #6b7280;
            margin-bottom: 1rem;
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
            <h4 class="page-title">Manage Users</h4>
            <a href="add-user.php" class="btn-add">
                <i class="fa fa-plus"></i> Add User
            </a>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
        <?php } ?>

        <?php if ($users != 0) { ?>
            <div class="users-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=0; foreach ($users as $user) { ?>
                            <tr>
                                <td><?=++$i?></td>
                                <td class="fw-medium"><?=$user['full_name']?></td>
                                <td><?=$user['username']?></td>
                                <td><span class="badge-role"><?=$user['role']?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-user.php?id=<?=$user['id']?>" class="btn-edit">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="delete-user.php?id=<?=$user['id']?>" class="btn-delete">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <i class="fa fa-users"></i>
                <h3>No Users Found</h3>
                <p>Start by adding your first user</p>
            </div>
        <?php } ?>
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