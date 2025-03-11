<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    
    if (!isset($_GET['id'])) {
         header("Location: tasks.php");
         exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);

    if ($task == 0) {
         header("Location: tasks.php");
         exit();
    }
    $users = get_all_users($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
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
        .edit-form {
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

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236B7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .btn-update {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            background: #5254cc;
            transform: translateY(-1px);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
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
                Edit Task
                <a href="tasks.php"><i class="fa fa-arrow-left"></i> Back to Tasks</a>
            </h4>
        </div>

        <div class="edit-form">
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

            <form method="POST" action="app/update-task.php">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" 
                           name="title" 
                           class="form-control" 
                           placeholder="Enter task title" 
                           value="<?=$task['title']?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" 
                              rows="5" 
                              class="form-control" 
                              placeholder="Enter task description"><?=$task['description']?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" 
                           name="due_date" 
                           class="form-control" 
                           value="<?=$task['due_date']?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Assigned to</label>
                    <select name="assigned_to" class="form-control">
                        <option value="0">Select employee</option>
                        <?php if ($users != 0) { 
                            foreach ($users as $user) {
                                if ($task['assigned_to'] == $user['id']) { ?>
                                    <option selected value="<?=$user['id']?>"><?=$user['full_name']?></option>
                                <?php } else { ?>
                                    <option value="<?=$user['id']?>"><?=$user['full_name']?></option>
                        <?php } } } ?>
                    </select>
                </div>

                <input type="hidden" name="id" value="<?=$task['id']?>">

                <button type="submit" class="btn-update">
                    <i class="fa fa-save"></i> Update Task
                </button>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(4)");
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