<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Support.php";
    include "Model/Notification.php";
    include "Model/User.php";
    
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
        $user_id = $_SESSION['id'];
        
        // Validate input
        if (empty($message)) {
            $em = "Message is required";
            header("Location: ../view-ticket.php?id=$ticket_id&error=$em");
            exit();
        } else {
            // Check if ticket exists and belongs to this user
            $ticket = get_ticket_by_id($conn, $ticket_id);
            
            if (!$ticket) {
                $em = "Ticket not found";
                header("Location: ../contact-support.php?error=$em");
                exit();
            }
            
            // Check if the ticket belongs to this user or is an admin
            if ($ticket['user_id'] != $user_id && $_SESSION['role'] != 'admin') {
                $em = "You don't have permission to reply to this ticket";
                header("Location: ../contact-support.php?error=$em");
                exit();
            }
            
            // Add reply
            $reply_data = [
                'ticket_id' => $ticket_id,
                'user_id' => $user_id,
                'message' => $message,
                'role' => 'user'
            ];
            
            try {
                add_ticket_reply($conn, $reply_data);
                
                // If ticket was resolved, reopen it
                if ($ticket['status'] == 'resolved') {
                    update_ticket_status($conn, $ticket_id, 'open');
                }
                
                // Send notification to all admins about the new reply
                $admins = get_all_admins($conn);
                if ($admins != 0) {
                    $user = get_user_by_id($conn, $user_id);
                    $username = $user['username'];
                    $subject = $ticket['subject'];
                    
                    foreach ($admins as $admin) {
                        $notif_data = array(
                            "New reply on ticket (#$ticket_id): '$subject' from $username",
                            $admin['id'],
                            'Support Reply'
                        );
                        insert_notification($conn, $notif_data);
                    }
                }
                
                $sm = "Your reply has been added successfully";
                header("Location: ../view-ticket.php?id=$ticket_id&success=$sm");
                exit();
                
            } catch (Exception $e) {
                $em = "An error occurred: " . $e->getMessage();
                header("Location: ../view-ticket.php?id=$ticket_id&error=$em");
                exit();
            }
        }
    } else {
        $em = "Error: Missing required fields";
        header("Location: ../contact-support.php?error=$em");
        exit();
    }
} else {
    $em = "You must login first";
    header("Location: ../login.php?error=$em");
    exit();
} 