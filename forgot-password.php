<!DOCTYPE html>
<html>
<head>
    <title>Reset Password | Project Desk</title>
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
                    <h3>Reset Password</h3>
                    <p class="subtitle" id="stepText">Step 1: Enter your username</p>
                </div>

                <?php if (isset($_GET['error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php } ?>

                <!-- Step 1: Username Form -->
                <form id="usernameForm" class="step-form">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                    <button type="submit" class="btn btn-primary w-100 mt-3">Continue</button>
                </form>

                <!-- Step 2: Security Question Forms (Hidden initially) -->
                <div id="securityQuestionForm" class="d-none">
                    <div id="question1Container" class="security-question mb-3">
                        <label class="form-label fw-bold" id="question1Text"></label>
                        <input type="text" class="form-control" id="answer1" placeholder="Your answer" required>
                        <button type="button" id="verifyAnswer1" class="btn btn-primary w-100 mt-3">Continue</button>
                    </div>
                    <div id="question2Container" class="security-question mb-3 d-none">
                        <label class="form-label fw-bold" id="question2Text"></label>
                        <input type="text" class="form-control" id="answer2" placeholder="Your answer" required>
                        <button type="button" id="verifyAnswer2" class="btn btn-primary w-100 mt-3">Verify Answers</button>
                    </div>
                </div>

                <!-- Step 3: Reset Password Form (Hidden initially) -->
                <form id="resetForm" action="app/process-reset.php" method="POST" class="d-none">
                    <input type="hidden" id="username_final" name="username">
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               placeholder="New Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" placeholder="Confirm Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-requirements mb-3">
                        <div class="requirement" id="length">
                            <i class="fas fa-circle-small"></i>Must be at least 8 characters
                        </div>
                        <div class="requirement" id="special">
                            <i class="fas fa-circle-small"></i>Must contain one special character
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Back to login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('usernameForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = this.querySelector('input[name="username"]').value;
            
            try {
                const response = await fetch('app/get_security_questions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show first security question
                    document.getElementById('question1Text').textContent = data.questions[0].question;
                    document.getElementById('question2Text').textContent = data.questions[1].question;
                    this.classList.add('d-none');
                    document.getElementById('securityQuestionForm').classList.remove('d-none');
                    // Show the actual question as the step text
                    document.getElementById('stepText').textContent = data.questions[0].question;
                    sessionStorage.setItem('reset_username', username);
                } else {
                    alert(data.message || 'Username not found');
                }
            } catch (error) {
                alert('Error occurred. Please try again.');
            }
        });

        document.getElementById('verifyAnswer1').addEventListener('click', async function() {
            const answer = document.getElementById('answer1').value;
            const username = sessionStorage.getItem('reset_username');
            
            try {
                const response = await fetch('app/verify_security_answer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        username, 
                        answer,
                        question_number: 1 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('question1Container').classList.add('d-none');
                    document.getElementById('question2Container').classList.remove('d-none');
                    // Show the second question as the step text
                    document.getElementById('stepText').textContent = document.getElementById('question2Text').textContent;
                } else {
                    alert('Incorrect answer. Please try again.');
                }
            } catch (error) {
                alert('Error occurred. Please try again.');
            }
        });

        document.getElementById('verifyAnswer2').addEventListener('click', async function() {
            const answer = document.getElementById('answer2').value;
            const username = sessionStorage.getItem('reset_username');
            
            try {
                const response = await fetch('app/verify_security_answer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        username, 
                        answer,
                        question_number: 2 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('securityQuestionForm').classList.add('d-none');
                    document.getElementById('resetForm').classList.remove('d-none');
                    document.getElementById('stepText').textContent = 'Step 3: Set New Password';
                    document.querySelector('#resetForm input[name="username"]').value = username;
                } else {
                    alert('Incorrect answer. Please try again.');
                }
            } catch (error) {
                alert('Error occurred. Please try again.');
            }
        });

        function validatePassword() {
            const pwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;

            // Check requirements
            const requirements = {
                length: pwd.length >= 8,
                special: /[!@#$%^&*(),.?":{}|<>]/.test(pwd),
                match: pwd === confirmPwd && pwd !== ''
            };

            // Update requirement indicators
            for (const [key, valid] of Object.entries(requirements)) {
                const elem = document.getElementById(key);
                if (elem) {
                    elem.className = `requirement ${valid ? 'valid' : 'invalid'}`;
                    elem.querySelector('i').className = `fas ${valid ? 'fa-check' : 'fa-circle-small'}`;
                }
            }
        }

        // Add input event listeners
        ['new_password', 'confirm_password'].forEach(id => {
            document.getElementById(id).addEventListener('input', validatePassword);
        });

        // Form submit handler
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const pwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;

            if (pwd.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return false;
            }

            if (!/[!@#$%^&*(),.?":{}|<>]/.test(pwd)) {
                e.preventDefault();
                alert('Password must contain at least one special character');
                return false;
            }

            if (pwd !== confirmPwd) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }

            return true;
        });

        // Add password visibility toggle
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            
            button.addEventListener('click', () => {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // Toggle eye icon
                const icon = button.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Initialize password toggles
        togglePasswordVisibility('new_password', 'togglePassword');
        togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');
    </script>
</body>
</html>
