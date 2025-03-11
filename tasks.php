<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    
    $text = "All Task";
    if (isset($_GET['due_date']) &&  $_GET['due_date'] == "Due Today") {
    	$text = "Due Today";
      $tasks = get_all_tasks_due_today($conn);
      $num_task = count_tasks_due_today($conn);
    }else if (isset($_GET['due_date']) &&  $_GET['due_date'] == "Overdue") {
    	$text = "Overdue";
      $tasks = get_all_tasks_overdue($conn);
      $num_task = count_tasks_overdue($conn);
    }else if (isset($_GET['due_date']) &&  $_GET['due_date'] == "No Deadline") {
    	$text = "No Deadline";
      $tasks = get_all_tasks_NoDeadline($conn);
      $num_task = count_tasks_NoDeadline($conn);
    }else{
    	 $tasks = get_all_tasks($conn);
       $num_task = count_tasks($conn);
    }
    $users = get_all_users($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
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

        /* New Sidebar Styles */
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

        /* Adjust Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: 1200px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-new-btn {
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .add-new-btn:hover {
            background: #5254cc;
            color: white;
            transform: translateY(-1px);
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-tab {
            padding: 0.625rem 1.25rem;
            border-radius: 6px;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .tasks-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-top: 1.5rem;
        }

        .table > :not(caption) > * > * {
            padding: 1rem;
        }

        .tasks-table th {
            background: white;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .tasks-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-completed { background: #dcfce7; color: #166534; }
        .status-progress { background: #e0e7ff; color: #4338ca; }
        .status-pending { background: #fff7ed; color: #c2410c; }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .edit-btn, .delete-btn {
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .edit-btn {
            background: #e0e7ff;
            color: #4338ca;
        }

        .delete-btn {
            background: #fee2e2;
            color: #b91c1c;
        }

        .edit-btn:hover, .delete-btn:hover {
            transform: translateY(-1px);
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

        .success-alert {
            background: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .assigned-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            color: #4b5563;
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
            <li>
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
            <li class="active">
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
                <?=$text?> 
                <span class="badge bg-light text-dark"><?=$num_task?></span>
            </h4>
            <a href="create_task.php" class="add-new-btn">
                <i class="fa fa-plus"></i> Create Task
            </a>
        </div>

        <div class="filter-tabs">
            <a href="tasks.php" class="filter-tab <?php echo !isset($_GET['due_date']) ? 'active' : ''; ?>">
                All Tasks
            </a>
            <a href="tasks.php?due_date=Due Today" class="filter-tab <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'Due Today' ? 'active' : ''; ?>">
                Due Today
            </a>
            <a href="tasks.php?due_date=Overdue" class="filter-tab <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'Overdue' ? 'active' : ''; ?>">
                Overdue
            </a>
            <a href="tasks.php?due_date=No Deadline" class="filter-tab <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'No Deadline' ? 'active' : ''; ?>">
                No Deadline
            </a>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="success-alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
        <?php } ?>

        <?php if ($tasks != 0) { ?>
            <div class="tasks-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=0; foreach ($tasks as $task) { ?>
                            <tr>
                                <td><?=++$i?></td>
                                <td class="fw-medium"><?=$task['title']?></td>
                                <td class="text-muted"><?=$task['description']?></td>
                                <td>
                                    <?php 
                                    foreach ($users as $user) {
                                        if($user['id'] == $task['assigned_to']){
                                            echo "<div class='assigned-user'>
                                                    <div class='user-avatar'>
                                                        " . substr($user['full_name'], 0, 1) . "
                                                    </div>
                                                    <span>{$user['full_name']}</span>
                                                </div>";
                                        }
                                    }?>
                                </td>
                                <td>
                                    <?php if($task['due_date'] == "") { ?>
                                        <span class="status-badge status-pending">No Deadline</span>
                                    <?php } else { ?>
                                        <span class="status-badge"><?=$task['due_date']?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php
                                        echo $task['status'] == 'Completed' ? 'status-completed' :
                                            ($task['status'] == 'In Progress' ? 'status-progress' : 'status-pending');
                                    ?>">
                                        <?=$task['status']?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-task.php?id=<?=$task['id']?>" class="edit-btn">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="delete-task.php?id=<?=$task['id']?>" class="delete-btn">
                                            <i class="fa fa-trash"></i>
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
                <i class="fa fa-tasks"></i>
                <h3>No Tasks Found</h3>
                <p>Start by creating your first task</p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
<?php } else { 
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}
?>