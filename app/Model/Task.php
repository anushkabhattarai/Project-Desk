<?php 

function insert_task($conn, $data) {
    $sql = "INSERT INTO tasks (title, description, due_date) VALUES(?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    return $conn->lastInsertId();
}

function get_all_tasks($conn) {
    $sql = "SELECT t.*, GROUP_CONCAT(u.full_name) as assigned_users 
            FROM tasks t 
            LEFT JOIN task_assignments ta ON t.id = ta.task_id
            LEFT JOIN users u ON ta.user_id = u.id
            GROUP BY t.id 
            ORDER BY t.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $tasks = $stmt->fetchAll();
    } else {
        $tasks = 0;
    }

    return $tasks;
}

function get_all_tasks_due_today($conn){
	$sql = "SELECT * FROM tasks WHERE due_date = CURDATE() AND status != 'completed' ORDER BY id DESC";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	if($stmt->rowCount() > 0){
		$tasks = $stmt->fetchAll();
	}else $tasks = 0;

	return $tasks;
}
function count_tasks_due_today($conn){
	$sql = "SELECT id FROM tasks WHERE due_date = CURDATE() AND status != 'completed'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}

function get_all_tasks_overdue($conn){
	$sql = "SELECT * FROM tasks WHERE due_date < CURDATE() AND status != 'completed' ORDER BY id DESC";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	if($stmt->rowCount() > 0){
		$tasks = $stmt->fetchAll();
	}else $tasks = 0;

	return $tasks;
}
function count_tasks_overdue($conn){
	$sql = "SELECT id FROM tasks WHERE due_date < CURDATE() AND status != 'completed'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}


function get_all_tasks_NoDeadline($conn){
	$sql = "SELECT * FROM tasks WHERE status != 'completed' AND due_date IS NULL OR due_date = '0000-00-00' ORDER BY id DESC";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	if($stmt->rowCount() > 0){
		$tasks = $stmt->fetchAll();
	}else $tasks = 0;

	return $tasks;
}
function count_tasks_NoDeadline($conn){
	$sql = "SELECT id FROM tasks WHERE status != 'completed' AND due_date IS NULL OR due_date = '0000-00-00'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}



function delete_task($conn, $data){
	$sql = "DELETE FROM tasks WHERE id=? ";
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}


function get_task_by_id($conn, $id){
	$sql = "SELECT * FROM tasks WHERE id =? ";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);

	if($stmt->rowCount() > 0){
		$task = $stmt->fetch();
	}else $task = 0;

	return $task;
}
function count_tasks($conn){
	$sql = "SELECT id FROM tasks";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}

function update_task($conn, $data) {
    $sql = "UPDATE tasks 
            SET title=?, description=?, due_date=?, status=? 
            WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['due_date'],
        $data['status'],
        $data['task_id']
    ]);
    
    // Update task assignments
    if(isset($data['assigned_to']) && is_array($data['assigned_to'])) {
        // First remove existing assignments
        $sql = "DELETE FROM task_assignments WHERE task_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data['task_id']]);
        
        // Add new assignments
        $sql = "INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        foreach($data['assigned_to'] as $user_id) {
            $stmt->execute([$data['task_id'], $user_id]);
        }
    }
    
    return true;
}

function update_task_status($conn, $data){
	$sql = "UPDATE tasks SET status=? WHERE id=?";
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}


function get_all_tasks_by_id($conn, $id) {
    $sql = "SELECT t.* FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ? 
            ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return !empty($tasks) ? $tasks : [];
}

function count_pending_tasks($conn){
	$sql = "SELECT id FROM tasks WHERE status = 'pending'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}

function count_in_progress_tasks($conn){
	$sql = "SELECT id FROM tasks WHERE status = 'in_progress'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}

function count_completed_tasks($conn){
	$sql = "SELECT id FROM tasks WHERE status = 'completed'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}


function count_my_tasks($conn, $id) {
    $sql = "SELECT COUNT(DISTINCT t.id) 
            FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

function count_my_tasks_overdue($conn, $id) {
    $sql = "SELECT COUNT(DISTINCT t.id) 
            FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ? 
            AND t.due_date < CURDATE() 
            AND t.status != 'completed' 
            AND t.due_date != '0000-00-00'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

function count_my_tasks_NoDeadline($conn, $id) {
    $sql = "SELECT COUNT(DISTINCT t.id) 
            FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ? 
            AND t.status != 'completed' 
            AND (t.due_date IS NULL OR t.due_date = '0000-00-00')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

function count_my_pending_tasks($conn, $id) {
    $sql = "SELECT COUNT(DISTINCT t.id) 
            FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ? 
            AND t.status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

function count_my_in_progress_tasks($conn, $id) {
    $sql = "SELECT COUNT(DISTINCT t.id) 
            FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ? 
            AND t.status = 'in_progress'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

function count_my_completed_tasks($conn, $id) {
    $sql = "SELECT COUNT(DISTINCT t.id) 
            FROM tasks t 
            INNER JOIN task_assignments ta ON t.id = ta.task_id 
            WHERE ta.user_id = ? 
            AND t.status = 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}

function get_task_assignees($conn, $task_id) {
    $sql = "SELECT u.* FROM users u 
            JOIN task_assignments ta ON u.id = ta.user_id 
            WHERE ta.task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$task_id]);
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return !empty($result) ? $result : 0;
}

function get_task_assignee_ids($conn, $task_id) {
    $sql = "SELECT user_id FROM task_assignments WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$task_id]);
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function assign_task_to_users($conn, $task_id, $user_id) {
    try {
        $sql = "INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$task_id, $user_id]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}