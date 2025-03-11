<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    
    if (!isset($_GET['id'])) {
    	 header("Location: user.php");
    	 exit();
    }
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);

    if ($user == 0) {
    	 header("Location: user.php");
    	 exit();
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --bg-light: #f8f9ff;
            --border-color: #e9ecef;
            --sidebar-bg: #2A2F3C;
            --sidebar-width: 240px;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            padding: 1rem;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }

        .sidebar-header span {
            color: var(--primary-color);
        }

        #navList {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #navList li {
            margin-bottom: 0.5rem;
        }

        #navList li a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #navList li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        #navList li.active a,
        #navList li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: 800px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title a {
            font-size: 0.875rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Form Styles */
        .edit-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
            outline: none;
        }

        .btn-update {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-update:hover {
            background: #5254cc;
            transform: translateY(-1px);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .password-note {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        /* Password Input Styles */
        .password-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .btn-toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }

        .btn-toggle-password:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Project<span>Desk</span></h1>
        </div>
        <ul id="navList">
            <li>
                <a href="index.php">
                    <i class="fa fa-dashboard"></i>
                    Dashboard
                </a>
            </li>
            <li class="active">
                <a href="user.php">
                    <i class="fa fa-users"></i>
                    Manage Users
                </a>
            </li>
            <li>
                <a href="create_task.php">
                    <i class="fa fa-plus"></i>
                    Create Task
                </a>
            </li>
            <li>
                <a href="tasks.php">
                    <i class="fa fa-tasks"></i>
                    All Tasks
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fa fa-sign-out"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h4 class="page-title">
                Edit User
                <a href="user.php">
                    <i class="fa fa-arrow-left"></i> Back to Users
                </a>
            </h4>
        </div>

        <div class="edit-form">
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert alert-danger">
			  <?php echo stripcslashes($_GET['error']); ?>
			</div>
      	  <?php } ?>

            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success">
			  <?php echo stripcslashes($_GET['success']); ?>
			</div>
      	  <?php } ?>

            <form method="POST" action="app/update-user.php">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" 
                           name="full_name" 
                           class="form-control" 
                           placeholder="Enter full name" 
                           value="<?=$user['full_name']?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" 
                           name="user_name" 
                           class="form-control" 
                           placeholder="Enter username" 
                           value="<?=$user['username']?>">
				</div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-input-group">
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               id="passwordInput"
                               placeholder="Enter new password" 
                               value="**********">
                        <button type="button" 
                                class="btn-toggle-password" 
                                onclick="togglePassword()">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-note">
                        <i class="fa fa-info-circle"></i>
                        Leave password unchanged to keep the current password
                    </div>
                </div>

                <input type="hidden" name="id" value="<?=$user['id']?>">

                <button type="submit" class="btn-update">
                    <i class="fa fa-save"></i> Update User
                </button>
			</form>
        </div>
	</div>

<script type="text/javascript">
	var active = document.querySelector("#navList li:nth-child(2)");
	active.classList.add("active");

	// Password toggle functionality
	function togglePassword() {
		const passwordInput = document.getElementById('passwordInput');
		const toggleButton = document.querySelector('.btn-toggle-password i');
		
		if (passwordInput.type === 'password') {
			passwordInput.type = 'text';
			toggleButton.classList.remove('fa-eye');
			toggleButton.classList.add('fa-eye-slash');
		} else {
			passwordInput.type = 'password';
			toggleButton.classList.remove('fa-eye-slash');
			toggleButton.classList.add('fa-eye');
		}
	}
</script>
</body>
</html>
<?php } else { 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
 ?>