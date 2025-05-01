<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['note_id']) || !isset($data['comment'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$note_id = (int)$data['note_id'];
$comment = trim($data['comment']);
$user_id = $_SESSION['id'];

if ($note_id <= 0 || empty($comment)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid note ID or empty comment']);
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "task_management_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Verify user has access to the note
$check_stmt = $conn->prepare("
    SELECT n.id 
    FROM notes n 
    LEFT JOIN note_shares ns ON n.id = ns.note_id AND ns.shared_with = ?
    WHERE n.id = ? AND (n.user_id = ? OR ns.id IS NOT NULL)
");

$check_stmt->bind_param("iii", $user_id, $note_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Insert the comment
$stmt = $conn->prepare("INSERT INTO note_comments (note_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $note_id, $user_id, $comment);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add comment']);
    exit;
}

// Get the newly inserted comment details
$new_comment_id = $stmt->insert_id;
$stmt = $conn->prepare("
    SELECT nc.*, u.full_name as author_name, u.profile_pic
    FROM note_comments nc 
    JOIN users u ON nc.user_id = u.id 
    WHERE nc.id = ?
");
$stmt->bind_param("i", $new_comment_id);
$stmt->execute();
$result = $stmt->get_result();
$comment_data = $result->fetch_assoc();

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'comment' => [
        'id' => $comment_data['id'],
        'comment' => htmlspecialchars($comment_data['comment']),
        'author_name' => htmlspecialchars($comment_data['author_name']),
        'profile_pic' => $comment_data['profile_pic'],
        'formatted_date' => date('M j, Y g:i A', strtotime($comment_data['created_at']))
    ]
]); 