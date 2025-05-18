<!DOCTYPE html>
<html>
<head>
    <title>Set New Password | Project Desk</title>
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
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 480px;
            margin: auto;
        }
        .forgot-form {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            padding: 0.5rem;
        }
        .lock-icon {
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
        .password-requirements {
            margin: 1rem 0;
        }
        .requirement {
            color: #5f6368;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
        .back-link {
            color: #1a73e8;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            color: #1557b0;
        }
        .form-label {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gradient-container">
            <div class="forgot-form">
                <div class="text-center">
                    <i class="fas fa-lock lock-icon"></i>
                    <h3>Set a new password</h3>
                    <p class="subtitle">Your new password must be different from previously used passwords</p>
                </div>

                <?php if (isset($_GET['error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php } ?>

                <form action="app/process-reset.php" method="POST" id="resetForm">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" required>
                    
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Password" required>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>

                    <div class="password-requirements">
                        <div class="requirement" id="length"><i class="fas fa-circle-small"></i>Must be at least 8 characters</div>
                        <div class="requirement" id="special"><i class="fas fa-circle-small"></i>Must contain one special character</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Reset password</button>
                    <div class="text-center mt-3">
                        <a href="login.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Back to login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function validatePassword() {
            const pwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');

            const requirements = {
                length: pwd.length >= 8,
                special: /[!@#$%^&*(),.?":{}|<>]/.test(pwd),
                match: pwd === confirmPwd && pwd !== ''
            };

            for (const [key, valid] of Object.entries(requirements)) {
                const elem = document.getElementById(key);
                elem.className = `requirement ${valid ? 'valid' : 'invalid'}`;
                elem.querySelector('i').className = `fas ${valid ? 'fa-check' : 'fa-circle-small'}`;
            }

            submitBtn.disabled = !Object.values(requirements).every(Boolean);
        }

        ['new_password', 'confirm_password'].forEach(id => {
            document.getElementById(id).addEventListener('input', validatePassword);
        });
    </script>
</body>
</html>
