<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) ) {

	 include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";

	if ($_SESSION['role'] == "admin") {
		  $todaydue_task = count_tasks_due_today($conn);
	     $overdue_task = count_tasks_overdue($conn);
	     $nodeadline_task = count_tasks_NoDeadline($conn);
	     $num_task = count_tasks($conn);
	     $num_users = count_users($conn);
	     $pending = count_pending_tasks($conn);
	     $in_progress = count_in_progress_tasks($conn);
	     $completed = count_completed_tasks($conn);
	}else {
        $num_my_task = count_my_tasks($conn, $_SESSION['id']);
        $overdue_task = count_my_tasks_overdue($conn, $_SESSION['id']);
        $nodeadline_task = count_my_tasks_NoDeadline($conn, $_SESSION['id']);
        $pending = count_my_pending_tasks($conn, $_SESSION['id']);
	     $in_progress = count_my_in_progress_tasks($conn, $_SESSION['id']);
	     $completed = count_my_completed_tasks($conn, $_SESSION['id']);

	}
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Dashboard</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	
	<!-- Include the navigation sidebar -->
	<?php include "inc/nav.php" ?>
	
	<!-- Main content area with margin-left to account for sidebar width -->
	<main style="margin-left: 250px; padding-top: 70px;">
		<section class="section-1 bg-white">
			<div class="container-fluid px-4 py-3">
				<div class="mb-4">
					<h4 class="mb-1">Dashboard</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
							<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
						</ol>
					</nav>
				</div>

				<?php if ($_SESSION['role'] == "admin") { ?>
					<div class="row g-4">
						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Total Employees</h6>
											<h3 class="mb-0"><?=$num_users?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-users text-primary fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">All Tasks</h6>
											<h3 class="mb-0"><?=$num_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-tasks text-success fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Overdue</h6>
											<h3 class="mb-0"><?=$overdue_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-window-close-o text-danger fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">No Deadline</h6>
											<h3 class="mb-0"><?=$nodeadline_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-clock-o text-warning fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Due Today</h6>
											<h3 class="mb-0"><?=$todaydue_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-exclamation-triangle text-warning fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Pending</h6>
											<h3 class="mb-0"><?=$pending?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-square-o text-secondary fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">In Progress</h6>
											<h3 class="mb-0"><?=$in_progress?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-spinner text-info fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4 col-lg-3">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Completed</h6>
											<h3 class="mb-0"><?=$completed?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-check-square-o text-success fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?php } else { ?>
					<div class="row g-4">
						<div class="col-md-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">My Tasks</h6>
											<h3 class="mb-0"><?=$num_my_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-tasks text-primary fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Overdue</h6>
											<h3 class="mb-0"><?=$overdue_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-window-close-o text-danger fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">No Deadline</h6>
											<h3 class="mb-0"><?=$nodeadline_task?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-clock-o text-warning fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Pending</h6>
											<h3 class="mb-0"><?=$pending?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-square-o text-secondary fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">In Progress</h6>
											<h3 class="mb-0"><?=$in_progress?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-spinner text-info fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<h6 class="text-muted mb-2">Completed</h6>
											<h3 class="mb-0"><?=$completed?></h3>
										</div>
										<div class="bg-light rounded-circle p-3">
											<i class="fa fa-check-square-o text-success fs-4"></i>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</section>
	</main>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(1)");
		active.classList.add("active");
	</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
 ?>