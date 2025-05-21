<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit;
}

require_once 'db_connect.php';

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if (empty($term)) {
    echo json_encode([]);
    exit;
}

try {
    // Simplified query to get users like search_users.php
    $stmt = $conn->prepare("
        SELECT id, username, full_name 
        FROM users 
        WHERE (username LIKE :term OR full_name LIKE :term) 
        AND id != :current_user 
        LIMIT 5
    ");
    
    $searchTerm = "%" . $term . "%";
    $stmt->bindParam(':term', $searchTerm);
    $stmt->bindParam(':current_user', $_SESSION['id']);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} catch(PDOException $e) {
    echo json_encode([]);
}
