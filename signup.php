<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up | Project Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 4rem);
        }
        .gradient-container {
            background: linear-gradient(145deg, #ffffff 0%, #f5f5f5 100%);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px; /* Reduced from 480px */
            margin: auto;
        }
        .signup-form {
            width: 100%;
            max-width: 340px; /* Reduced from 380px */
            margin: 0 auto;
            padding: 0.5rem;
        }
        .user-icon {
            font-size: 1.5rem;
            color: #1a73e8;
            margin-bottom: 1rem;
        }
        h3 {
            color: #202124;
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            color: #5f6368;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }
        .form-control {
            border: 1px solid #dadce0;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            background: white;
        }
        .form-control:focus {
            border-color: #1a73e8;
            box-shadow: none;
        }
        .btn-primary {
            background: #1a73e8;
            border: none;
            padding: 0.75rem;
            border-radius: 4px;
            font-weight: 500;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background: #1557b0;
        }
        .login-link {
            color: #1a73e8;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link:hover {
            color: #1557b0;
        }
        .back-link {
            color: #1a73e8;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            color: #1557b0;
        }
        .success-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
            animation: slideIn 0.5s ease forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 576px) {
            .gradient-container {
                padding: 1.25rem; /* Slightly reduced padding for mobile */
                max-width: 90%;   /* Added responsive width */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gradient-container">
            <div class="signup-form">
                <div class="text-center">
                    <i class="fas fa-user-plus user-icon"></i>
                    <h3>Create Account</h3>
                    <p class="subtitle">Join Project Desk to manage your projects</p>
                </div>

                <?php if (isset($_GET['error'])) {?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo stripcslashes($_GET['error']); ?>
                    </div>
                <?php } ?>

                <?php if (isset($_GET['success'])) {?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo stripcslashes($_GET['success']); ?>
                    </div>
                <?php } ?>

                <form method="POST" action="app/signup.php" id="signupForm">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Enter your full name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                            <input type="text" class="form-control" name="username" id="username" placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Create a password" required>
                        </div>
                    </div>

                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="showPassword">
                            <label class="form-check-label" for="showPassword">Show password</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-4" id="submitBtn">Sign Up</button>
                    
                    <div class="text-center">
                        <span class="text-muted">Already have an account?</span>
                        <a href="login.php" class="login-link ms-2">Login</a>
                    </div>
                    <div class="text-center mt-3">
                        <a href="index.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Back to home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="success-popup" id="successPopup">
        <i class="fas fa-check-circle"></i>
        <span>Account created successfully! Redirecting to login...</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });

        // Show success popup if there's a success message
        <?php if (isset($_GET['success'])) { ?>
            document.getElementById('successPopup').style.display = 'flex';
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 3000);
        <?php } ?>
    </script>
</body>
</html>