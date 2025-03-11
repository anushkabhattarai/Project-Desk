<?php 
session_start();
if (isset($_POST['user_name']) && isset($_POST['password'])) {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	// Add new password validation function
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

	if (empty($user_name)) {
		$em = "User name is required";
	    header("Location: ../login.php?error=$em");
	    exit();
	}else if (empty($password)) {
		$em = "Password name is required";
	    header("Location: ../login.php?error=$em");
	    exit();
	}else {
    
       $sql = "SELECT * FROM users WHERE username = ?";
       $stmt = $conn->prepare($sql);
       $stmt->execute([$user_name]);

       if ($stmt->rowCount() == 1) {
       	   $user = $stmt->fetch();
       	   $usernameDb = $user['username'];
       	   $passwordDb = $user['password'];
       	   $role = $user['role'];
       	   $id = $user['id'];

       	   if ($user_name === $usernameDb) {
	       	   	if (password_verify($password, $passwordDb)) {
	       	   		if ($role == "admin") {
	       	   			$_SESSION['role'] = $role;
	       	   			$_SESSION['id'] = $id;
	       	   			$_SESSION['username'] = $usernameDb;
                        header("Location: ../index.php");
	       	   		}else if ($role == 'employee') {
	       	   			$_SESSION['role'] = $role;
	       	   			$_SESSION['id'] = $id;
	       	   			$_SESSION['username'] = $usernameDb;
                        header("Location: ../index.php");
	       	   		}else {
	       	   		   $em = "Unknown error occurred ";
							   header("Location: ../login.php?error=$em");
							   exit();
	       	   		}
	       	   	}else {
	       	   	   $em = "Incorrect username or password ";
						   header("Location: ../login.php?error=$em");
						   exit();
	       	   }
       	   }else {
       	   	   $em = "Incorrect username or password ";
			   header("Location: ../login.php?error=$em");
			   exit();
       	   }
       }
      

	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../login.php?error=$em");
   exit();
}