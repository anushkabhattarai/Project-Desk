<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    include "../DB_connection.php";
    include "Model/Support.php";
    include "Model/Notification.php";
    
    if (isset($_POST['ticket_id']) && isset($_POST['message'])) {
        // Validate and sanitize inputs
        function validate_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
        
        $ticket_id = validate_input($_POST['ticket_id']);
        $message = validate_input($_POST['message']);
        $admin_id = $_SESSION['id'];
        
        // Validate input
        if (empty($message)) {
            $em = "Message is required";
            header("Location: ../admin-view-ticket.php?id=$ticket_id&error=$em");
            exit();
        } else {
            // Check if ticket exists
            $ticket = get_ticket_by_id($conn, $ticket_id);
            
            if (!$ticket) {
                $em = "Ticket not found";
                header("Location: ../admin-support.php?error=$em");
                exit();
            }
            
            // Add reply as admin
            $reply_data = [
                'ticket_id' => $ticket_id,
                'user_id' => $admin_id,
                'message' => $message,
                'role' => 'admin'
            ];
            
            try {
                add_ticket_reply($conn, $reply_data);
                
                // Send notification to the ticket creator
                $user_id = $ticket['user_id'];
                $notif_data = array(
                    "Admin has replied to your support ticket: '".substr($ticket['subject'], 0, 30).(strlen($ticket['subject']) > 30 ? '...' : '')."'",
                    $user_id,
                    'Support Ticket Reply'
                );
                insert_notification($conn, $notif_data);
                
                $sm = "Your reply has been added successfully";
                header("Location: ../admin-view-ticket.php?id=$ticket_id&success=$sm");
                exit();
                
            } catch (Exception $e) {
                $em = "An error occurred: " . $e->getMessage();
                header("Location: ../admin-view-ticket.php?id=$ticket_id&error=$em");
                exit();
            }
        }
    } else {
        $em = "Error: Missing required fields";
        header("Location: ../admin-support.php?error=$em");
        exit();
    }
} else {
    $em = "You don't have permission to access this page";
    header("Location: ../login.php?error=$em");
    exit();
} 