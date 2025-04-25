<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Support.php";
    include "Model/Notification.php";
    include "Model/User.php";
    
    if (isset($_POST['subject']) && isset($_POST['message'])) {
        // Validate and sanitize inputs
        function validate_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
        
        $subject = validate_input($_POST['subject']);
        $message = validate_input($_POST['message']);
        $user_id = $_SESSION['id'];
        
        // Validate input
        if (empty($subject)) {
            $em = "Subject is required";
            header("Location: ../contact-support.php?error=$em");
            exit();
        } else if (empty($message)) {
            $em = "Message is required";
            header("Location: ../contact-support.php?error=$em");
            exit();
        } else {
            // Create the ticket
            $ticket_data = [
                'user_id' => $user_id,
                'subject' => $subject
            ];
            
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Insert ticket
                $ticket_id = create_support_ticket($conn, $ticket_data);
                
                // Insert the first message
                $reply_data = [
                    'ticket_id' => $ticket_id,
                    'user_id' => $user_id,
                    'message' => $message,
                    'role' => 'user'
                ];
                
                add_ticket_reply($conn, $reply_data);
                
                // Send notification to all admins
                $admins = get_all_admins($conn);
                if ($admins != 0) {
                    $user = get_user_by_id($conn, $user_id);
                    $username = $user['username'];
                    
                    foreach ($admins as $admin) {
                        $notif_data = array(
                            "New support ticket (#$ticket_id): '$subject' submitted by $username",
                            $admin['id'],
                            'Support Ticket'
                        );
                        insert_notification($conn, $notif_data);
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $sm = "Your support ticket has been submitted successfully";
                header("Location: ../contact-support.php?success=$sm");
                exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                
                $em = "An error occurred: " . $e->getMessage();
                header("Location: ../contact-support.php?error=$em");
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