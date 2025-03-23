<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Notification.php";

    // Mark all notifications as read for the current user
    mark_all_notifications_read($conn, $_SESSION['id']);
    
    // Redirect back to notifications page
    header("Location: ../notifications.php?success=All%20notifications%20marked%20as%20read");
    exit();
} else { 
    $em = "First login";
    header("Location: ../login.php?error=$em");
    exit();
}
?> 