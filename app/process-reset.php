<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../forgot-password.php?error=Please enter new password and confirmation");
        exit();
    }

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        header("Location: ../forgot-password.php?error=Passwords do not match");
        exit();
    }

    // Verify username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: ../forgot-password.php?error=Invalid username");
        exit();
    }

    // Validate password requirements
    if (strlen($new_password) < 8 || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
        header("Location: ../forgot-password.php?error=Password must be at least 8 characters and contain one special character");
        exit();
    }

    try {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in database
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->execute([$hashed_password, $user['id']]);

        // Redirect to login with success message
        header("Location: ../login.php?success=Password reset successful. Please login with your new password.");
        exit();
        
    } catch (PDOException $e) {
        header("Location: ../forgot-password.php?error=An error occurred. Please try again.");
        exit();
    }
} else {
    header("Location: ../forgot-password.php");
    exit();
}
?>
