<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Notification.php";

   if (isset($_GET['notification_id'])) {
       $notification_id = $_GET['notification_id'];
       
       // Mark notification as read
       notification_make_read($conn, $_SESSION['id'], $notification_id);
       
       // Get notification details to determine where to redirect
       $sql = "SELECT * FROM notifications WHERE id = ? AND recipient = ?";
       $stmt = $conn->prepare($sql);
       $stmt->execute([$notification_id, $_SESSION['id']]);
       
       if ($stmt->rowCount() > 0) {
           $notification = $stmt->fetch();
           $type = strtolower($notification['type']);
           
           // Determine redirect based on notification type
           if (stripos($type, 'task') !== false) {
               header("Location: ../my_task.php");
           } elseif (stripos($type, 'note') !== false) {
               header("Location: ../notes.php");
           } elseif (stripos($type, 'support') !== false || stripos($type, 'ticket') !== false) {
               if ($_SESSION['role'] == 'admin') {
                   header("Location: ../admin-support.php");
               } else {
                   header("Location: ../contact-support.php");
               }
           } else {
               // Default redirect to referrer or notifications
               if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'notifications.php') === false) {
                   header("Location: ".$_SERVER['HTTP_REFERER']);
               } else {
                   header("Location: ../notifications.php");
               }
           }
       } else {
           // If notification not found, go to notifications page
           header("Location: ../notifications.php");
       }
       exit();
   } else {
       header("Location: ../index.php");
       exit();
   }
} else { 
    $em = "First login";
    header("Location: ../login.php?error=$em");
    exit();
}
?>