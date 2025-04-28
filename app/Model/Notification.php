<?php  

function get_all_my_notifications($conn, $id){
	$sql = "SELECT * FROM notifications WHERE recipient=?";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);

	if($stmt->rowCount() > 0){
		$notifications = $stmt->fetchAll();
	}else $notifications = 0;

	return $notifications;
}


function count_notification($conn, $id){
	$sql = "SELECT id FROM notifications WHERE recipient=? AND is_read=0";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);

	return $stmt->rowCount();
}

function insert_notification($conn, $data){
	$sql = "INSERT INTO notifications (message, recipient, type) VALUES(?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}

function notification_make_read($conn, $recipient_id, $notification_id){
	$sql = "UPDATE notifications SET is_read=1 WHERE id=? AND recipient=?";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$notification_id, $recipient_id]);
}

function mark_all_notifications_read($conn, $user_id) {
	$sql = "UPDATE notifications SET is_read = 1 WHERE recipient = ?";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$user_id]);
}

function get_notification_date_formatted($date_str) {
    if (empty($date_str) || $date_str == '0000-00-00') {
        return date('Y-m-d'); // Return today's date as fallback
    }
    
    // Try to parse the date
    $timestamp = strtotime($date_str);
    if ($timestamp === false) {
        return date('Y-m-d');
    }
    
    return date('Y-m-d', $timestamp);
}

function find_task_by_title($conn, $title, $user_id) {
    $sql = "SELECT id FROM tasks WHERE title = ? AND assigned_to = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$title, $user_id]);
    
    if($stmt->rowCount() > 0) {
        $task = $stmt->fetch();
        return $task['id'];
    }
    
    return null;
}