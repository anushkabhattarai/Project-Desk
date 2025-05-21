<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    
    $text = "All Tasks";
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
	<title>Tasks</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<?php include "inc/nav.php" ?>
	
	<main style="margin-left: 250px;">
		<div class="container-fluid p-4">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h4 class="fw-bold"><?=$text?></h4>
				<a href="create_task.php" class="btn btn-primary btn-sm">
					<i class="fa fa-plus me-1"></i> New Task
				</a>
			</div>
			
			<!-- Task Stats -->
			<div class="row mb-4">
				<div class="col-md-3 col-sm-6 mb-3 mb-md-0">
					<div class="card border-0 shadow-sm">
						<div class="card-body d-flex align-items-center p-3">
							<div class="bg-primary text-white rounded p-2 me-3">
								<i class="fa fa-list-ul"></i>
							</div>
							<div>
								<div class="text-muted small">Total Tasks</div>
								<div class="fw-bold fs-4"><?=$num_task?></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3 col-sm-6 mb-3 mb-md-0">
					<div class="card border-0 shadow-sm">
						<div class="card-body d-flex align-items-center p-3">
							<div class="bg-warning text-white rounded p-2 me-3">
								<i class="fa fa-clock-o"></i>
							</div>
							<div>
								<div class="text-muted small">Pending</div>
								<div class="fw-bold fs-4">
									<?php 
										$pending = 0;
										if ($tasks != 0) {
											foreach ($tasks as $task) {
												if ($task['status'] == 'Pending') $pending++;
											}
										}
										echo $pending;
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3 col-sm-6 mb-3 mb-md-0">
					<div class="card border-0 shadow-sm">
						<div class="card-body d-flex align-items-center p-3">
							<div class="bg-info text-white rounded p-2 me-3">
								<i class="fa fa-spinner"></i>
							</div>
							<div>
								<div class="text-muted small">In Progress</div>
								<div class="fw-bold fs-4">
									<?php 
										$inProgress = 0;
										if ($tasks != 0) {
											foreach ($tasks as $task) {
												if ($task['status'] == 'In Progress') $inProgress++;
											}
										}
										echo $inProgress;
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3 col-sm-6">
					<div class="card border-0 shadow-sm">
						<div class="card-body d-flex align-items-center p-3">
							<div class="bg-success text-white rounded p-2 me-3">
								<i class="fa fa-check"></i>
							</div>
							<div>
								<div class="text-muted small">Completed</div>
								<div class="fw-bold fs-4">
									<?php 
										$completed = 0;
										if ($tasks != 0) {
											foreach ($tasks as $task) {
												if ($task['status'] == 'Completed') $completed++;
											}
										}
										echo $completed;
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Filter Tabs -->
			<div class="mb-4">
				<div class="nav nav-pills">
					<a href="tasks.php" class="nav-link <?php echo !isset($_GET['due_date']) ? 'active bg-primary' : 'text-dark'; ?>">
						All Tasks
					</a>
					<a href="tasks.php?due_date=Due Today" class="nav-link <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'Due Today' ? 'active bg-primary' : 'text-dark'; ?>">
						Due Today
					</a>
					<a href="tasks.php?due_date=Overdue" class="nav-link <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'Overdue' ? 'active bg-primary' : 'text-dark'; ?>">
						Overdue
					</a>
					<a href="tasks.php?due_date=No Deadline" class="nav-link <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'No Deadline' ? 'active bg-primary' : 'text-dark'; ?>">
						No Deadline
					</a>
				</div>
			</div>

			<?php if (isset($_GET['success'])) { ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<?php echo stripcslashes($_GET['success']); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php } ?>

			<!-- Tasks Table -->
			<?php if ($tasks != 0) { ?>
				<div class="card border-0 shadow-sm">
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-hover align-middle mb-0">
								<thead>
									<tr>
										<th>#</th>
										<th>Title</th>
										<th>Description</th>
										<th>Assigned To</th>
										<th>Due Date</th>
										<th>Status</th>
										<th class="text-end">Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php $i=0; foreach ($tasks as $task) { ?>
										<tr>
											<td><?=++$i?></td>
											<td class="fw-medium"><?=$task['title']?></td>
											<td>
												<?php 
													echo (strlen($task['description']) > 40) ? 
														substr($task['description'], 0, 40) . '...' : 
														$task['description']; 
												?>
											</td>
											<td>
												<?php 
													 $assignees = get_task_assignees($conn, $task['id']);
													 if ($assignees !== 0) {
														 $names = array_map(function($user) {
															 return "<span class='badge bg-light text-dark'>{$user['full_name']}</span>";
														 }, $assignees);
														 echo implode(" ", $names);
													 } else {
														 echo "<span class='text-muted'>Unassigned</span>";
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
											<td>
												<?php
												switch($task['status']) {
													case 'Pending':
														echo "<span class='badge bg-warning text-dark'>Pending</span>";
														break;
													case 'In Progress':
														echo "<span class='badge bg-info text-dark'>In Progress</span>";
														break;
													case 'Completed':
														echo "<span class='badge bg-success text-white'>Completed</span>";
														break;
													default:
														echo "<span class='badge bg-secondary text-white'>{$task['status']}</span>";
												}
												?>
											</td>
											<td class="text-end">
												<a href="edit-task.php?id=<?=$task['id']?>" class="btn btn-sm btn-outline-primary">
													<i class="fa fa-edit"></i>
												</a>
												<a href="delete-task.php?id=<?=$task['id']?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
													<i class="fa fa-trash"></i>
												</a>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<div class="text-center py-5 my-4">
					<i class="fa fa-tasks fa-3x text-muted mb-3"></i>
					<h5 class="text-muted">No tasks found</h5>
					<a href="create_task.php" class="btn btn-primary mt-3">Create New Task</a>
				</div>
			<?php } ?>
		</div>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		var active = document.querySelector("#navList li:nth-child(4)");
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