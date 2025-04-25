<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    include "../DB_connection.php";
    include "Model/Support.php";
    include "Model/Notification.php";
    
    if (isset($_GET['id'])) {
        $ticket_id = $_GET['id'];
        
        // Check if ticket exists
        $ticket = get_ticket_by_id($conn, $ticket_id);
        
        if (!$ticket) {
            $em = "Ticket not found";
            header("Location: ../admin-support.php?error=$em");
            exit();
        }
        
        // Update ticket status to resolved
        try {
            update_ticket_status($conn, $ticket_id, 'resolved');
            
            // Send notification to the ticket creator
            $user_id = $ticket['user_id'];
            $subject = $ticket['subject'];
            $notif_data = array(
                "Your support ticket '$subject' has been marked as resolved",
                $user_id,
                'Ticket Resolved'
            );
            insert_notification($conn, $notif_data);
            
            $sm = "Ticket has been resolved successfully";
            header("Location: ../admin-view-ticket.php?id=$ticket_id&success=$sm");
            exit();
            
        } catch (Exception $e) {
            $em = "An error occurred: " . $e->getMessage();
            header("Location: ../admin-view-ticket.php?id=$ticket_id&error=$em");
            exit();
        }
    } else {
        $em = "Error: Missing ticket ID";
        header("Location: ../admin-support.php?error=$em");
        exit();
    }
} else {
    $em = "You don't have permission to access this page";
    header("Location: ../login.php?error=$em");
    exit();
} 