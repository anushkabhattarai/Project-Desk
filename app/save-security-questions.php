<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['temp_user_id']) || !isset($_POST['answer1']) || !isset($_POST['answer2'])) {
    header("Location: ../signup.php");
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO security_questions (user_id, question, answer) VALUES (?, ?, ?), (?, ?, ?)");
    
    $stmt->execute([
        $_SESSION['temp_user_id'],
        'What is the name of your first school?',
        trim($_POST['answer1']),
        $_SESSION['temp_user_id'],
        'What is your favorite food?',
        trim($_POST['answer2'])
    ]);

    // Clear temporary session data
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['temp_username']);
    
    // Redirect to login with success message
    header("Location: ../login.php?success=Account created successfully! Please login.");
    exit;
    
} catch (PDOException $e) {
    header("Location: ../setup-security.php?error=Failed to save security questions");
    exit;
}
