<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";
    include "app/Model/User.php";
    $user = get_user_by_id($conn, $_SESSION['id']);

    // Add password verification on form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            header("Location: edit_profile.php?error=Current password is incorrect");
            exit();
        }
        
        // Continue with form processing if password is correct
        // ...rest of your form processing code
    }
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
			background-color: #ffffff !important;
		}
		.section-1 {
			margin-top: 60px;
			padding-top: 20px;
		}
		.form-container {
			background-color: #ffffff;
			padding: 30px;
			border-radius: 15px;
			box-shadow: 0 0 20px rgba(0,0,0,0.03);
		}
		.form-control {
			border-radius: 8px;
			border: 1px solid #e0e0e0;
			padding: 10px 12px;
			font-size: 0.9rem;
			transition: all 0.3s;
		}
		.form-control:focus {
			border-color: #0d6efd;
			box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
		}
		.form-label {
			font-weight: 500;
			color: #566a7f;
			margin-bottom: 6px;
			font-size: 0.85rem;
		}
		.btn {
			padding: 8px 16px;
			border-radius: 8px;
			font-weight: 500;
			font-size: 0.9rem;
			transition: all 0.3s;
		}
		.btn-primary {
			background: linear-gradient(45deg, #0d6efd, #0099ff);
			border: none;
		}
		.btn-primary:hover {
			background: linear-gradient(45deg, #0099ff, #0d6efd);
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(13,110,253,0.2);
		}
		.page-title {
			color: #2d3748;
			font-size: 1.3rem;
			margin-bottom: 1.5rem;
		}
		.user-info {
			background: linear-gradient(45deg, #f8f9fa, #ffffff);
			border-radius: 12px;
			padding: 15px;
			margin-bottom: 20px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.05);
			font-size: 0.85rem;
		}
		.user-info i {
			color: #0d6efd;
			width: 20px;
		}
		.badge {
			font-size: 0.75rem;
		}
		.form-control-lg {
			font-size: 0.9rem;
			padding: 10px 12px;
		}
		.alert {
			margin-bottom: 1.5rem;
			border: none;
			border-radius: 8px;
			font-size: 0.9rem;
		}
		.alert-danger {
			background-color: #fff2f2;
			color: #dc3545;
			border-left: 4px solid #dc3545;
		}
		.alert-success {
			background-color: #f0fff4;
			color: #28a745;
			border-left: 4px solid #28a745;
		}
	</style>
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body bg-white">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
			<div class="container-fluid px-4 py-3">
				<div class="row">
					<div class="col-md-8 mx-auto">
						<!-- Move Alert Messages here, before the form-container -->
						<?php if (isset($_GET['error'])) { ?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<i class="fa fa-exclamation-circle me-2"></i>
								<?php echo stripcslashes($_GET['error']); ?>
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
						<?php } ?>

						<?php if (isset($_GET['success'])) { ?>
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<i class="fa fa-check-circle me-2"></i>
								<?php echo stripcslashes($_GET['success']); ?>
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
						<?php } ?>

						<div class="form-container">
							<div class="d-flex justify-content-between align-items-center mb-4">
								<h5 class="page-title mb-0">
									<?php if ($_SESSION['role'] == 'admin'): ?>
										<i class="fa fa-shield text-primary me-2"></i>Admin Profile Settings
									<?php else: ?>
										<i class="fa fa-user text-primary me-2"></i>Edit Profile
									<?php endif; ?>
								</h5>
								<a href="<?= $_SESSION['role'] == 'admin' ? 'index.php' : 'profile.php' ?>" 
								   class="btn btn-sm btn-light rounded-pill">
									<i class="fa fa-arrow-left me-1"></i>Back
								</a>
							</div>

							<?php if ($_SESSION['role'] == 'admin'): ?>
								<div class="user-info">
									<div class="mb-2">
										<i class="fa fa-user-circle me-2"></i>
										<strong>Role:</strong> <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
									</div>
									<div class="mb-2">
										<i class="fa fa-calendar me-2"></i>
										<strong>Created:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?>
									</div>
									<div>
										<i class="fa fa-envelope me-2"></i>
										<strong>Email:</strong> <?= $user['email'] ?? 'Not set' ?>
									</div>
								</div>
							<?php endif; ?>
							
							<form method="POST" action="" class="needs-validation" novalidate>
								<div class="row">
									<div class="col-12">
										<div class="mb-4">
											<label for="fullName" class="form-label">
												<i class="fa fa-user-circle text-primary me-2"></i>Full Name
											</label>
											<input type="text" name="full_name" 
												   class="form-control form-control-lg" 
												   id="fullName" 
												   value="<?=$user['full_name']?>" required>
										</div>
									</div>

									<?php if ($_SESSION['role'] == 'admin'): ?>
										<div class="col-12">
											<div class="mb-4">
												<label for="email" class="form-label">
													<i class="fa fa-envelope text-primary me-2"></i>Email Address
												</label>
												<input type="email" name="email" 
													   class="form-control form-control-lg" 
													   id="email" 
													   value="<?=$user['email']?>" required>
											</div>
										</div>
									<?php endif; ?>

									<div class="col-12">
										<div class="mb-4">
											<label for="oldPassword" class="form-label">
												<i class="fa fa-lock text-primary me-2"></i>Current Password
											</label>
											<input type="password" name="password" 
												   class="form-control form-control-lg" 
												   id="oldPassword" required>
											<div class="form-text text-muted">
												Enter your current password to verify changes
											</div>
										</div>
									</div>

									<div class="col-md-6">
										<div class="mb-4">
											<label for="newPassword" class="form-label">
												<i class="fa fa-key text-primary me-2"></i>New Password
											</label>
											<input type="password" name="new_password" 
												   class="form-control form-control-lg" 
												   id="newPassword" 
												   placeholder="Leave blank to keep current">
										</div>
									</div>

									<div class="col-md-6">
										<div class="mb-4">
											<label for="confirmPassword" class="form-label">
												<i class="fa fa-check text-primary me-2"></i>Confirm Password
											</label>
											<input type="password" name="confirm_password" 
												   class="form-control form-control-lg" 
												   id="confirmPassword" 
												   placeholder="Confirm new password">
										</div>
									</div>
								</div>

								<div class="d-flex justify-content-end mt-4">
									<a href="<?= $_SESSION['role'] == 'admin' ? 'index.php' : 'profile.php' ?>" 
									   class="btn btn-light btn-lg me-2">
										<i class="fa fa-times me-2"></i>Cancel
									</a>
									<button type="submit" class="btn btn-primary btn-lg">
										<i class="fa fa-save me-2"></i>Save Changes
									</button>
								</div>
							</form>
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