<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
$logged_in = isset($_SESSION['id']);

// Return session status
echo json_encode([
    'logged_in' => $logged_in,
    'user_id' => $_SESSION['id'] ?? null,
    'session_id' => session_id()
]);
?> 