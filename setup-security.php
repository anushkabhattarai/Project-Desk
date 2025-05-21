<?php
session_start();
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: signup.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Security Questions | Project Desk</title>
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
        .gradient-container {
            background: linear-gradient(145deg, #ffffff 0%, #f5f5f5 100%);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 480px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gradient-container">
            <div class="text-center mb-4">
                <i class="fas fa-shield-alt text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-3">Setup Security Questions</h3>
                <p class="text-muted">Please set up your security questions for account recovery</p>
            </div>

            <form action="app/save-security.php" method="POST">
                <div class="mb-4">
                    <label class="form-label">Question 1: What is the name of your first school?</label>
                    <input type="text" class="form-control" name="answer1" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Question 2: What is your favorite food?</label>
                    <input type="text" class="form-control" name="answer2" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Complete Setup & Login</button>
            </form>
        </div>
    </div>
</body>
</html>
