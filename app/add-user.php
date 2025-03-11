<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['full_name']) && $_SESSION['role'] == 'admin') {
	include "../DB_connection.php";
	include "Model/User.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	function validate_password($password) {
		// Minimum length check
		if (strlen($password) < 8 || strlen($password) > 12) {
			return "Password must be between 8 and 12 characters long";
		}
		
		// Check for uppercase
		if (!preg_match('/[A-Z]/', $password)) {
			return "Password must contain at least one uppercase letter";
		}
		
		// Check for lowercase
		if (!preg_match('/[a-z]/', $password)) {
			return "Password must contain at least one lowercase letter";
		}
		
		// Check for numbers
		if (!preg_match('/[0-9]/', $password)) {
			return "Password must contain at least one number";
		}
		
		// Check for special characters
		if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
			return "Password must contain at least one special character";
		}
		
		return true;
	}

	$user_name = validate_input($_POST['user_name']);
	$password = validate_input($_POST['password']);
	$full_name = validate_input($_POST['full_name']);

	if (empty($user_name)) {
		$em = "User name is required";
	    header("Location: ../add-user.php?error=$em");
	    exit();
	}else if (empty($password)) {
		$em = "Password is required";
	    header("Location: ../add-user.php?error=$em");
	    exit();
	}else if (empty($full_name)) {
		$em = "Full name is required";
	    header("Location: ../add-user.php?error=$em");
	    exit();
	}else {
    
       $password_validation = validate_password($password);
       if ($password_validation !== true) {
           $em = $password_validation;
           header("Location: ../add-user.php?error=$em");
           exit();
       }

       $password = password_hash($password, PASSWORD_DEFAULT);

       $data = array($full_name, $user_name, $password, "employee");
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
   header("Location: ../add-user.php?error=$em");
   exit();
}