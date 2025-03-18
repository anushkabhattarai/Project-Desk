<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee") {
    include "DB_connection.php";
    include "app/Model/User.php";
    $user = get_user_by_id($conn, $_SESSION['id']);
    
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Profile</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body, .body, .section-1 {
			background-color: white !important;
		}
		
		.card {
			border: none !important;
			box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
			transition: all 0.3s ease !important;
		}
		
		.card:hover {
			box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
			transform: translateY(-2px);
		}
		
		.btn {
			box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
			border: none !important;
		}
		
		.btn:hover {
			box-shadow: 0 6px 18px rgba(0,0,0,0.1) !important;
		}
	</style>
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body bg-white">
		<?php include "inc/nav.php" ?>
		<section class="section-1 bg-white">
			<div class="container-fluid px-4 py-3">
				<!-- Enhanced Header Area -->
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h4 class="mb-1 fw-bold">My Profile</h4>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
								<li class="breadcrumb-item active">Profile</li>
							</ol>
						</nav>
					</div>
					<a href="edit_profile.php" class="btn btn-primary rounded-pill px-4">
						<i class="fa fa-pencil me-2"></i>Edit Profile
					</a>
				</div>
				
				<!-- Profile Card -->
				<div class="row">
					<div class="col-md-8 col-lg-6 mx-auto">
						<div class="card rounded-4">
							<div class="card-body p-4">
								<!-- User Avatar -->
								<div class="text-center mb-4">
									<div class="avatar bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
										<i class="fa fa-user fa-3x"></i>
									</div>
									<h3 class="fw-bold"><?=$user['full_name']?></h3>
									<span class="badge bg-success-subtle text-success px-3 py-2">
										<?=ucfirst($user['role'])?>
									</span>
								</div>
								
								<!-- User Info -->
								<div class="row">
									<div class="col-md-10 mx-auto">
										<div class="list-group list-group-flush">
											<div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
												<span class="text-muted">
													<i class="fa fa-user-circle-o me-2"></i>Username
												</span>
												<span class="fw-medium"><?=$user['username']?></span>
											</div>
											<div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
												<span class="text-muted">
													<i class="fa fa-envelope me-2"></i>Email
												</span>
												<span class="fw-medium"><?=$user['email'] ?? 'Not provided'?></span>
											</div>
											<div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
												<span class="text-muted">
													<i class="fa fa-calendar me-2"></i>Joined At
												</span>
												<span class="fw-medium"><?=$user['created_at']?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<!-- Additional Card for Stats (optional) -->
						<div class="card rounded-4 mt-4">
							<div class="card-body p-4">
								<h5 class="card-title fw-bold mb-3">Task Statistics</h5>
								<div class="row text-center">
									<div class="col-4">
										<div class="rounded-circle bg-primary-subtle p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
											<i class="fa fa-tasks text-primary fa-lg"></i>
										</div>
										<h3 class="fw-bold mb-0">12</h3>
										<p class="text-muted small">Total Tasks</p>
									</div>
									<div class="col-4">
										<div class="rounded-circle bg-warning-subtle p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
											<i class="fa fa-clock-o text-warning fa-lg"></i>
										</div>
										<h3 class="fw-bold mb-0">5</h3>
										<p class="text-muted small">Pending</p>
									</div>
									<div class="col-4">
										<div class="rounded-circle bg-success-subtle p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
											<i class="fa fa-check-circle text-success fa-lg"></i>
										</div>
										<h3 class="fw-bold mb-0">7</h3>
										<p class="text-muted small">Completed</p>
									</div>
								</div>
							</div>
						</div>
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