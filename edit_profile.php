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
	<title>Edit Profile</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body, .body, .section-1 {
			background-color: white !important;
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
				<!-- Alert Messages -->
				<?php if (isset($_GET['error'])) {?>
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<?php echo stripcslashes($_GET['error']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php } ?>

				<?php if (isset($_GET['success'])) {?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<?php echo stripcslashes($_GET['success']); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				<?php } ?>
				
				<!-- Profile Edit Form -->
				<div class="row">
					<div class="col-md-8 mx-auto">
						<div class="card border-0 shadow-sm rounded-3">
							<div class="card-body p-4">
								<div class="d-flex justify-content-between align-items-center mb-4">
									<h5 class="card-title fw-bold mb-0">Edit Profile</h5>
									<a href="profile.php" class="btn btn-sm btn-light rounded-pill">
										<i class="fa fa-arrow-left me-1"></i>
										Back to Profile
									</a>
								</div>
								<hr class="mb-4">
								<h6 class="fw-medium text-muted mb-4">Update Profile Information</h6>
								
								<form method="POST" action="app/update-profile.php">
									<div class="mb-3">
										<label for="fullName" class="form-label">Full Name</label>
										<input type="text" name="full_name" class="form-control" id="fullName" placeholder="Full Name" value="<?=$user['full_name']?>">
									</div>

									<div class="mb-3">
										<label for="oldPassword" class="form-label">Old Password</label>
										<input type="password" value="**********" name="password" class="form-control" id="oldPassword" placeholder="Old Password">
									</div>
									
									<div class="mb-3">
										<label for="newPassword" class="form-label">New Password</label>
										<input type="password" name="new_password" class="form-control" id="newPassword" placeholder="New Password">
									</div>
									
									<div class="mb-3">
										<label for="confirmPassword" class="form-label">Confirm Password</label>
										<input type="password" name="confirm_password" class="form-control" id="confirmPassword" placeholder="Confirm Password">
									</div>

									<div class="d-grid gap-2 d-md-flex justify-content-md-end">
										<a href="profile.php" class="btn btn-light me-md-2">Cancel</a>
										<button type="submit" class="btn btn-primary px-4">Save Changes</button>
									</div>
								</form>
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