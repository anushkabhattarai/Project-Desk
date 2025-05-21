<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';

try {
    $stmt = $conn->prepare("SELECT sq.id, sq.question 
                           FROM users u 
                           INNER JOIN security_questions sq ON u.id = sq.user_id 
                           WHERE u.username = ?
                           ORDER BY sq.id ASC
                           LIMIT 2");
    $stmt->execute([$username]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($questions) === 2) {
        echo json_encode([
            'success' => true,
            'questions' => $questions
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username not found or security questions not set'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
