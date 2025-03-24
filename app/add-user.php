<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['full_name']) && isset($_POST['role']) && $_SESSION['role'] == 'admin') {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$user_name = validate_input($_POST['user_name']);
	$password = validate_input($_POST['password']);
	$full_name = validate_input($_POST['full_name']);
	$role = validate_input($_POST['role']);

	// Password validation
	$password_pattern = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/";
	
	if (empty($user_name)) {
		$em = "User name is required";
	    header("Location: ../add-user.php?error=$em");
	    exit();
	}else if (empty($password)) {
		$em = "Password is required";
	    header("Location: ../add-user.php?error=$em");
	    exit();
	}else if (!preg_match($password_pattern, $password)) {
		$em = "Password must contain at least 8 characters, including uppercase, lowercase, numbers and special characters";
		header("Location: ../add-user.php?error=$em");
		exit();
	}else if (empty($full_name)) {
		$em = "Full name is required";
	    header("Location: ../add-user.php?error=$em");
	    exit();
	}else if (empty($role)) {
		$em = "Role is required";
		header("Location: ../add-user.php?error=$em");
		exit();
	}else {
    
       // Handle file upload
       $profile_pic = null;
       if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
           $allowed = ['jpg', 'jpeg', 'png'];
           $file_name = $_FILES['profile_pic']['name'];
           $file_size = $_FILES['profile_pic']['size'];
           $file_tmp = $_FILES['profile_pic']['tmp_name'];
           $file_type = $_FILES['profile_pic']['type'];
           $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

           // Validate file extension
           if (!in_array($file_ext, $allowed)) {
               $em = "Only JPG and PNG files are allowed";
               header("Location: ../add-user.php?error=$em");
               exit();
           }

           // Validate file size (5MB max)
           if ($file_size > 5 * 1024 * 1024) {
               $em = "File size must be less than 5MB";
               header("Location: ../add-user.php?error=$em");
               exit();
           }

           // Generate unique filename
           $profile_pic = uniqid() . '.' . $file_ext;
           move_uploaded_file($file_tmp, "../uploads/profile_pics/" . $profile_pic);
       }

       include "Model/User.php";
       $password = password_hash($password, PASSWORD_DEFAULT);

       $data = array($full_name, $user_name, $password, $role);
       insert_user($conn, $data);

       $em = "User created successfully";
	    header("Location: ../add-user.php?success=$em");
	    exit();

    
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../add-user.php?error=$em");
   exit();
}

}else{ 
   $em = "First login";
   header("Location: ../login.php?error=$em");
   exit();
}