<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    
    if (!isset($_GET['id'])) {
    	 header("Location: user.php");
    	 exit();
    }
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);

    if ($user == 0) {
    	 header("Location: user.php");
    	 exit();
    }

 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit User</title>
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
				<div class="mb-4">
					<h4 class="mb-1">Edit User</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
							<li class="breadcrumb-item"><a href="user.php" class="text-decoration-none">Users</a></li>
							<li class="breadcrumb-item active">Edit User</li>
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

				<!-- Edit User Form -->
				<div class="card border-0 shadow-sm">
					<div class="card-body p-4">
						<form method="POST" action="app/update-user.php" enctype="multipart/form-data">
							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="full_name" class="form-label">Full Name</label>
									<input type="text" 
										   class="form-control" 
										   id="full_name" 
										   name="full_name" 
										   value="<?=$user['full_name']?>"
										   required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="user_name" class="form-label">Username</label>
									<input type="text" 
										   class="form-control" 
										   id="user_name" 
										   name="user_name" 
										   value="<?=$user['username']?>"
										   required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="password" class="form-label">Password</label>
									<div class="input-group">
										<input type="password" 
											   class="form-control" 
											   id="password" 
											   name="password" 
											   placeholder="Enter new password"
											   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}"
											   title="Password must contain at least 8 characters, including uppercase, lowercase, numbers and special characters">
										<button class="btn btn-outline-secondary" type="button" id="togglePassword">
											<i class="fa fa-eye"></i>
										</button>
									</div>
									<small class="text-muted">Leave blank to keep current password</small>
									<div id="passwordRequirements" class="mt-2" style="display: none;">
										<p class="mb-1">Password must contain:</p>
										<ul class="list-unstyled">
											<li id="length" class="text-danger"><i class="fa fa-times-circle"></i> At least 8 characters</li>
											<li id="uppercase" class="text-danger"><i class="fa fa-times-circle"></i> One uppercase letter</li>
											<li id="lowercase" class="text-danger"><i class="fa fa-times-circle"></i> One lowercase letter</li>
											<li id="number" class="text-danger"><i class="fa fa-times-circle"></i> One number</li>
											<li id="special" class="text-danger"><i class="fa fa-times-circle"></i> One special character (!@#$%^&*)</li>
										</ul>
									</div>
								</div>

								<div class="col-md-6 mb-3">
									<label for="role" class="form-label">Role</label>
									<select class="form-select" id="role" name="role" required>
										<option value="employee" <?=$user['role'] == 'employee' ? 'selected' : ''?>>Employee</option>
										<option value="admin" <?=$user['role'] == 'admin' ? 'selected' : ''?>>Admin</option>
									</select>
								</div>

								<input type="hidden" name="id" value="<?=$user['id']?>">
								<input type="hidden" name="current_role" value="<?=$user['role']?>">

								<div class="col-12">
									<button type="submit" class="btn btn-primary" style="background-color: #1a237e; border-color: #1a237e;">
										<i class="fa fa-save me-2"></i>Update User
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
	// Password validation
	const password = document.getElementById('password');
	const requirements = document.getElementById('passwordRequirements');
	const togglePassword = document.getElementById('togglePassword');

	// Show/hide password requirements when password field is focused
	password.addEventListener('focus', () => {
	    requirements.style.display = 'block';
	});

	// Validate password as user types
	password.addEventListener('input', function() {
	    const value = this.value;
	    
	    // Only validate if password is not empty (since it's optional in edit)
	    if (value) {
	        // Check each requirement
	        document.getElementById('length').className = value.length >= 8 ? 'text-success' : 'text-danger';
	        document.getElementById('uppercase').className = /[A-Z]/.test(value) ? 'text-success' : 'text-danger';
	        document.getElementById('lowercase').className = /[a-z]/.test(value) ? 'text-success' : 'text-danger';
	        document.getElementById('number').className = /\d/.test(value) ? 'text-success' : 'text-danger';
	        document.getElementById('special').className = /[!@#$%^&*]/.test(value) ? 'text-success' : 'text-danger';

	        // Update icons
	        document.querySelectorAll('#passwordRequirements li').forEach(li => {
	            const icon = li.querySelector('i');
	            if (li.classList.contains('text-success')) {
	                icon.className = 'fa fa-check-circle';
	            } else {
	                icon.className = 'fa fa-times-circle';
	            }
	        });
	    } else {
	        // Reset all to default state if password field is empty
	        document.querySelectorAll('#passwordRequirements li').forEach(li => {
	            li.className = 'text-danger';
	            li.querySelector('i').className = 'fa fa-times-circle';
	        });
	    }
	});

	// Toggle password visibility
	togglePassword.addEventListener('click', function() {
	    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
	    password.setAttribute('type', type);
	    
	    // Toggle eye icon
	    const icon = this.querySelector('i');
	    icon.className = type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
	});

	// Form submission validation
	document.querySelector('form').addEventListener('submit', function(e) {
	    const password = document.getElementById('password').value;
	    
	    if (password) { // Only validate if a new password is being set
	        const isValid = 
	            password.length >= 8 &&
	            /[A-Z]/.test(password) &&
	            /[a-z]/.test(password) &&
	            /\d/.test(password) &&
	            /[!@#$%^&*]/.test(password);
	            
	        if (!isValid) {
	            e.preventDefault();
	            alert('Please ensure the password meets all requirements');
	        }
	    }
	});
	</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>