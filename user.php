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
	<title>Manage Users</title>
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
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h4 class="mb-1">Manage Users</h4>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
								<li class="breadcrumb-item active">Users</li>
							</ol>
						</nav>
					</div>
					<div>
						<a href="add-user.php" class="btn btn-primary" style="background-color: #1a237e; border-color: #1a237e;">
							<i class="fa fa-plus me-2"></i>Add User
						</a>
					</div>
				</div>

				<?php if (isset($_GET['success'])) { ?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<?php echo stripcslashes($_GET['success']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php } ?>

				<!-- Users Table -->
				<?php if ($users != 0) { ?>
					<div class="card border-0 shadow-sm">
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-hover mb-0">
									<thead class="bg-light">
										<tr>
											<th class="border-0">#</th>
											<th class="border-0">Full Name</th>
											<th class="border-0">Username</th>
											<th class="border-0">Role</th>
											<th class="border-0">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php $i=0; foreach ($users as $user) { ?>
											<tr>
												<td><?=++$i?></td>
												<td><?=$user['full_name']?></td>
												<td><?=$user['username']?></td>
												<td><span class="badge bg-primary"><?=$user['role']?></span></td>
												<td>
													<div class="btn-group btn-group-sm">
														<a href="edit-user.php?id=<?=$user['id']?>" class="btn btn-outline-primary">
															<i class="fa fa-edit"></i>
														</a>
														<a href="delete-user.php?id=<?=$user['id']?>" class="btn btn-outline-danger">
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
						<h3 class="text-muted">No users found</h3>
					</div>
				<?php } ?>
			</div>
		</section>
	</div>

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