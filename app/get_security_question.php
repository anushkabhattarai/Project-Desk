<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';

try {
    $stmt = $conn->prepare("SELECT sq.question FROM users u 
                           INNER JOIN security_questions sq ON u.id = sq.user_id 
                           WHERE u.username = ? LIMIT 1");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'question' => $result['question']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
