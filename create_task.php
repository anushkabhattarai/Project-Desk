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
	<title>Create Task</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
			<div class="container-fluid px-4 py-3">
				<!-- Header Area -->
				<div class="mb-4">
					<h4 class="mb-1">Create Task</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
							<li class="breadcrumb-item"><a href="tasks.php" class="text-decoration-none">Tasks</a></li>
							<li class="breadcrumb-item active">Create Task</li>
						</ol>
					</nav>
				</div>

				<!-- Alert Messages -->
				<?php if (isset($_GET['error'])) { ?>
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<?php echo stripcslashes($_GET['error']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php } ?>

				<?php if (isset($_GET['success'])) { ?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<?php echo stripcslashes($_GET['success']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php } ?>

				<!-- Task Form -->
				<div class="card border-0 shadow-sm">
					<div class="card-body p-4">
						<form method="POST" action="app/add-task.php">
							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="title" class="form-label">Title</label>
									<input type="text" 
										   class="form-control" 
										   id="title" 
										   name="title" 
										   placeholder="Enter task title"
										   required>
								</div>
								
								<div class="col-md-6 mb-3">
									<label for="due_date" class="form-label">Due Date</label>
									<input type="date" 
										   class="form-control" 
										   id="due_date" 
										   name="due_date">
								</div>

								<div class="col-md-6 mb-3">
									<label for="assigned_to" class="form-label">Assigned to</label>
									<select class="form-select" 
											id="assigned_to" 
											name="assigned_to" 
											required>
										<option value="0">Select employee</option>
										<?php if ($users != 0) { 
											foreach ($users as $user) { ?>
												<option value="<?=$user['id']?>"><?=$user['full_name']?></option>
										<?php } } ?>
									</select>
								</div>

								<div class="col-12 mb-3">
									<label for="description" class="form-label">Description</label>
									<textarea class="form-control" 
											  id="description" 
											  name="description" 
											  rows="4" 
											  placeholder="Enter task description"
											  required></textarea>
								</div>

								<div class="col-12">
									<button type="submit" class="btn btn-primary" style="background-color: #1a237e; border-color: #1a237e;">
										<i class="fa fa-plus me-2"></i>Create Task
									</button>
									<a href="tasks.php" class="btn btn-outline-secondary">
										Cancel
									</a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</section>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(3)");
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