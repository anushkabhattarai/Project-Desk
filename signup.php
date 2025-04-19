<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up | Project Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js">
</head>
<body class="login-body">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-md-6 pe-md-5 mb-5 mb-md-0">
                <div class="text-center text-md-start">
                    <h1 class="display-4 fw-bold mb-3">Project Desk</h1>
                    <p class="lead text-muted mb-4">Your digital workspace to write, manage, and grow.</p>
                    <div class="welcome-image mb-4">
                        <img src="assets/workspace.jpg" alt="Workspace" class="img-fluid rounded-4 shadow-sm">
                    </div>
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <div class="progress-dot" id="step1-dot"></div>
                        <div class="progress-dot" id="step2-dot"></div>
                        <div class="progress-dot" id="step3-dot"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Create Account</h2>
                            <p class="text-muted">Get started with Project Desk</p>
                        </div>

                        <?php if (isset($_GET['error'])) {?>
                            <div class="alert alert-danger animate__animated animate__fadeIn" role="alert">
                                <?php echo stripcslashes($_GET['error']); ?>
                            </div>
                        <?php } ?>

                        <form method="POST" action="app/signup.php" id="signupForm">
                            <div class="step" id="step1">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Full Name" required>
                                    <label for="full_name">Full Name</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                                    <label for="username">Username</label>
                                </div>
                                <button type="button" class="btn btn-primary w-100 py-2" onclick="nextStep(1)">Next</button>
                            </div>

                            <div class="step" id="step2" style="display: none;">
                                <div class="form-floating mb-4">
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                                    <label for="email">Email Address</label>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary w-100 py-2" onclick="prevStep(2)">Back</button>
                                    <button type="button" class="btn btn-primary w-100 py-2" onclick="nextStep(2)">Next</button>
                                </div>
                            </div>

                            <div class="step" id="step3" style="display: none;">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                    <label for="password">Password</label>
                                </div>
                                <div class="password-requirements mb-4">
                                    <p class="text-muted mb-2">Password must contain:</p>
                                    <div class="requirement" id="length-check">
                                        <small>✓ At least 10 characters</small>
                                    </div>
                                    <div class="requirement" id="uppercase-check">
                                        <small>✓ One uppercase letter</small>
                                    </div>
                                    <div class="requirement" id="special-check">
                                        <small>✓ One special character</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary w-100 py-2" onclick="prevStep(3)">Back</button>
                                    <button type="submit" class="btn btn-primary w-100 py-2">Create Account</button>
                                </div>
                            </div>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-decoration-none">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        let currentStep = 1;

        function updateProgressDots(step) {
            document.querySelectorAll('.progress-dot').forEach(dot => dot.classList.remove('active'));
            document.getElementById(`step${step}-dot`).classList.add('active');
        }

        function nextStep(step) {
            document.getElementById(`step${step}`).style.display = 'none';
            document.getElementById(`step${step + 1}`).style.display = 'block';
            currentStep = step + 1;
            updateProgressDots(currentStep);
        }

        function prevStep(step) {
            document.getElementById(`step${step}`).style.display = 'none';
            document.getElementById(`step${step - 1}`).style.display = 'block';
            currentStep = step - 1;
            updateProgressDots(currentStep);
        }

        // Password validation
        const password = document.getElementById('password');
        const lengthCheck = document.getElementById('length-check');
        const uppercaseCheck = document.getElementById('uppercase-check');
        const specialCheck = document.getElementById('special-check');

        password.addEventListener('input', function() {
            const value = this.value;
            
            // Length check
            if (value.length >= 10) {
                lengthCheck.classList.add('valid');
            } else {
                lengthCheck.classList.remove('valid');
            }
            
            // Uppercase check
            if (/[A-Z]/.test(value)) {
                uppercaseCheck.classList.add('valid');
            } else {
                uppercaseCheck.classList.remove('valid');
            }
            
            // Special character check
            if (/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
                specialCheck.classList.add('valid');
            } else {
                specialCheck.classList.remove('valid');
            }
        });

        // Initialize first step
        updateProgressDots(1);
    </script>

    <style>
        :root {
            --primary-color: #0B5ED7;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #0a51b8;
            border-color: #0a51b8;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(11, 94, 215, 0.25);
        }

        .card {
            border-radius: 1rem;
        }

        .progress-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #dee2e6;
            transition: all 0.3s ease;
        }

        .progress-dot.active {
            background-color: var(--primary-color);
            transform: scale(1.5);
        }

        .welcome-image img {
            max-height: 300px;
            object-fit: cover;
        }

        .step {
            animation: fadeIn 0.3s ease;
        }

        .requirement {
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .requirement.valid {
            color: #198754;
        }

        .requirement.valid small::before {
            content: '✓';
            margin-right: 0.5rem;
            color: #198754;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .card {
                border-radius: 0.5rem;
            }
        }
    </style>
</body>
</html> 