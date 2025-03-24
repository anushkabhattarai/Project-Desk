<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['id']) && isset($_POST['user_name']) && isset($_POST['full_name']) && isset($_POST['role']) && $_SESSION['role'] == 'admin') {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$id = validate_input($_POST['id']);
	$user_name = validate_input($_POST['user_name']);
	$full_name = validate_input($_POST['full_name']);
	$role = validate_input($_POST['role']);
	$current_role = validate_input($_POST['current_role']);
	$password = !empty($_POST['password']) ? validate_input($_POST['password']) : null;

	if (empty($user_name)) {
		$em = "Username is required";
	    header("Location: ../edit-user.php?error=$em&id=$id");
	    exit();
	}else if (empty($full_name)) {
		$em = "Full name is required";
	    header("Location: ../edit-user.php?error=$em&id=$id");
	    exit();
	}else if (empty($role)) {
		$em = "Role is required";
	    header("Location: ../edit-user.php?error=$em&id=$id");
	    exit();
	}else {
    
       include "Model/User.php";
       
       // Handle profile picture upload
       $profile_pic = null;
       if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
           $allowed = ['jpg', 'jpeg', 'png'];
           $file_name = $_FILES['profile_pic']['name'];
           $file_size = $_FILES['profile_pic']['size'];
           $file_tmp = $_FILES['profile_pic']['tmp_name'];
           $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

           if (!in_array($file_ext, $allowed)) {
               $em = "Only JPG and PNG files are allowed";
               header("Location: ../edit-user.php?error=$em&id=$id");
               exit();
           }

           if ($file_size > 5 * 1024 * 1024) {
               $em = "File size must be less than 5MB";
               header("Location: ../edit-user.php?error=$em&id=$id");
               exit();
           }

           $profile_pic = uniqid() . '.' . $file_ext;
           move_uploaded_file($file_tmp, "../uploads/profile_pics/" . $profile_pic);
           
           // Delete old profile picture if exists
           $user = get_user_by_id($conn, $id);
           if ($user['profile_pic'] && file_exists("../uploads/profile_pics/" . $user['profile_pic'])) {
               unlink("../uploads/profile_pics/" . $user['profile_pic']);
           }
       }

       // If password is provided, validate it
       if ($password !== null) {
           $password_pattern = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/";
           if (!preg_match($password_pattern, $password)) {
               $em = "Password must contain at least 8 characters, including:
                      - One uppercase letter
                      - One lowercase letter
                      - One number
                      - One special character (!@#$%^&*)";
               header("Location: ../edit-user.php?error=$em&id=$id");
               exit();
           }
           $password = password_hash($password, PASSWORD_DEFAULT);
       } else {
           // Keep existing password
           $user = get_user_by_id($conn, $id);
           $password = $user['password'];
       }

       $data = array($full_name, $user_name, $password, $role, $profile_pic, $id, $current_role);
       update_user($conn, $data);

       $sm = "User updated successfully";
	    header("Location: ../edit-user.php?success=$sm&id=$id");
	    exit();

    
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../edit-user.php?error=$em");
   exit();
}

}else{ 
   $em = "First login";
   header("Location: ../edit-user.php?error=$em");
   exit();
}