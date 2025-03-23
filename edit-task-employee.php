<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee") {
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
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		/* Match the nice styles from my_task.php */
		.card {
			border: none !important;
			box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
			transition: all 0.3s ease !important;
		}
		
		.card:hover {
			box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
			transform: translateY(-2px);
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
				<!-- Header Area -->
				<div class="mb-4">
					<h4 class="mb-1">Edit Task</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
							<li class="breadcrumb-item"><a href="my_task.php" class="text-decoration-none">My Tasks</a></li>
							<li class="breadcrumb-item active">Edit Task</li>
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

				<div class="row">
					<!-- Task Details Card -->
					<div class="col-md-6 mb-4">
						<div class="card border-0 shadow-sm h-100">
							<div class="card-header bg-light py-3">
								<h5 class="card-title mb-0">
									<i class="fa fa-info-circle me-2 text-primary"></i>Task Details
								</h5>
							</div>
							<div class="card-body">
								<div class="mb-4">
									<h6 class="text-muted mb-3 fw-bold">Title</h6>
									<p class="fs-4 fw-medium mb-0"><?=$task['title']?></p>
								</div>
								
								<hr class="my-4 text-muted">
								
								<div>
									<h6 class="text-muted mb-3 fw-bold">Description</h6>
									<p class="fs-5 mb-0 text-dark"><?=$task['description']?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Update Status Card -->
					<div class="col-md-6 mb-4">
						<div class="card border-0 shadow-sm h-100">
							<div class="card-header bg-light py-3">
								<h5 class="card-title mb-0">
									<i class="fa fa-edit me-2 text-primary"></i>Update Status
								</h5>
							</div>
							<div class="card-body">
								<form method="POST" action="app/update-task-employee.php">
									<div class="mb-4">
										<label for="status" class="form-label">Current Status</label>
										<select name="status" id="status" class="form-select form-select-lg">
											<option value="pending" <?php if($task['status'] == "pending") echo "selected"; ?>>
												Pending
											</option>
											<option value="in_progress" <?php if($task['status'] == "in_progress") echo "selected"; ?>>
												In Progress
											</option>
											<option value="completed" <?php if($task['status'] == "completed") echo "selected"; ?>>
												Completed
											</option>
										</select>
									</div>

									<input type="hidden" name="id" value="<?=$task['id']?>">

									<div class="d-grid gap-2">
										<button type="submit" class="btn btn-primary btn-lg" style="background-color: #1a237e; border-color: #1a237e;">
											<i class="fa fa-save me-2"></i>Update Status
										</button>
										<a href="my_task.php" class="btn btn-outline-secondary btn-lg">
											<i class="fa fa-arrow-left me-2"></i>Back to Tasks
										</a>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(2)");
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