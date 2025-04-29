<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) ) {

	 include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";

    // Function to get upcoming tasks due in next 7 days
    function get_upcoming_tasks($conn, $user_id = null, $days = 7) {
        $today = date('Y-m-d');
        $futureDate = date('Y-m-d', strtotime("+$days days"));
        
        if ($user_id) {
            // For specific user
            $sql = "SELECT * FROM tasks WHERE assigned_to = ? AND due_date > CURDATE() AND due_date <= ? AND status != 'completed' ORDER BY due_date ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $futureDate]);
        } else {
            // For admin (all tasks)
            $sql = "SELECT t.*, u.full_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.due_date > CURDATE() AND t.due_date <= ? AND t.status != 'completed' ORDER BY t.due_date ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$futureDate]);
        }
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetchAll();
        } else {
            return 0;
        }
    }

    // Function to get newly assigned tasks (within last 48 hours)
    function get_newly_assigned_tasks($conn, $user_id = null) {
        $recentTime = date('Y-m-d H:i:s', strtotime("-48 hours"));
        
        if ($user_id) {
            // For specific user
            $sql = "SELECT * FROM tasks WHERE assigned_to = ? AND created_at >= ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $recentTime]);
        } else {
            // For admin (all recent tasks)
            $sql = "SELECT t.*, u.full_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.created_at >= ? ORDER BY t.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$recentTime]);
        }
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetchAll();
        } else {
            return 0;
        }
    }

	if ($_SESSION['role'] == "admin") {
		$todaydue_task = count_tasks_due_today($conn);
	     $overdue_task = count_tasks_overdue($conn);
	     $nodeadline_task = count_tasks_NoDeadline($conn);
	     $num_task = count_tasks($conn);
	     $num_users = count_users($conn);
	     $pending = count_pending_tasks($conn);
	     $in_progress = count_in_progress_tasks($conn);
	     $completed = count_completed_tasks($conn);
         $upcoming_tasks = get_upcoming_tasks($conn);
         $newly_assigned_tasks = get_newly_assigned_tasks($conn);
	}else {
        $num_my_task = count_my_tasks($conn, $_SESSION['id']);
        $overdue_task = count_my_tasks_overdue($conn, $_SESSION['id']);
        $nodeadline_task = count_my_tasks_NoDeadline($conn, $_SESSION['id']);
        $pending = count_my_pending_tasks($conn, $_SESSION['id']);
	     $in_progress = count_my_in_progress_tasks($conn, $_SESSION['id']);
	     $completed = count_my_completed_tasks($conn, $_SESSION['id']);
         $upcoming_tasks = get_upcoming_tasks($conn, $_SESSION['id']);
         $newly_assigned_tasks = get_newly_assigned_tasks($conn, $_SESSION['id']);
	}
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Dashboard</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/dashboard-style.css">
</head>
<body class="bg-light">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	
	<!-- Include the navigation sidebar -->
	<?php include "inc/nav.php" ?>
	
	<!-- Main content area with margin-left to account for sidebar width -->
	<main style="margin-left: 250px; ">
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
					<!-- Modern redesigned dashboard for non-admin users -->
					<div class="dashboard-container">
						<!-- Stats Cards Row -->
						<div class="row stats-row g-4 mb-4">
							<div class="col-md-4 col-lg-2">
								<div class="card stat-card border-0 shadow-sm h-100 task-card">
									<div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
										<div class="stat-icon-container mb-3">
											<i class="fa fa-tasks"></i>
										</div>
										<h3 class="mb-1"><?=$num_my_task?></h3>
										<p class="text-center mb-0">My Tasks</p>
									</div>
								</div>
							</div>

							<div class="col-md-4 col-lg-2">
								<div class="card stat-card border-0 shadow-sm h-100 overdue-card">
									<div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
										<div class="stat-icon-container mb-3">
											<i class="fa fa-window-close-o"></i>
										</div>
										<h3 class="mb-1"><?=$overdue_task?></h3>
										<p class="text-center mb-0">Overdue</p>
									</div>
								</div>
							</div>

							<div class="col-md-4 col-lg-2">
								<div class="card stat-card border-0 shadow-sm h-100 nodeadline-card">
									<div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
										<div class="stat-icon-container mb-3">
											<i class="fa fa-clock-o"></i>
										</div>
										<h3 class="mb-1"><?=$nodeadline_task?></h3>
										<p class="text-center mb-0">No Deadline</p>
									</div>
								</div>
							</div>

							<div class="col-md-4 col-lg-2">
								<div class="card stat-card border-0 shadow-sm h-100 pending-card">
									<div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
										<div class="stat-icon-container mb-3">
											<i class="fa fa-square-o"></i>
										</div>
										<h3 class="mb-1"><?=$pending?></h3>
										<p class="text-center mb-0">Pending</p>
									</div>
								</div>
							</div>

							<div class="col-md-4 col-lg-2">
								<div class="card stat-card border-0 shadow-sm h-100 progress-card">
									<div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
										<div class="stat-icon-container mb-3">
											<i class="fa fa-spinner"></i>
										</div>
										<h3 class="mb-1"><?=$in_progress?></h3>
										<p class="text-center mb-0">In Progress</p>
									</div>
								</div>
							</div>

							<div class="col-md-4 col-lg-2">
								<div class="card stat-card border-0 shadow-sm h-100 completed-card">
									<div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
										<div class="stat-icon-container mb-3">
											<i class="fa fa-check-square-o"></i>
										</div>
										<h3 class="mb-1"><?=$completed?></h3>
										<p class="text-center mb-0">Completed</p>
									</div>
								</div>
							</div>
						</div>

						<!-- Two column layout for task sections -->
						<div class="row task-columns g-4">
							<!-- Column 1: Upcoming Task Reminders -->
							<div class="col-lg-6">
								<div class="card border-0 shadow-sm h-100 task-section-card">
									<div class="card-header bg-transparent border-0 d-flex align-items-center">
										<div class="section-icon upcoming-icon me-2">
											<i class="fa fa-calendar"></i>
										</div>
										<h5 class="mb-0">Upcoming Task Reminders</h5>
									</div>
									<div class="card-body pt-0">
										<?php if ($upcoming_tasks != 0) { ?>
											<div class="table-responsive">
												<table class="table table-hover align-middle task-table mb-0">
													<thead>
														<tr>
															<th>Title</th>
															<th>Due Date</th>
															<th>Status</th>
															<th class="text-end">Actions</th>
														</tr>
													</thead>
													<tbody>
														<?php foreach ($upcoming_tasks as $task) { ?>
															<tr>
																<td class="fw-medium"><?=$task['title']?></td>
																<td>
																	<?php 
																		$dueDate = strtotime($task['due_date']);
																		$today = strtotime(date('Y-m-d'));
																		$daysLeft = floor(($dueDate - $today) / (60 * 60 * 24));
																	
																		if ($daysLeft == 0) {
																			echo "<span class='badge bg-warning text-dark'>Today</span>";
																		} elseif ($daysLeft == 1) {
																			echo "<span class='badge bg-warning text-dark'>Tomorrow</span>";
																		} else {
																			echo "<span class='badge bg-info text-white'>In $daysLeft days</span>";
																		}
																		echo " " . date('M d, Y', $dueDate);
																	?>
																</td>
																<td>
																	<?php 
																		if ($task['status'] == 'pending') {
																			echo '<span class="badge bg-secondary">Pending</span>';
																		} elseif ($task['status'] == 'in_progress') {
																			echo '<span class="badge bg-info">In Progress</span>';
																		}
																	?>
																</td>
																<td class="text-end">
																	<a href="edit-task-employee.php?id=<?=$task['id']?>" class="btn btn-sm btn-primary btn-view">View</a>
																</td>
															</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
										<?php } else { ?>
											<div class="alert alert-info mb-0">
												<i class="fa fa-info-circle me-2"></i> No upcoming tasks in the next 7 days.
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
						
							<!-- Column 2: Newly Assigned Tasks -->
							<div class="col-lg-6">
								<div class="card border-0 shadow-sm h-100 task-section-card">
									<div class="card-header bg-transparent border-0 d-flex align-items-center">
										<div class="section-icon new-task-icon me-2">
											<i class="fa fa-star"></i>
										</div>
										<h5 class="mb-0">Newly Assigned Tasks</h5>
									</div>
									<div class="card-body pt-0">
										<?php if ($newly_assigned_tasks != 0) { ?>
											<div class="table-responsive">
												<table class="table table-hover align-middle task-table mb-0">
													<thead>
														<tr>
															<th>Title</th>
															<th>Assigned</th>
															<th>Due Date</th>
															<th class="text-end">Actions</th>
														</tr>
													</thead>
													<tbody>
														<?php foreach ($newly_assigned_tasks as $task) { ?>
															<tr>
																<td class="fw-medium">
																	<?=$task['title']?>
																	<span class="badge bg-success ms-2">New</span>
																</td>
																<td>
																	<?php 
																		$assignedDate = strtotime($task['created_at']);
																		$now = time();
																		$hoursAgo = round(($now - $assignedDate) / 3600);
																	
																		if ($hoursAgo < 1) {
																			echo "<span class='text-success'>Just now</span>";
																		} elseif ($hoursAgo < 24) {
																			echo "<span class='text-success'>{$hoursAgo} hour" . ($hoursAgo > 1 ? "s" : "") . " ago</span>";
																		} else {
																			echo "<span class='text-success'>" . floor($hoursAgo / 24) . " day" . (floor($hoursAgo / 24) > 1 ? "s" : "") . " ago</span>";
																		}
																	?>
																</td>
																<td>
																	<?php if($task['due_date'] == "") { ?>
																		<span class="badge bg-secondary">No Deadline</span>
																	<?php } else { 
																		$dueDate = strtotime($task['due_date']);
																		$today = strtotime(date('Y-m-d'));
																	
																		if($dueDate < $today) {
																			echo "<span class='badge bg-danger'>" . date('M d, Y', $dueDate) . "</span>";
																		} elseif($dueDate == $today) {
																			echo "<span class='badge bg-warning text-dark'>Today</span>";
																		} else {
																			echo date('M d, Y', $dueDate);
																		}
																	} ?>
																</td>
																<td class="text-end">
																	<a href="edit-task-employee.php?id=<?=$task['id']?>" class="btn btn-sm btn-primary btn-view">View</a>
																</td>
															</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
										<?php } else { ?>
											<div class="alert alert-info mb-0">
												<i class="fa fa-info-circle me-2"></i> No new tasks have been assigned recently.
											</div>
										<?php } ?>
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