<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$answer = $data['answer'] ?? '';
$question_number = $data['question_number'] ?? 1;

try {
    // First find the user ID and get their security questions
    $userStmt = $conn->prepare("SELECT u.id, sq.answer, sq.question FROM users u 
                               INNER JOIN security_questions sq ON u.id = sq.user_id 
                               WHERE u.username = ? ORDER BY sq.id ASC");
    $userStmt->execute([$username]);
    $questions = $userStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$questions) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    // Get the specific question being answered (1st or 2nd)
    $currentQuestion = $questions[$question_number - 1];
    
    // Compare answers (case-insensitive)
    $userAnswer = strtolower(trim($answer));
    $correctAnswer = strtolower(trim($currentQuestion['answer']));

    if ($userAnswer === $correctAnswer) {
        echo json_encode([
            'success' => true,
            'message' => 'Answer correct',
            'next_question' => $question_number < 2 ? $questions[1]['question'] : null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect answer',
            'debug' => [
                'provided' => $userAnswer,
                'expected' => $correctAnswer,
                'question' => $currentQuestion['question']
            ]
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
