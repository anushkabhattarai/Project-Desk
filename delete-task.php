<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/Notification.php";
    
    if (!isset($_GET['id'])) {
    	 header("Location: tasks.php");
    	 exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);

    if ($task == 0) {
    	 header("Location: tasks.php");
    	 exit();
    }

     // Send notification to the employee if task was assigned
     if ($task['assigned_to']) {
         $task_title = $task['title'];
         $admin_id = $_SESSION['id'];
         
         $notif_data = array(
             "Task '$task_title' has been deleted",
             $task['assigned_to'],
             'Task Deleted'
         );
         insert_notification($conn, $notif_data);
     }

     $data = array($id);
     delete_task($conn, $data);
     $sm = "Deleted Successfully";
     header("Location: tasks.php?success=$sm");
     exit();

 }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>