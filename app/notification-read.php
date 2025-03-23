<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Notification.php";

   if (isset($_GET['notification_id'])) {
       $notification_id = $_GET['notification_id'];
       notification_make_read($conn, $_SESSION['id'], $notification_id);
       
       // Redirect back to the previous page or notifications
       if(isset($_SERVER['HTTP_REFERER'])) {
           header("Location: ".$_SERVER['HTTP_REFERER']);
       } else {
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