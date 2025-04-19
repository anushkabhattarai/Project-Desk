<?php
session_start();

// Check if the user has already seen the welcome page
if (!isset($_SESSION['welcomed']) && !isset($_SESSION['user_id'])) {
    // First time visitor, redirect to welcome page
    header("Location: welcome.php");
    exit();
} else if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // User has seen welcome page but not logged in
    header("Location: login.php");
    exit();
}
?>