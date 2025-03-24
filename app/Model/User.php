<?php 

function get_all_users($conn) {
	$sql = "SELECT * FROM users";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($users) > 0) {
		return $users;
	} else {
		return 0;
	}
}


function insert_user($conn, $data){
	$sql = "INSERT INTO users (full_name, username, password, role) VALUES(?,?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}

function update_user($conn, $data) {
	// If profile pic is provided, update it too
	if ($data[4] !== null) {
		$sql = "UPDATE users 
				SET full_name=?, 
					username=?, 
					password=?, 
					role=?,
					profile_pic=?
				WHERE id=? AND role=?";
	} else {
		// Don't update profile pic if not provided
		$sql = "UPDATE users 
				SET full_name=?, 
					username=?, 
					password=?, 
					role=?
				WHERE id=? AND role=?";
		// Remove profile_pic from data array
		array_splice($data, 4, 1);
	}
	
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}

function delete_user($conn, $data){
	$sql = "DELETE FROM users WHERE id=? AND role=?";
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}


function get_user_by_id($conn, $id){
	$sql = "SELECT * FROM users WHERE id =? ";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$id]);

	if($stmt->rowCount() > 0){
		$user = $stmt->fetch();
	}else $user = 0;

	return $user;
}

function update_profile($conn, $data){
	$sql = "UPDATE users SET full_name=?,  password=? WHERE id=? ";
	$stmt = $conn->prepare($sql);
	$stmt->execute($data);
}

function count_users($conn){
	$sql = "SELECT id FROM users WHERE role='employee'";
	$stmt = $conn->prepare($sql);
	$stmt->execute([]);

	return $stmt->rowCount();
}  