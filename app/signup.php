<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

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

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: ../signup.php?error=" . urlencode("Username or email already exists"));
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'employee')");
    $stmt->bind_param("ssss", $full_name, $username, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: ../login.php?success=" . urlencode("Account created successfully. Please login."));
        exit();
    } else {
        header("Location: ../signup.php?error=" . urlencode("Error creating account. Please try again."));
        exit();
    }
} else {
    header("Location: ../signup.php");
    exit();
}
?> 