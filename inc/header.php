<header class="bg-white border-bottom p-0 m-0" style="height: 60px; position: fixed; top: 0; right: 0; left: 0; z-index: 1031; width: 100vw;">
	<div class="container-fluid h-100">
		<div class="row h-100 align-items-center">
			<!-- Left side with logo -->
			<div class="col-auto me-auto">
				<div class="d-flex align-items-center">
					<h2 class="mb-0 fw-bold fs-4">
						<span class="text-dark">Project</span><span class="text-primary">Desk</span>
					</h2>
				</div>
			</div>
			
			 <!-- Center section for premium upgrade -->
			<div class="col-auto">
				<?php
				if ($_SESSION['role'] !== 'admin') { // Only show for non-admin users
					try {
						$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['id'];
						$planQuery = "SELECT p.name, p.is_unlimited 
									FROM subscriptions s 
									JOIN plans p ON s.plan_id = p.id 
									WHERE s.user_id = ? 
									AND s.status = 'active' 
									AND CURRENT_DATE BETWEEN s.start_date AND s.end_date";
						
						$stmt = $conn->prepare($planQuery);
						$stmt->execute([$userId]);
						$userPlan = $stmt->fetch();

						if (!$userPlan || ($userPlan && $userPlan['is_unlimited'] == 0)) {
							echo '<a href="plans.php?highlight=premium" class="btn btn-light btn-sm d-flex align-items-center text-decoration-none">
									<img src="img/premium.png" alt="Premium" style="width: 24px; height: 24px; margin-right: 8px;">
									<span class="text-muted" style="font-size: 0.85rem;">Upgrade to Premium</span>
								  </a>';
						}
					} catch (Exception $e) {
						error_log("Plan check error: " . $e->getMessage());
					}
				}
				?>
			</div>

			<!-- Right side with notifications and profile -->
			<div class="col-auto">
				<div class="d-flex align-items-center">
					<!-- Notification bell using image -->
					<div class="dropdown me-3">
						<button class="btn position-relative p-0" id="notificationBtn" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="img/noti.png" width="24" height="24" alt="Notifications">
							<span id="notificationNum" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger p-1" style="font-size: 0.6rem;">
								<span class="visually-hidden">notifications</span>
							</span>
						</button>
						<ul class="dropdown-menu dropdown-menu-end" id="notificationBar" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
							<li class="dropdown-header">Notifications</li>
							<li><hr class="dropdown-divider"></li>
							<div id="notifications"></div>
						</ul>
					</div>

					<!-- Profile dropdown -->
					<div class="dropdown">
						<button class="btn dropdown-toggle p-0 d-flex align-items-center" 
								type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="<?php echo $_SESSION['role'] == 'admin' ? 'img/admin.png' : 'img/user.png'; ?>" 
								 class="rounded-circle" 
								 width="32" 
								 height="32" 
								 alt="Profile">
							<span class="d-none d-sm-inline-block ms-2 text-dark small">
								<?php 
									echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username']; 
								?>
							</span>
						</button>
						<ul class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userDropdown">
							<li><a class="dropdown-item" href="edit_profile.php">
								<i class="fa fa-user me-2 text-primary"></i> Profile
							</a></li>
							<li><a class="dropdown-item" href="user-payment-history.php">
								<i class="fa fa-history me-2 text-info"></i> Payment History
							</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a class="dropdown-item" href="logout.php">
								<i class="fa fa-sign-out me-2 text-danger"></i> Logout
							</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Load required scripts -->
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/common.js"></script>

<script type="text/javascript">
	function toggleNav() {
		document.body.classList.toggle('sidebar-collapsed');
	}

	$(document).ready(function(){
		// Load initial notifications
		loadNotifications();
		
		// Function to load notifications
		function loadNotifications() {
			$.get("app/notification-count.php", function(count) {
				if(count.trim() !== '') {
					$("#notificationNum").html(count);
					$("#notificationNum").show();
				} else {
					$("#notificationNum").hide();
				}
			});
			
			$.get("app/notification.php", function(data) {
				$("#notifications").html(data);
				
				// If no notifications, show a message
				if (!data.trim()) {
					$("#notifications").html('<li class="dropdown-item text-center py-3"><span class="text-secondary">No notifications</span></li>');
				}
			});
		}
		
		// Refresh notifications every 30 seconds
		setInterval(loadNotifications, 30000);
	});
</script>

<!-- Only necessary CSS for responsive behavior -->
<style>
	header {
		position: fixed !important;
		width: 100vw !important;
		right: 0 !important;
		padding-right: 0 !important;
		margin-right: 0 !important;
		z-index: 1040 !important; /* Increase z-index */
	}

	/* Add dropdown styles */
	.dropdown-menu {
		z-index: 1045 !important;
		margin-top: 10px !important;
		box-shadow: 0 4px 16px rgba(0,0,0,0.1) !important;
	}

	#notificationBar {
		position: fixed !important;
		top: 60px !important;
		right: 20px !important;
		max-height: calc(100vh - 100px) !important;
	}

	nav {
		transition: width 0.3s ease;
	}
	
	.sidebar-collapsed {
		margin-left: 0;
	}
	
	@media (max-width: 991.98px) {
		nav {
			width: 0px !important;
		}
		body {
			margin-left: 0 !important;
		}
	}
</style>