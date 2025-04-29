<?php 
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

include "DB_connection.php";
include "app/Model/Task.php";
include "app/Model/User.php";

// Get tasks
$tasks = get_all_tasks_by_id($conn, $_SESSION['id']);

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            height: 100vh;
            margin: 0;
        }

        .kanban-container {
            height: calc(100vh - 120px);
            padding: 1.5rem;
        }

        .kanban-board {
            display: flex;
            gap: 2.5rem;
            height: 100%;
            padding: 2rem;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
        
        .kanban-column {
            background: white;
            border-radius: 16px;
            min-width: 340px;
            width: 340px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: none;
            margin: 0.5rem 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kanban-column:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .kanban-board::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-board::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .kanban-board::-webkit-scrollbar-thumb {
            background: #cdcdcd;
            border-radius: 4px;
        }

        .kanban-board::-webkit-scrollbar-thumb:hover {
            background: #ababab;
        }
        
        .column-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: background-color 0.2s ease;
        }

        .column-header:hover {
            background-color: rgba(0,0,0,0.02);
        }

        .column-title {
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            margin: 0;
            color: var(--bs-gray-800);
        }
        
        .task-count {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            min-width: 28px;
            text-align: center;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .column-content {
            padding: 1.25rem;
            height: calc(100% - 65px);
            overflow-y: auto;
        }
        
        .kanban-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .kanban-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
            border-color: rgba(var(--bs-primary-rgb), 0.2);
        }

        .task-date {
            font-size: 0.8rem;
            color: var(--bs-gray-600);
            background: var(--bs-gray-100);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .kanban-card:hover .task-date {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }
        
        .task-title {
            font-weight: 600;
            margin: 0.75rem 0;
            color: var(--bs-gray-900);
            font-size: 1rem;
            transition: color 0.2s ease;
        }

        .kanban-card:hover .task-title {
            color: var(--bs-primary);
        }
        
        .task-description {
            font-size: 0.9rem;
            color: var(--bs-gray-600);
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .task-footer .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .task-footer .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Column-specific styles */
        .column-pending .task-count { 
            background: rgba(255, 193, 7, 0.1); 
            color: #ffc107; 
        }
        .column-progress .task-count { 
            background: rgba(13, 202, 240, 0.1); 
            color: #0dcaf0; 
        }
        .column-completed .task-count { 
            background: rgba(25, 135, 84, 0.1); 
            color: #198754; 
        }

        main {
            margin-left: 250px;
            background: #f8fafc;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: white;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .breadcrumb-item a {
            color: var(--bs-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb-item a:hover {
            color: var(--bs-primary-dark);
        }

        .alert {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .alert:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="bg-light">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main>
        <div class="page-header" style="border-bottom: none; shadow: none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-1 fw-bold">My Tasks</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                                <li class="breadcrumb-item active">My Tasks</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <?php echo stripcslashes($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <div class="kanban-container">
                <?php if ($tasks != 0) { ?>
                    <div class="kanban-board">
                        <!-- Pending Column -->
                        <div class="kanban-column column-pending">
                            <div class="column-header">
                                <h6 class="column-title">Pending</h6>
                                <span class="task-count">
                                    <?= array_reduce($tasks, function($carry, $task) {
                                        return $carry + ($task['status'] == 'pending' ? 1 : 0);
                                    }, 0) ?>
                                </span>
                            </div>
                            <div class="column-content">
                                <?php foreach ($tasks as $task) { 
                                    if ($task['status'] == 'pending') { ?>
                                        <div class="kanban-card">
                                            <?php if($task['due_date'] != "") { ?>
                                                <div class="task-date">
                                                    <i class="fa fa-calendar-o"></i>
                                                    <?=$task['due_date']?>
                                                </div>
                                            <?php } ?>
                                            <h6 class="task-title"><?=$task['title']?></h6>
                                            <p class="task-description"><?=$task['description']?></p>
                                            <div class="task-footer">
                                                <a href="edit-task-employee.php?id=<?=$task['id']?>" 
                                                class="btn btn-sm">
                                                    <i class="fa fa-eye me-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                <?php } 
                                } ?>
                            </div>
                        </div>

                        <!-- In Progress Column -->
                        <div class="kanban-column column-progress">
                            <div class="column-header">
                                <h6 class="column-title">In Progress</h6>
                                <span class="task-count">
                                    <?= array_reduce($tasks, function($carry, $task) {
                                        return $carry + ($task['status'] == 'in_progress' ? 1 : 0);
                                    }, 0) ?>
                                </span>
                            </div>
                            <div class="column-content">
                                <?php foreach ($tasks as $task) { 
                                    if ($task['status'] == 'in_progress') { ?>
                                        <div class="kanban-card">
                                            <?php if($task['due_date'] != "") { ?>
                                                <div class="task-date">
                                                    <i class="fa fa-calendar-o"></i>
                                                    <?=$task['due_date']?>
                                                </div>
                                            <?php } ?>
                                            <h6 class="task-title"><?=$task['title']?></h6>
                                            <p class="task-description"><?=$task['description']?></p>
                                            <div class="task-footer">
                                                <a href="edit-task-employee.php?id=<?=$task['id']?>" 
                                                class="btn btn-sm">
                                                    <i class="fa fa-eye me-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                <?php } 
                                } ?>
                            </div>
                        </div>

                        <!-- Completed Column -->
                        <div class="kanban-column column-completed">
                            <div class="column-header">
                                <h6 class="column-title">Completed</h6>
                                <span class="task-count">
                                    <?= array_reduce($tasks, function($carry, $task) {
                                        return $carry + ($task['status'] == 'completed' ? 1 : 0);
                                    }, 0) ?>
                                </span>
                            </div>
                            <div class="column-content">
                                <?php foreach ($tasks as $task) { 
                                    if ($task['status'] == 'completed') { ?>
                                        <div class="kanban-card">
                                            <?php if($task['due_date'] != "") { ?>
                                                <div class="task-date">
                                                    <i class="fa fa-calendar-o"></i>
                                                    <?=$task['due_date']?>
                                                </div>
                                            <?php } ?>
                                            <h6 class="task-title"><?=$task['title']?></h6>
                                            <p class="task-description"><?=$task['description']?></p>
                                            <div class="task-footer">
                                                <a href="edit-task-employee.php?id=<?=$task['id']?>" 
                                                class="btn btn-sm">
                                                    <i class="fa fa-eye me-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                <?php } 
                                } ?>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <div class="card border-0 shadow-sm py-5">
                            <div class="card-body">
                                <img src="img/empty-task.svg" alt="No Tasks" class="mb-4" style="width: 200px;">
                                <h3 class="text-muted mb-2">No Tasks Found</h3>
                                <p class="text-muted mb-0">You don't have any tasks assigned yet.</p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(2)");
        active.classList.add("active");
    </script>
</body>
</html>

<style>


    .kanban-container {
        height: calc(100vh - 100px);
        padding: 1rem;
    }

    .kanban-board {
        display: flex;
        gap: 2.5rem;  /* Increased from 1.5rem */
        height: 100%;
        padding: 2rem;  /* Added padding around the board */
        overflow-x: auto;
    }
    
    .kanban-column {
        background: white;
        border-radius: 16px;
        min-width: 340px;  /* Slightly increased from 320px */
        width: 340px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        border: none;
        margin: 0.5rem 0;  /* Added vertical margin */
    }

    /* Add smooth scrolling to the board */
    .kanban-board {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 2rem;
    }

    /* Enhance scrollbar appearance */
    .kanban-board::-webkit-scrollbar {
        height: 8px;
    }

    .kanban-board::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .kanban-board::-webkit-scrollbar-thumb {
        background: #cdcdcd;
        border-radius: 4px;
    }

    .kanban-board::-webkit-scrollbar-thumb:hover {
        background: #ababab;
    }
        
    .column-header {
        padding: 1rem;
        border-bottom: 2px solid;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
    }

    .column-pending .column-header { border-bottom-color: #ffc107; }
    .column-progress .column-header { border-bottom-color: #0dcaf0; }
    .column-completed .column-header { border-bottom-color: #198754; }

    .column-content {
        padding: 1rem;
        height: calc(100% - 60px);
        overflow-y: auto;
    }

    .kanban-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border: 1px solid #edf2f7;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .task-date {
        font-size: 0.75rem;
        color: #64748b;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }

    .task-title {
        font-weight: 600;
        margin: 0.75rem 0;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .task-description {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    /* Task count badges */
    .task-count {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.2rem 0.75rem;
        border-radius: 20px;
    }

    .column-pending .task-count { 
        background: #fff8e1; 
        color: #b17800; 
    }
    
    .column-progress .task-count { 
        background: #e1f8fb; 
        color: #0987a0; 
    }
    
    .column-completed .task-count { 
        background: #e1f5ea; 
        color: #0f5132; 
    }

    /* View button styles */
    .task-footer .btn {
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .column-pending .task-footer .btn {
        color: #b17800;
        background: #fff8e1;
    }

    .column-progress .task-footer .btn {
        color: #0987a0;
        background: #e1f8fb;
    }

    .column-completed .task-footer .btn {
        color: #0f5132;
        background: #e1f5ea;
    }

    .task-footer .btn:hover {
        transform: translateY(-1px);
    }

    main {
        margin-left: 250px;
        background: white;
    }

    
</style>

<div class="task-footer">
    <a href="edit-task-employee.php?id=<?=$task['id']?>" class="btn">
        <i class="fa fa-eye"></i>
        <span>View Details</span>
    </a>
</div>