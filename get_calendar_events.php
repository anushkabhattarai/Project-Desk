<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['id'];
$view = $_GET['view'] ?? 'task';
$events = [];

if ($view === 'task') {
    // Get all tasks for the user using PDO
    $taskQuery = "SELECT * FROM tasks WHERE assigned_to = :userId";
    $stmt = $conn->prepare($taskQuery);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Use due_date if available, otherwise use created_at
        $eventDate = $row['due_date'] ?: date('Y-m-d', strtotime($row['created_at']));
        
        $events[] = [
            'id' => 't_' . $row['id'],
            'title' => $row['title'],
            'start' => $eventDate,
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
