<?php
require_once "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if user exists and is not admin
    $sql = "SELECT * FROM users WHERE username = ? AND full_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $fullname]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: ../forgot-password.php?error=Invalid username or full name");
        exit();
    }

    if ($user['role'] === 'admin') {
        header("Location: ../forgot-password.php?error=Admin accounts cannot use password reset");
        exit();
    }

    // Validate password requirements
    if (strlen($new_password) < 10 || 
        !preg_match('/[A-Z]/', $new_password) || 
        !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
        header("Location: ../forgot-password.php?error=Password does not meet requirements");
        exit();
    }

    // Check password history
    $history_sql = "SELECT password_hash FROM password_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->execute([$user['id']]);
    $old_passwords = $history_stmt->fetchAll(PDO::FETCH_COLUMN);

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    foreach ($old_passwords as $old_hash) {
        if (password_verify($new_password, $old_hash)) {
            header("Location: ../forgot-password.php?error=Cannot reuse recent passwords");
            exit();
        }
    }

    try {
        $conn->beginTransaction();

        // Update password
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([$new_hash, $user['id']]);

        // Store in password history
        $history_sql = "INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)";
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->execute([$user['id'], $new_hash]);

        $conn->commit();
        header("Location: ../login.php?success=Password reset successful");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: ../forgot-password.php?error=An error occurred. Please try again.");
        exit();
    }
}
