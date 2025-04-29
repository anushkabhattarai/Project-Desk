<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login | Project Desk</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
	<link rel="stylesheet" href="css/style.css">
	<style>
		body {
			min-height: 100vh;
			margin: 0;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		}

		.split-container {
			display: flex;
			min-height: 100vh;
		}

		.info-section {
			flex: 1;
			padding: 2rem;
			color: white;
			display: flex;
			flex-direction: column;
			justify-content: center;
			position: relative;
			overflow: hidden;
		}

		.info-section::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(255, 255, 255, 0.1);
			backdrop-filter: blur(10px);
			z-index: 1;
		}

		.info-content {
			position: relative;
			z-index: 2;
			max-width: 600px;
			margin: 0 auto;
		}

		.login-section {
			flex: 1;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 2rem;
			background: rgba(255, 255, 255, 0.95);
		}

		.login-form {
			width: 100%;
			max-width: 400px;
			padding: 2rem;
			border-radius: 15px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
			background: white;
		}

		.project-title {
			font-size: 2.5rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
			color: white;
		}

		.project-description {
			font-size: 1.1rem;
			line-height: 1.6;
			margin-bottom: 2rem;
			color: rgba(255, 255, 255, 0.9);
		}

		.feature-list {
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.feature-item {
			display: flex;
			align-items: center;
			margin-bottom: 1rem;
			color: rgba(255, 255, 255, 0.9);
		}

		.feature-item i {
			margin-right: 1rem;
			color: #fff;
			font-size: 1.2rem;
		}

		.form-control {
			border-radius: 8px;
			padding: 12px;
			border: 1px solid #e0e0e0;
			transition: all 0.3s ease;
		}

		.form-control:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
		}

		.btn-primary {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			padding: 12px 30px;
			border-radius: 8px;
			font-weight: 600;
			transition: all 0.3s ease;
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
		}

		.input-group-text {
			background: transparent;
			border-right: none;
		}

		.form-control {
			border-left: none;
		}

		.input-group:focus-within {
			box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
			border-radius: 8px;
		}

		.login-link {
			color: #667eea;
			text-decoration: none;
			font-weight: 500;
			transition: color 0.3s ease;
		}

		.login-link:hover {
			color: #764ba2;
		}

		@media (max-width: 768px) {
			.split-container {
				flex-direction: column;
			}
			
			.info-section {
				padding: 2rem 1rem;
			}
			
			.login-section {
				padding: 2rem 1rem;
			}
		}
	</style>
</head>
<body>
	<div class="split-container">
		<div class="info-section">
			<div class="info-content">
				<h1 class="project-title animate__animated animate__fadeIn">Project Desk</h1>
				<p class="project-description animate__animated animate__fadeIn animate__delay-1s">
					Your comprehensive platform for task management and collaborative note-taking. 
					Organize your work, share ideas, and collaborate seamlessly with your team.
				</p>
				<ul class="feature-list">
					<li class="feature-item animate__animated animate__fadeIn animate__delay-2s">
						<i class="fas fa-tasks"></i>
						<span>Manage tasks with ease - create, assign, and track progress</span>
					</li>
					<li class="feature-item animate__animated animate__fadeIn animate__delay-3s">
						<i class="fas fa-sticky-note"></i>
						<span>Create and organize notes with rich text formatting</span>
					</li>
					<li class="feature-item animate__animated animate__fadeIn animate__delay-4s">
						<i class="fas fa-share-alt"></i>
						<span>Share notes and tasks with team members instantly</span>
					</li>
					<li class="feature-item animate__animated animate__fadeIn animate__delay-5s">
						<i class="fas fa-comments"></i>
						<span>Collaborate in real-time with comments and discussions</span>
					</li>
				</ul>
			</div>
		</div>

		<div class="login-section">
			<form method="POST" action="app/login.php" class="login-form animate__animated animate__fadeIn" id="loginForm">
				<h3 class="text-center mb-4">Welcome Back!</h3>
				<p class="text-center text-muted mb-4">Sign in to continue to your account</p>

				<?php if (isset($_GET['error'])) {?>
					<div class="alert alert-danger animate__animated animate__shakeX" role="alert">
						<i class="fas fa-exclamation-circle"></i> <?php echo stripcslashes($_GET['error']); ?>
					</div>
				<?php } ?>

				<?php if (isset($_GET['success'])) {?>
					<div class="alert alert-success animate__animated animate__fadeIn" role="alert">
						<i class="fas fa-check-circle"></i> <?php echo stripcslashes($_GET['success']); ?>
					</div>
				<?php } ?>

				<div class="mb-4">
					<label for="username" class="form-label">Username</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fas fa-user"></i></span>
						<input type="text" class="form-control" name="user_name" id="username" placeholder="Enter your username" required>
					</div>
				</div>

				<div class="mb-4">
					<label for="password" class="form-label">Password</label>
					<div class="input-group">
						<span class="input-group-text"><i class="fas fa-lock"></i></span>
						<input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
					</div>
				</div>

				<div class="mb-4 d-flex justify-content-between align-items-center">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="showPassword">
						<label class="form-check-label" for="showPassword">Show password</label>
					</div>
					<a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
				</div>

				<button type="submit" class="btn btn-primary w-100 mb-4" id="submitBtn">Login</button>
				
				<div class="text-center">
					<span class="text-muted">Don't have an account?</span>
					<a href="signup.php" class="login-link ms-2">Sign up</a>
				</div>
			</form>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		document.getElementById('showPassword').addEventListener('change', function() {
			const passwordInput = document.getElementById('password');
			passwordInput.type = this.checked ? 'text' : 'password';
		});
	</script>
</body>
</html>