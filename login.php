<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login | ProjectDesk</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/style.css">
	<style>
		.password-requirements {
			font-size: 0.85rem;
			color: #6c757d;
			margin-top: 0.5rem;
		}
		.password-requirements ul {
			padding-left: 1.2rem;
			margin-bottom: 0;
		}
		.requirement {
			transition: color 0.3s ease;
		}
		.requirement.valid {
			color: #198754;
		}
		.requirement.invalid {
			color: #dc3545;
		}
		.requirement i {
			margin-right: 5px;
		}
		.input-error {
			color: #dc3545;
			font-size: 0.875rem;
			margin-top: 0.25rem;
			display: none;
		}
	</style>
</head>
<body class="login-body">
	<form method="POST" action="app/login.php" class="shadow p-4" id="loginForm" novalidate>
      	  <h3 class="display-4">LOGIN</h3>
      	  <?php if (isset($_GET['error'])) {?>
      	  	<div class="alert alert-danger" role="alert">
			  <?php echo stripcslashes($_GET['error']); ?>
			</div>
      	  <?php } ?>

		<div class="mb-3">
			<label for="username" class="form-label">Username</label>
			<input type="text" 
				   class="form-control" 
				   name="user_name" 
				   id="username" 
				   required>
			<div class="input-error" id="usernameError">Username is required</div>
			</div>
			
		  <div class="mb-3">
			<label for="password" class="form-label">Password</label>
			<input type="password" 
				   class="form-control" 
				   name="password" 
				   id="password" 
				   required>
			<div class="input-error" id="passwordError">Password is required</div>
			<div class="password-requirements mt-2">
				Password must meet the following requirements:
				<ul>
					<li class="requirement" id="length">
						<i class="fa fa-times-circle"></i>
						8-12 characters long
					</li>
					<li class="requirement" id="uppercase">
						<i class="fa fa-times-circle"></i>
						At least one uppercase letter
					</li>
					<li class="requirement" id="lowercase">
						<i class="fa fa-times-circle"></i>
						At least one lowercase letter
					</li>
					<li class="requirement" id="number">
						<i class="fa fa-times-circle"></i>
						At least one number
					</li>
					<li class="requirement" id="special">
						<i class="fa fa-times-circle"></i>
						At least one special character
					</li>
				</ul>
		  </div>
		  </div>

		<button type="submit" class="btn btn-primary" id="submitBtn" disabled>Login</button>
		</form>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const form = document.getElementById('loginForm');
			const username = document.getElementById('username');
			const password = document.getElementById('password');
			const submitBtn = document.getElementById('submitBtn');
			const usernameError = document.getElementById('usernameError');
			const passwordError = document.getElementById('passwordError');
			
			// Password requirement elements
			const requirements = {
				length: document.getElementById('length'),
				uppercase: document.getElementById('uppercase'),
				lowercase: document.getElementById('lowercase'),
				number: document.getElementById('number'),
				special: document.getElementById('special')
			};

			// Validation functions
			const validators = {
				length: (value) => value.length >= 8 && value.length <= 12,
				uppercase: (value) => /[A-Z]/.test(value),
				lowercase: (value) => /[a-z]/.test(value),
				number: (value) => /[0-9]/.test(value),
				special: (value) => /[!@#$%^&*(),.?":{}|<>]/.test(value)
			};

			// Update requirement status
			function updateRequirement(element, valid) {
				element.classList.toggle('valid', valid);
				element.classList.toggle('invalid', !valid);
				element.querySelector('i').className = valid ? 
					'fa fa-check-circle' : 'fa fa-times-circle';
			}

			// Validate password and update UI
			function validatePassword() {
				const value = password.value;
				let allValid = true;

				// Check each requirement
				for (const [key, validator] of Object.entries(validators)) {
					const isValid = validator(value);
					updateRequirement(requirements[key], isValid);
					allValid = allValid && isValid;
				}

				// Show/hide error message
				passwordError.style.display = value ? 'none' : 'block';
				
				return allValid;
			}

			// Validate username
			function validateUsername() {
				const valid = username.value.trim() !== '';
				usernameError.style.display = valid ? 'none' : 'block';
				return valid;
			}

			// Update submit button state
			function updateSubmitButton() {
				const usernameValid = validateUsername();
				const passwordValid = validatePassword();
				submitBtn.disabled = !(usernameValid && passwordValid);
			}

			// Event listeners
			username.addEventListener('input', updateSubmitButton);
			password.addEventListener('input', updateSubmitButton);

			// Form submission
			form.addEventListener('submit', function(e) {
				if (!validateUsername() || !validatePassword()) {
					e.preventDefault();
					return false;
				}
				return true;
			});

			// Initial validation
			updateSubmitButton();
		});
	</script>
</body>
</html>