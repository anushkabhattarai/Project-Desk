<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $security_answer1 = trim($_POST['security_answer1']);
    $security_answer2 = trim($_POST['security_answer2']);

    // Validate input
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        header("Location: ../signup.php?error=" . urlencode("All fields are required"));
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../signup.php?error=" . urlencode("Invalid email format"));
        exit();
    }

    try {
        $conn->beginTransaction();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: ../signup.php?error=" . urlencode("Username or email already exists"));
            exit();
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'employee')");
        $stmt->execute([$full_name, $username, $email, $hashed_password]);
        
        // Get the new user's ID
        $userId = $conn->lastInsertId();
        
        // Store in session for security questions setup
        $_SESSION['temp_user_id'] = $userId;
        $_SESSION['temp_username'] = $username;
        
        $conn->commit();
        
        // Redirect to security questions setup
        header("Location: ../setup-security.php");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        header("Location: ../signup.php?error=" . urlencode("Database error occurred"));
        exit();
    }
} else {
    header("Location: ../signup.php");
    exit();
}
?>