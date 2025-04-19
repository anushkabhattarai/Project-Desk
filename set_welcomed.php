<?php
session_start();

// Set session flag to indicate the user has seen the welcome page
$_SESSION['welcomed'] = true;

// Redirect to login page
header("Location: login.php");
exit();
?> 