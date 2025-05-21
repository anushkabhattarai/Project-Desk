<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "task_management_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([]);
    exit;
}

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$user_id = $_SESSION['id'];

if (empty($term)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, username, full_name 
        FROM users 
        WHERE (username LIKE :term OR full_name LIKE :term) 
        AND id != :user_id 
        LIMIT 5
    ");
    
    $searchTerm = "%{$term}%";
    $stmt->bindParam(':term', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} catch(PDOException $e) {
    echo json_encode([]);
}
