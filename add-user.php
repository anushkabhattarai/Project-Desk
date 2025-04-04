<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
  
?>
<!DOCTYPE html>
<html>
<head>
	<title>Add User</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<?php include "inc/nav.php" ?>
	
	<!-- Main content area with margin-left to account for sidebar width -->
	<main style="margin-left: 250px;ss">
		<section class="section-1 bg-white">
			<div class="container-fluid px-4 py-3">
				<!-- Header Area -->
				<div class="mb-4">
					<h4 class="mb-1">Add User</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
							<li class="breadcrumb-item"><a href="user.php" class="text-decoration-none">Users</a></li>
							<li class="breadcrumb-item active">Add User</li>
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

				<!-- Add User Form -->
				<div class="card border-0 shadow-sm">
					<div class="card-body p-4">
						<form method="POST" action="app/add-user.php">
							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="full_name" class="form-label">Full Name</label>
									<input type="text" 
										   class="form-control" 
										   id="full_name" 
										   name="full_name" 
										   placeholder="Enter full name"
										   required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="user_name" class="form-label">Username</label>
									<input type="text" 
										   class="form-control" 
										   id="user_name" 
										   name="user_name" 
										   placeholder="Enter username"
										   required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="password" class="form-label">Password</label>
									<input type="password" 
										   class="form-control" 
										   id="password" 
										   name="password" 
										   placeholder="Enter password"
										   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}"
										   title="Must contain at least one number, one uppercase and lowercase letter, one special character, and at least 8 characters"
										   required>
									<small class="text-muted">
										Password must contain at least 8 characters, including uppercase, lowercase, numbers and special characters
									</small>
								</div>

								<div class="col-md-6 mb-3">
									<label for="role" class="form-label">Role</label>
									<select class="form-select" id="role" name="role" required>
										<option value="">Select role</option>
										<option value="employee">Employee</option>
										<option value="admin">Admin</option>
									</select>
								</div>

								<div class="col-12">
									<button type="submit" class="btn btn-primary" style="background-color: #1a237e; border-color: #1a237e;">
										<i class="fa fa-plus me-2"></i>Add User
									</button>
									<a href="user.php" class="btn btn-outline-secondary">
										Cancel
									</a>
								</div>
							</div>
						</form>
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
	<script>
	document.getElementById('password').addEventListener('input', function() {
	    const password = this.value;
	    const pattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/;
	    
	    if (pattern.test(password)) {
	        this.setCustomValidity('');
	    } else {
	        this.setCustomValidity('Password must contain at least 8 characters, including uppercase, lowercase, numbers and special characters');
	    }
	});
	</script>
</body>
</html>
<?php } else { 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>