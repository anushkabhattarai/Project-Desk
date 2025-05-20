<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";

    if (!$conn) {
        die("Connection failed: Connection not established");
    }

    // Define these functions first before using them - updated for PDO
    if (!function_exists('get_users_by_role')) {
        function get_users_by_role($conn, $role) {
            try {
                $sql = "SELECT * FROM users WHERE LOWER(role) = LOWER(:role)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':role', $role);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($results) > 0) {
                    return $results;
                } else {
                    return 0;
                }
            } catch (PDOException $e) {
                // For debugging
                // echo "Error: " . $e->getMessage();
                return 0;
            }
        }
    }

    if (!function_exists('search_users')) {
        function search_users($conn, $search) {
            try {
                $search = "%$search%";
                $sql = "SELECT * FROM users WHERE full_name LIKE :search OR username LIKE :search OR email LIKE :search";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':search', $search);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($results) > 0) {
                    return $results;
                } else {
                    return 0;
                }
            } catch (PDOException $e) {
                // echo "Error: " . $e->getMessage();
                return 0;
            }
        }
    }

    if (!function_exists('get_users_by_role_and_search')) {
        function get_users_by_role_and_search($conn, $role, $search) {
            try {
                $search = "%$search%";
                $sql = "SELECT * FROM users WHERE LOWER(role) = LOWER(:role) AND (full_name LIKE :search OR username LIKE :search OR email LIKE :search)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':search', $search);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($results) > 0) {
                    return $results;
                } else {
                    return 0;
                }
            } catch (PDOException $e) {
                // echo "Error: " . $e->getMessage();
                return 0;
            }
        }
    }

    // Now use the functions
    $filter_role = isset($_GET['role']) ? $_GET['role'] : 'all';
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Get users based on filters
    if ($filter_role != 'all' && $search_query != '') {
        $users = get_users_by_role_and_search($conn, $filter_role, $search_query);
    } else if ($filter_role != 'all') {
        $users = get_users_by_role($conn, $filter_role);
    } else if ($search_query != '') {
        $users = search_users($conn, $search_query);
    } else {
        $users = get_all_users($conn);
    }
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Manage Users</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<?php include "inc/nav.php" ?>
	
	<main style="margin-left: 250px; padding-top: 60px;">
		<div class="container-fluid p-4">
			<!-- Header with title -->
			<div class="d-flex justify-content-between align-items-center mb-4">
				<div>
					<h4 class="fw-bold mb-1">User Management</h4>
					<p class="text-muted mb-0">
						<?php if ($users !== 0) { ?>
							Total: <?php echo count($users); ?> users
						<?php } else { ?>
							No users found
						<?php } ?>
					</p>
				</div>
			</div>
			
			<!-- Filters and search bar -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-body">
					<form method="get" action="" id="filterForm">
						<div class="row g-3 align-items-center">
							<div class="col-md-4">
								<div class="input-group">
									<span class="input-group-text bg-white border-end-0">
										<i class="fa fa-search text-muted"></i>
									</span>
									<input type="text" name="search" id="searchInput" class="form-control border-start-0" 
										placeholder="Search by name, username, or email..." value="<?php echo $search_query; ?>"
										autocomplete="off">
								</div>
							</div>
							
							<div class="col-md-3">
								<select name="role" id="roleSelect" class="form-select">
									<option value="all" <?php echo $filter_role == 'all' ? 'selected' : ''; ?>>All Roles</option>
									<option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
									<option value="employee" <?php echo $filter_role == 'employee' ? 'selected' : ''; ?>>Employee</option>
								</select>
							</div>
							
							<div class="col-md-5 text-md-end">
								<?php if ($search_query != '' || $filter_role != 'all') { ?>
									<a href="user.php" class="btn btn-outline-secondary" id="resetBtn">
										<i class="fa fa-refresh me-1"></i> Reset Filters
									</a>
								<?php } ?>
								<button type="submit" class="btn btn-primary ms-2">
									<i class="fa fa-filter me-1"></i> Apply Filters
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<?php if (isset($_GET['success'])) { ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<?php echo stripcslashes($_GET['success']); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php } ?>

			<!-- User Table -->
			<div id="resultsContainer">
				<?php if ($users != 0) { ?>
					<div class="card border-0 shadow-sm">
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-hover align-middle mb-0">
									<thead class="bg-light">
										<tr>
											<th class="py-3 ps-4">#</th>
											<th class="py-3">Name</th>
											<th class="py-3">Username</th>
											<th class="py-3">Role</th>
											<th class="py-3 text-end pe-4">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php $i=0; foreach ($users as $user) { ?>
											<tr>
												<td class="ps-4"><?=++$i?></td>
												<td>
													<div class="d-flex align-items-center">
														<div class="avatar text-center rounded-circle bg-primary text-white" 
															style="width: 40px; height: 40px; line-height: 40px;">
															<?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
														</div>
														<div class="ms-3">
															<h6 class="mb-0"><?=$user['full_name']?></h6>
														</div>
													</div>
												</td>
												<td><?=$user['username']?></td>
												<td>
													<?php if ($user['role'] == 'admin') { ?>
														<span class="badge bg-primary rounded-pill">Admin</span>
													<?php } else { ?>
														<span class="badge bg-info text-dark rounded-pill">Employee</span>
													<?php } ?>
												</td>
												<td class="text-end pe-4">
													<a href="edit-user.php?id=<?=$user['id']?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
														<i class="fa fa-edit me-1"></i> Edit
													</a>
													<a href="delete-user.php?id=<?=$user['id']?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
														onclick="return confirm('Are you sure you want to delete this user?')">
														<i class="fa fa-trash me-1"></i> Delete
													</a>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php } else { ?>
					<div class="card border-0 shadow-sm">
						<div class="card-body py-5">
							<div class="text-center">
								<i class="fa fa-users fa-3x text-muted mb-3"></i>
								<h5 class="text-muted mb-3">No users found</h5>
								<?php if ($search_query != '' || $filter_role != 'all') { ?>
									<p class="text-muted mb-3">Try changing your search criteria</p>
									<a href="user.php" class="btn btn-outline-primary">
										<i class="fa fa-refresh me-2"></i>Reset Filters
									</a>
								<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		// Set active navigation
		var active = document.querySelector("#navList li:nth-child(2)");
		active.classList.add("active");
		
		// Live search functionality with improved debounce
		document.addEventListener('DOMContentLoaded', function() {
			const searchInput = document.getElementById('searchInput');
			const roleSelect = document.getElementById('roleSelect');
			const filterForm = document.getElementById('filterForm');
			
			// Debounce function to limit how often the search is performed
			function debounce(func, wait) {
				let timeout;
				return function() {
					const context = this;
					const args = arguments;
					clearTimeout(timeout);
					timeout = setTimeout(function() {
						func.apply(context, args);
					}, wait);
				};
			}
			
			// Function to submit the form
			const submitForm = debounce(function() {
				filterForm.submit();
			}, 300); // Reduced to 300ms for faster response
			
			// Event listener for search input
			if (searchInput) {
				searchInput.addEventListener('input', submitForm);
				
				// Focus the search input on page load for better UX
				setTimeout(function() {
					searchInput.focus();
				}, 500);
			}
			
			// Event listener for role select
			if (roleSelect) {
				roleSelect.addEventListener('change', function() {
					filterForm.submit();
				});
			}
			
			// Reset button functionality
			const resetBtn = document.getElementById('resetBtn');
			if (resetBtn) {
				resetBtn.addEventListener('click', function(e) {
					e.preventDefault();
					window.location.href = 'user.php';
				});
			}
		});
	</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>