<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login | Task Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
      
      <form method="POST" action="app/login.php" class="shadow p-4" id="loginForm">

      	  <h3 class="display-4">LOGIN</h3>
      	  <?php if (isset($_GET['error'])) {?>
      	  	<div class="alert alert-danger" role="alert">
			  <?php echo stripcslashes($_GET['error']); ?>
			</div>
      	  <?php } ?>

      	  <?php if (isset($_GET['success'])) {?>
      	  	<div class="alert alert-success" role="alert">
			  <?php echo stripcslashes($_GET['success']); ?>
			</div>
      	  <?php } 

                // $pass = "123";
                // $pass = password_hash($pass, PASSWORD_DEFAULT);
                // echo $pass;
      
      	  ?>
  
			
		  <div class="mb-3">
		    <label for="username" class="form-label">User name</label>
		    <input type="text" class="form-control" name="user_name" id="username" required>
		  </div>
		  <div class="mb-3">
		    <label for="password" class="form-label">Password</label>
		    <input type="password" class="form-control" name="password" id="password" required>
		    <div id="passwordHelp" class="form-text">
		        Password must contain:
		        <ul class="small mb-0">
		            <li id="uppercase" class="text-danger">One uppercase letter (A-Z)</li>
		            <li id="lowercase" class="text-danger">One lowercase letter (a-z)</li>
		            <li id="number" class="text-danger">One number (0-9)</li>
		            <li id="special" class="text-danger">One special character (@#$%&)</li>
		            <li id="length" class="text-danger">Minimum 8 characters</li>
		        </ul>
		    </div>
		  </div>
		  <div class="mb-3 d-flex justify-content-between align-items-center">
		    <div class="form-check">
		        <input type="checkbox" class="form-check-input" id="showPassword">
		        <label class="form-check-label" for="showPassword">Show password</label>
		    </div>
		    <a href="forgot-password.php" class="text-primary text-decoration-none">Forgot Password?</a>
		  </div>
		  <button type="submit" class="btn btn-primary" id="submitBtn">Login</button>
		</form>


      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
      <script>
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            // Check each requirement
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[@#$%&]/.test(password);
            const hasLength = password.length >= 8;

            // Update visual indicators
            document.getElementById('uppercase').className = hasUpperCase ? 'text-success' : 'text-danger';
            document.getElementById('lowercase').className = hasLowerCase ? 'text-success' : 'text-danger';
            document.getElementById('number').className = hasNumber ? 'text-success' : 'text-danger';
            document.getElementById('special').className = hasSpecial ? 'text-success' : 'text-danger';
            document.getElementById('length').className = hasLength ? 'text-success' : 'text-danger';

            // Enable/disable submit button based on all requirements being met
            const submitBtn = document.getElementById('submitBtn');
            if (hasUpperCase && hasLowerCase && hasNumber && hasSpecial && hasLength) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        });

        // Initially disable submit button
        document.getElementById('submitBtn').disabled = true;

        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    </script>

    <style>
    .login-body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
    }

    form {
        width: 100%;
        max-width: 400px;
        background: white;
        border-radius: 8px;
    }

    #passwordHelp ul {
        list-style-type: none;
        padding-left: 0;
    }

    #passwordHelp li {
        margin-top: 2px;
    }

    #passwordHelp li::before {
        content: '•';
        margin-right: 5px;
    }

    .text-success {
        color: #198754 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }
    </style>
</body>
</html>