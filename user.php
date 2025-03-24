<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $users = get_all_users($conn);
    /* Remove or comment out these debug lines
    if ($users !== 0) {
        echo "Total users found: " . count($users);
    }
    if ($users === 0) {
        echo "Error fetching users: ";
        if (mysqli_error($conn)) {
            echo mysqli_error($conn);
        }
    }
    */
  
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Manage Users</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		/* Match the nice styles from other pages */
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
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h4 class="mb-1">Manage Users</h4>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
								<li class="breadcrumb-item active">Users</li>
							</ol>
						</nav>
						<?php if ($users !== 0) { ?>
							<small class="text-muted">Total users: <?php echo count($users); ?></small>
						<?php } ?>
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
						<div class="card border-0 shadow-sm">
							<div class="card-body py-5">
								<h3 class="text-muted">No users found</h3>
								<p class="text-muted mb-0">Start by adding a new user</p>
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
	</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>