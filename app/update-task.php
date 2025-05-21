<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['assigned_to']) && $_SESSION['role'] == 'admin') {
    include "../DB_connection.php";

    function validate_input($data) {
        if(is_array($data)) {
            return array_map(function($item) {
                return htmlspecialchars(trim(stripslashes($item)));
            }, $data);
        }
        return htmlspecialchars(trim(stripslashes($data)));
    }

    $title = validate_input($_POST['title']);
    $description = validate_input($_POST['description']);
    $assigned_to = $_POST['assigned_to']; // Array of user IDs
    $id = validate_input($_POST['id']);
    $due_date = validate_input($_POST['due_date']);
    $status = isset($_POST['status']) ? validate_input($_POST['status']) : 'pending';

    if (empty($title)) {
        $em = "Title is required";
        header("Location: ../edit-task.php?error=$em&id=$id");
        exit();
    } else if (empty($description)) {
        $em = "Description is required";
        header("Location: ../edit-task.php?error=$em&id=$id");
        exit();
    } else if (empty($assigned_to)) {
        $em = "Select at least one user";
        header("Location: ../edit-task.php?error=$em&id=$id");
        exit();
    } else {
        include "Model/Task.php";
        include "Model/Notification.php";

        // Update task data
        $task_data = [
            'title' => $title,
            'description' => $description,
            'due_date' => $due_date,
            'status' => $status,
            'task_id' => $id,
            'assigned_to' => $assigned_to
        ];

        if(update_task($conn, $task_data)) {
            // Send notifications to newly assigned users
            foreach($assigned_to as $user_id) {
                $notif_data = array(
                    "'$title' has been assigned to you. Please review and start working on it",
                    $user_id,
                    'Task Updated'
                );
                insert_notification($conn, $notif_data);
            }

            $sm = "Task updated successfully";
            header("Location: ../edit-task.php?success=$sm&id=$id");
            exit();
        } else {
            $em = "Error updating task";
            header("Location: ../edit-task.php?error=$em&id=$id");
            exit();
        }
    }
} else {
    $em = "Unknown error occurred";
    header("Location: ../edit-task.php?error=$em");
    exit();
}

} else { 
    $em = "First login";
    header("Location: ../login.php?error=$em");
    exit();
}