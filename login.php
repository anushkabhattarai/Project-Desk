<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login | Task Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
		    <!-- Password requirements removed -->
		  </div>
		  <div class="mb-3 d-flex justify-content-between align-items-center">
		    <div class="form-check">
		        <input type="checkbox" class="form-check-input" id="showPassword">
		        <label class="form-check-label" for="showPassword">Show password</label>
		    </div>
		    <a href="forgot-password.php" class="text-primary text-decoration-none">Forgot Password?</a>
		  </div>
		  <button type="submit" class="btn btn-primary" id="submitBtn">Login</button>
		  <div class="mt-3 text-center">
		    <a href="signup.php" class="text-decoration-none">New member? Sign up</a>
		  </div>
		</form>


      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
      <script>
        // Show/hide password functionality only
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });

        // Remove password validation
        document.getElementById('submitBtn').disabled = false;
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