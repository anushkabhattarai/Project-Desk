<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$answer = $_POST['answer'] ?? '';

try {
    $stmt = $conn->prepare("SELECT sq.answer FROM users u 
                           INNER JOIN security_questions sq ON u.id = sq.user_id 
                           WHERE u.username = ? LIMIT 1");
    $stmt->execute([$username]);
    $result = $stmt->fetch();

    if ($result && strtolower($result['answer']) === strtolower($answer)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect answer'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
