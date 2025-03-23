<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";

    $tasks = get_all_tasks_by_id($conn, $_SESSION['id']);

 ?>
<!DOCTYPE html>
<html>
<head>
	<title>My Tasks</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body, .body, .section-1 {
			background-color: white !important;
		}
		
		/* Enhanced shadows and card styling */
		.card {
			border: none !important;
			box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
			transition: all 0.3s ease !important;
		}
		
		.card:hover {
			box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
			transform: translateY(-2px);
		}
		
		.card-header, .card-footer {
			background-color: transparent !important;
			border: none !important;
		}
		
		/* Enhanced button shadows */
		.task-filter {
			box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
		}
		
		.task-filter:hover, .task-filter.active {
			box-shadow: 0 6px 18px rgba(0,0,0,0.1) !important;
		}
		
		/* Remove all borders */
		.border-0, .btn, .alert {
			border: none !important;
		}
	</style>
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<?php include "inc/nav.php" ?>
	
	<!-- Main content area with margin-left to account for sidebar width -->
	<main style="margin-left: 250px; padding-top: 70px;">
		<section class="section-1 bg-white">
			<div class="container-fluid px-4 py-3">
				<!-- Enhanced Header Area -->
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h4 class="mb-1 fw-bold">My Tasks</h4>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
								<li class="breadcrumb-item active">My Tasks</li>
							</ol>
						</nav>
					</div>
					<!-- Task Statistics -->
					<div class="d-flex gap-4">
						<button class="btn btn-light px-4 py-3 rounded-4 task-filter" data-filter="all" style="min-width: 160px; transition: all 0.3s ease;">
							<div class="d-flex align-items-center mb-2">
								<img src="img/alltask.png" alt="All Tasks" class="me-2" style="width: 24px; height: 24px;">
								<small class="text-muted">Total Tasks</small>
							</div>
							<span class="fw-bold fs-4 text-primary"><?= count($tasks) ?></span>
						</button>
						<button class="btn btn-light border-0 px-4 py-3 rounded-4 task-filter shadow-sm hover-shadow" data-filter="pending" style="min-width: 160px; transition: all 0.3s ease;">
							<div class="d-flex align-items-center mb-2">
								<img src="img/pending.png" alt="Pending Tasks" class="me-2" style="width: 24px; height: 24px;">
								<small class="text-muted">Pending</small>
							</div>
							<span class="fw-bold fs-4 text-warning">
								<?= array_reduce($tasks, function($carry, $task) {
									return $carry + ($task['status'] == 'pending' ? 1 : 0);
								}, 0) ?>
							</span>
						</button>
						<button class="btn btn-light border-0 px-4 py-3 rounded-4 task-filter shadow-sm hover-shadow" data-filter="completed" style="min-width: 160px; transition: all 0.3s ease;">
							<div class="d-flex align-items-center mb-2">
								<img src="img/completed.png" alt="Completed Tasks" class="me-2" style="width: 24px; height: 24px;">
								<small class="text-muted">Completed</small>
							</div>
							<span class="fw-bold fs-4 text-success">
								<?= array_reduce($tasks, function($carry, $task) {
									return $carry + ($task['status'] == 'completed' ? 1 : 0);
								}, 0) ?>
							</span>
						</button>
					</div>
				</div>

				<?php if (isset($_GET['success'])) { ?>
					<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
						<?php echo stripcslashes($_GET['success']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php } ?>

				<!-- Enhanced Tasks Table -->
				<?php if ($tasks != 0) { ?>
					<div class="row g-4" id="taskContainer">
						<?php foreach ($tasks as $task) { ?>
							<div class="col-md-6 col-lg-4 task-item" data-status="<?=$task['status']?>">
								<div class="card h-100">
									<!-- Card Header -->
									<div class="card-header pt-4 pb-0">
										<div class="d-flex justify-content-between align-items-center">
											<?php
											$statusClass = '';
											$statusBg = '';
											switch($task['status']) {
												case 'pending':
													$statusClass = 'warning';
													$statusBg = 'bg-warning-subtle';
													break;
												case 'in_progress':
													$statusClass = 'info';
													$statusBg = 'bg-info-subtle';
													break;
												case 'completed':
													$statusClass = 'success';
													$statusBg = 'bg-success-subtle';
													break;
											}
											?>
											<span class="badge <?=$statusBg?> text-<?=$statusClass?> px-3 py-2">
												<?=ucfirst($task['status'])?>
											</span>
											<?php if($task['due_date'] != "") { ?>
												<span class="badge bg-primary-subtle text-primary px-3 py-2">
													<i class="fa fa-calendar-o me-1"></i>
													<?=$task['due_date']?>
												</span>
											<?php } else { ?>
												<span class="badge bg-secondary-subtle text-secondary px-3 py-2">
													No Deadline
												</span>
											<?php } ?>
										</div>
									</div>

									<!-- Card Body -->
									<div class="card-body">
										<h5 class="card-title fw-bold mb-3">
											<?=$task['title']?>
										</h5>
										<p class="card-text text-muted mb-0">
											<?=$task['description']?>
										</p>
									</div>

									<!-- Card Footer -->
									<div class="card-footer pt-0">
										<div class="d-grid">
											<a href="edit-task-employee.php?id=<?=$task['id']?>" 
											   class="btn bg-primary-subtle btn-sm shadow-sm text-primary">
												<i class="fa fa-eye me-2"></i>
												View Status
											</a>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
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
		</section>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(2)");
		active.classList.add("active");

		// Task filtering functionality
		document.querySelectorAll('.task-filter').forEach(button => {
			button.addEventListener('click', function() {
				const filter = this.getAttribute('data-filter');
				const taskItems = document.querySelectorAll('.task-item');
				
				// Remove active class from all buttons
				document.querySelectorAll('.task-filter').forEach(btn => {
					btn.classList.remove('active');
					btn.classList.remove('shadow');
					btn.classList.add('shadow-sm');
				});
				
				// Add active class to clicked button
				this.classList.add('active');
				this.classList.remove('shadow-sm');
				this.classList.add('shadow');
				
				taskItems.forEach(item => {
					if (filter === 'all' || item.getAttribute('data-status') === filter) {
						item.style.display = 'block';
					} else {
						item.style.display = 'none';
					}
				});
			});

			// Add hover effect
			button.addEventListener('mouseenter', function() {
				if (!this.classList.contains('active')) {
					this.classList.remove('shadow-sm');
					this.classList.add('shadow');
				}
			});

			button.addEventListener('mouseleave', function() {
				if (!this.classList.contains('active')) {
					this.classList.remove('shadow');
					this.classList.add('shadow-sm');
				}
			});
		});
	</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>