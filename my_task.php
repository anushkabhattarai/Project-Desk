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
			background: #f0f3f6;
			height: 100vh;
			margin: 0;
		}

		.kanban-container {
			height: calc(100vh - 100px);
			padding: 1rem;
			overflow: hidden;
		}

		.kanban-board {
			display: flex;
			gap: 1rem;
			height: 100%;
			padding-bottom: 1rem;
			overflow-x: auto;
			overflow-y: hidden;
		}

		/* Custom scrollbar for the board */
		.kanban-board::-webkit-scrollbar {
			height: 8px;
		}

		.kanban-board::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 4px;
		}

		.kanban-board::-webkit-scrollbar-thumb {
			background: #ccc;
			border-radius: 4px;
		}

		.kanban-board::-webkit-scrollbar-thumb:hover {
			background: #999;
		}
		
		.kanban-column {
			background: #f8f9fa;
			border-radius: 8px;
			min-width: 300px;
			width: 300px;
			display: flex;
			flex-direction: column;
			max-height: 100%;
		}
		
		.column-header {
			padding: 0.75rem;
			border-radius: 8px 8px 0 0;
			display: flex;
			align-items: center;
			justify-content: space-between;
			background: white;
			border-bottom: 2px solid;
		}
		
		.column-pending .column-header {
			border-bottom-color: #ffc107;
		}
		
		.column-progress .column-header {
			border-bottom-color: #0dcaf0;
		}
		
		.column-completed .column-header {
			border-bottom-color: #198754;
		}

		.column-title {
			font-weight: 600;
			font-size: 0.95rem;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin: 0;
		}
		
		.task-count {
			background: #f8f9fa;
			padding: 0.2rem 0.75rem;
			border-radius: 12px;
			font-size: 0.8rem;
			font-weight: 600;
		}

		.column-content {
			padding: 1rem;
			overflow-y: auto;
			flex-grow: 1;
		}

		/* Custom scrollbar for column content */
		.column-content::-webkit-scrollbar {
			width: 4px;
		}

		.column-content::-webkit-scrollbar-track {
			background: transparent;
		}

		.column-content::-webkit-scrollbar-thumb {
			background: #ddd;
			border-radius: 2px;
		}
		
		.kanban-card {
			background: white;
			border-radius: 6px;
			padding: 0.75rem;
			margin-bottom: 0.75rem;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			border-left: 3px solid #e9ecef;
			transition: all 0.2s ease;
		}

		.column-pending .kanban-card {
			border-left-color: #ffc107;
		}

		.column-progress .kanban-card {
			border-left-color: #0dcaf0;
		}

		.column-completed .kanban-card {
			border-left-color: #198754;
		}
		
		.kanban-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 3px 5px rgba(0,0,0,0.1);
			cursor: grab;
		}

		.kanban-card:active {
			cursor: grabbing;
		}
		
		.task-date {
			font-size: 0.75rem;
			color: #6c757d;
			display: flex;
			align-items: center;
			gap: 0.25rem;
		}
		
		.task-title {
			font-weight: 600;
			margin: 0.5rem 0;
			color: #2c3e50;
			font-size: 0.95rem;
		}
		
		.task-description {
			font-size: 0.85rem;
			color: #6c757d;
			margin-bottom: 0.75rem;
			line-height: 1.4;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}
		
		.task-footer {
			display: flex;
			justify-content: flex-end;
			padding-top: 0.5rem;
			border-top: 1px solid #f0f0f0;
		}
		
		.task-footer .btn {
			padding: 0.25rem 0.75rem;
			font-size: 0.8rem;
			color: #6c757d;
			background: #f8f9fa;
		}

		.task-footer .btn:hover {
			background: #e9ecef;
			color: #2c3e50;
		}
		
		@media (max-width: 768px) {
			.kanban-container {
				height: auto;
				padding: 0.75rem;
			}

			.kanban-board {
				flex-direction: column;
				height: auto;
				overflow: visible;
			}
			
			.kanban-column {
				width: 100%;
				min-width: 100%;
				margin-bottom: 1rem;
				max-height: none;
			}

			.column-content {
				max-height: 400px;
			}
		}

		main {
			margin-left: 250px;
			padding-top: 80px;
		}

		.container-fluid {
			padding-top: 0.5rem;
		}

		.d-flex.justify-content-between.align-items-center.py-3 {
			padding-top: 0.5rem !important;
			padding-bottom: 0.5rem !important;
			margin-bottom: 0.5rem;
		}
	</style>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<?php include "inc/nav.php" ?>
	
	<main style="margin-left: 250px; padding-top: 0px;">
		<div class="container-fluid">
			<div class="d-flex justify-content-between align-items-center py-3">
				<div>
					<h4 class="mb-1 fw-bold">My Tasks</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
							<li class="breadcrumb-item active">My Tasks</li>
						</ol>
					</nav>
				</div>
			</div>

			<?php if (isset($_GET['success'])) { ?>
				<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
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