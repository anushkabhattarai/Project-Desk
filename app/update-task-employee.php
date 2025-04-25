<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['id']) && isset($_POST['status']) && $_SESSION['role'] == 'employee') {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$status = validate_input($_POST['status']);
	$id = validate_input($_POST['id']);

	if (empty($status)) {
		$em = "status is required";
	    header("Location: ../edit-task-employee.php?error=$em&id=$id");
	    exit();
	}else {
    
       include "Model/Task.php";
       include "Model/User.php";
       include "Model/Notification.php";

       // Get task details for notification
       $task = get_task_by_id($conn, $id);
       $employee = get_user_by_id($conn, $_SESSION['id']);
       $employee_name = $employee['username'];
       $task_title = $task['title'];
       
       // Get previous status to check if it changed
       $previous_status = $task['status'];
       
       if ($previous_status != $status) {
           // Update the task status
           $data = array($status, $id);
           update_task_status($conn, $data);
           
           // Get all admins to send notifications
           $admins = get_all_admins($conn);
           
           if ($admins != 0) {
               $status_label = str_replace('_', ' ', $status);
               $status_label = ucwords($status_label);
               
               foreach ($admins as $admin) {
                   $notif_data = array(
                       "'$task_title' has been updated to '$status_label' by $employee_name",
                       $admin['id'],
                       'Task Status Update'
                   );
                   insert_notification($conn, $notif_data);
               }
           }
       } else {
           // Status not changed, just update
           $data = array($status, $id);
           update_task_status($conn, $data);
       }

       $em = "Task updated successfully";
	    header("Location: ../edit-task-employee.php?success=$em&id=$id");
	    exit();

    
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../edit-task-employee.php?error=$em");
   exit();
}

}else{ 
   $em = "First login";
   header("Location: ../login.php?error=$em");
   exit();
}