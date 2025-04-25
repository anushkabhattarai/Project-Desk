<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['assigned_to']) && $_SESSION['role'] == 'admin'&& isset($_POST['due_date'])) {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$title = validate_input($_POST['title']);
	$description = validate_input($_POST['description']);
	$assigned_to = validate_input($_POST['assigned_to']);
	$id = validate_input($_POST['id']);
	$due_date = validate_input($_POST['due_date']);

	if (empty($title)) {
		$em = "Title is required";
	    header("Location: ../edit-task.php?error=$em&id=$id");
	    exit();
	}else if (empty($description)) {
		$em = "Description is required";
	    header("Location: ../edit-task.php?error=$em&id=$id");
	    exit();
	}else if ($assigned_to == 0) {
		$em = "Select User";
	    header("Location: ../edit-task.php?error=$em&id=$id");
	    exit();
	}else {
    
       include "Model/Task.php";
       include "Model/Notification.php";
       include "Model/User.php";

       // Get existing task to check what changed
       $current_task = get_task_by_id($conn, $id);
       $previous_assigned_to = $current_task['assigned_to'];
       
       // Update the task
       $data = array($title, $description, $assigned_to, $due_date, $id);
       update_task($conn, $data);
       
       // If assigned to changed, notify both previous and new employee
       if ($previous_assigned_to != $assigned_to) {
           // If there was a previous assignee
           if ($previous_assigned_to != null) {
               $notif_data = array(
                   "Task '$title' has been reassigned to another employee",
                   $previous_assigned_to,
                   'Task Reassigned'
               );
               insert_notification($conn, $notif_data);
           }
           
           // Notify the new assignee
           $notif_data = array(
               "Task '$title' has been assigned to you. Please review and start working on it",
               $assigned_to,
               'Task Assigned'
           );
           insert_notification($conn, $notif_data);
       } 
       // If assigned to same person but other details changed
       else if ($previous_assigned_to == $assigned_to && 
               ($current_task['title'] != $title || 
                $current_task['description'] != $description || 
                $current_task['due_date'] != $due_date)) {
           
           $changes = array();
           if ($current_task['title'] != $title) $changes[] = "title";
           if ($current_task['description'] != $description) $changes[] = "description";
           if ($current_task['due_date'] != $due_date) $changes[] = "due date";
           
           $changes_text = implode(", ", $changes);
           
           $notif_data = array(
               "Task '$title' has been updated. Changes made to: $changes_text",
               $assigned_to,
               'Task Updated'
           );
           insert_notification($conn, $notif_data);
       }

       $em = "Task updated successfully";
	    header("Location: ../edit-task.php?success=$em&id=$id");
	    exit();

    
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../edit-task.php?error=$em");
   exit();
}

}else{ 
   $em = "First login";
   header("Location: ../login.php?error=$em");
   exit();
}