<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['id'];
$view = $_GET['view'] ?? 'task';
$events = [];

if ($view === 'task') {
    // Get all tasks assigned to the user using PDO
    $taskQuery = "SELECT t.*, ta.user_id as assigned_to 
                  FROM tasks t 
                  INNER JOIN task_assignments ta ON t.id = ta.task_id 
                  WHERE ta.user_id = :userId";
    $stmt = $conn->prepare($taskQuery);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $eventTitle = $row['title'];
        if ($row['due_date']) {
            $eventTitle .= ' - Due: ' . date('Y-m-d', strtotime($row['due_date']));
        }
        
        $events[] = [
            'id' => 't_' . $row['id'],
            'title' => $eventTitle,
            'start' => date('Y-m-d', strtotime($row['created_at'])), // Always use created_at as event date
            'type' => 'task',
            'description' => $row['description'],
            'status' => $row['status'],
            'user_role' => $_SESSION['role'],
            'className' => 'task-event',
            'allDay' => true
        ];
    }
} else {
    // Get all notes for the user using PDO
    $noteQuery = "SELECT n.id, n.title, n.content, n.created_at 
                  FROM notes n 
                  LEFT JOIN note_shares ns ON n.id = ns.note_id 
                  WHERE n.user_id = :userId OR ns.shared_with = :userId";
                  
    $stmt = $conn->prepare($noteQuery);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => 'n_' . $row['id'],
            'title' => $row['title'],
            'start' => date('Y-m-d', strtotime($row['created_at'])),
            'type' => 'note',
            'description' => substr(strip_tags($row['content']), 0, 100) . '...',
            'user_role' => $_SESSION['role'],
            'className' => 'note-event',
            'allDay' => true
        ];
    }
}

echo json_encode($events);
