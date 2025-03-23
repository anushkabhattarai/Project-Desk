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
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<?php include "inc/nav.php" ?>
	
	<!-- Main content area with margin-left to account for sidebar width -->
	<main style="margin-left: 250px; padding-top: 70px;">
		<section class="section-1 bg-white">
			<div class="container-fluid px-4 py-3">
				<!-- Header Area -->
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h4 class="mb-1"><?=$text?></h4>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
								<li class="breadcrumb-item active">Tasks</li>
							</ol>
						</nav>
					</div>
					<div class="d-flex gap-2">
						<a href="create_task.php" class="btn btn-primary">
							<i class="fa fa-plus me-2"></i>Create Task
						</a>
					</div>
				</div>

				<!-- Filter Buttons -->
				<div class="mb-4">
					<div class="btn-group" role="group">
						<a href="tasks.php" class="btn btn-outline-primary <?php echo !isset($_GET['due_date']) ? 'active' : ''; ?>" style="--bs-btn-color: #1a237e; --bs-btn-border-color: #1a237e; --bs-btn-hover-bg: #1a237e; --bs-btn-hover-border-color: #1a237e; --bs-btn-active-bg: #1a237e; --bs-btn-active-border-color: #1a237e;">
							All Tasks
						</a>
						<a href="tasks.php?due_date=Due Today" class="btn btn-outline-primary <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'Due Today' ? 'active' : ''; ?>" style="--bs-btn-color: #1a237e; --bs-btn-border-color: #1a237e; --bs-btn-hover-bg: #1a237e; --bs-btn-hover-border-color: #1a237e; --bs-btn-active-bg: #1a237e; --bs-btn-active-border-color: #1a237e;">
							Due Today
						</a>
						<a href="tasks.php?due_date=Overdue" class="btn btn-outline-primary <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'Overdue' ? 'active' : ''; ?>" style="--bs-btn-color: #1a237e; --bs-btn-border-color: #1a237e; --bs-btn-hover-bg: #1a237e; --bs-btn-hover-border-color: #1a237e; --bs-btn-active-bg: #1a237e; --bs-btn-active-border-color: #1a237e;">
							Overdue
						</a>
						<a href="tasks.php?due_date=No Deadline" class="btn btn-outline-primary <?php echo isset($_GET['due_date']) && $_GET['due_date'] == 'No Deadline' ? 'active' : ''; ?>" style="--bs-btn-color: #1a237e; --bs-btn-border-color: #1a237e; --bs-btn-hover-bg: #1a237e; --bs-btn-hover-border-color: #1a237e; --bs-btn-active-bg: #1a237e; --bs-btn-active-border-color: #1a237e;">
							No Deadline
						</a>
					</div>
				</div>

				<?php if (isset($_GET['success'])) { ?>
					<div class="alert alert-success" role="alert">
						<?php echo stripcslashes($_GET['success']); ?>
					</div>
				<?php } ?>

				<!-- Tasks Table -->
				<?php if ($tasks != 0) { ?>
					<div class="card border-0 shadow-sm">
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-hover mb-0">
									<thead class="bg-light">
										<tr>
											<th class="border-0">#</th>
											<th class="border-0">Title</th>
											<th class="border-0">Description</th>
											<th class="border-0">Assigned To</th>
											<th class="border-0">Due Date</th>
											<th class="border-0">Status</th>
											<th class="border-0">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php $i=0; foreach ($tasks as $task) { ?>
											<tr>
												<td><?=++$i?></td>
												<td><?=$task['title']?></td>
												<td><?=$task['description']?></td>
												<td>
													<?php 
													foreach ($users as $user) {
														if($user['id'] == $task['assigned_to']){
															echo $user['full_name'];
														}
													}?>
												</td>
												<td>
													<?php if($task['due_date'] == "") 
														echo "<span class='badge bg-secondary'>No Deadline</span>";
													else 
														echo $task['due_date'];
													?>
												</td>
												<td>
													<?php
													$statusClass = '';
													switch($task['status']) {
														case 'Pending':
															$statusClass = 'bg-warning';
															break;
														case 'In Progress':
															$statusClass = 'bg-info';
															break;
														case 'Completed':
															$statusClass = 'bg-success';
															break;
													}
													?>
													<span class="badge <?=$statusClass?>"><?=$task['status']?></span>
												</td>
												<td>
													<div class="btn-group btn-group-sm">
														<a href="edit-task.php?id=<?=$task['id']?>" class="btn btn-outline-primary">
															<i class="fa fa-edit"></i>
														</a>
														<a href="delete-task.php?id=<?=$task['id']?>" class="btn btn-outline-danger">
															<i class="fa fa-trash"></i>
														</a>
													</div>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php } else { ?>
					<div class="text-center py-5">
						<h3 class="text-muted">No tasks found</h3>
					</div>
				<?php } ?>
			</div>
		</section>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript">
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