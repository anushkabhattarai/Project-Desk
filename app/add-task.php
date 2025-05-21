<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['assigned_to']) && $_SESSION['role'] == 'admin' && isset($_POST['due_date'])) {
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
    $due_date = validate_input($_POST['due_date']);

    if (empty($title)) {
        $em = "Title is required";
        header("Location: ../create_task.php?error=$em");
        exit();
    } else if (empty($description)) {
        $em = "Description is required";
        header("Location: ../create_task.php?error=$em");
        exit();
    } else if (empty($assigned_to)) {
        $em = "Select at least one user";
        header("Location: ../create_task.php?error=$em");
        exit();
    } else {
        include "Model/Task.php";
        include "Model/Notification.php";

        // First create the task
        $task_data = array($title, $description, $due_date);
        $task_id = insert_task($conn, $task_data);

        // Then create assignments and notifications for each assigned user
        if ($task_id && is_array($assigned_to)) {
            foreach($assigned_to as $user_id) {
                if (assign_task_to_users($conn, $task_id, $user_id)) {
                    // Send notification to each assigned user
                    $notif_data = array(
                        "'$title' has been assigned to you. Please review and start working on it",
                        $user_id,
                        'New Task Assigned'
                    );
                    insert_notification($conn, $notif_data);
                }
            }
        }

        $sm = "Task created successfully";
        header("Location: ../create_task.php?success=$sm");
        exit();
    }
} else {
    $em = "Unknown error occurred";
    header("Location: ../create_task.php?error=$em");
    exit();
}

} else { 
    $em = "First login";
    header("Location: ../login.php?error=$em");
    exit();
}