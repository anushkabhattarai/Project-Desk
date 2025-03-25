<header class="bg-white border-bottom sticky-top" style="height: 60px; z-index: 1030;">
	<div class="container-fluid h-100">
		<div class="row h-100 align-items-center">
			<!-- Left side with logo -->
			<div class="col-auto me-auto">
				<div class="d-flex align-items-center">
					<h2 class="mb-0 fw-bold fs-4">
						<span class="text-dark">Project</span><span class="text-primary">Desk</span>
					</h2>
					<button type="button" class="btn btn-sm ms-3 p-1" id="navToggle">
						<i class="fa fa-bars text-secondary" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			
			<!-- Right side with notifications and profile -->
			<div class="col-auto">
				<div class="d-flex align-items-center">
					<!-- Notification bell using image -->
					<div class="dropdown me-3">
						<button class="btn position-relative p-0" id="notificationBtn">
							<img src="img/notification.png" width="24" height="24" alt="Notifications">
							<span id="notificationNum" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger p-1" style="font-size: 0.6rem;">
								<span class="visually-hidden">notifications</span>
							</span>
						</button>
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

<!-- Notification panel -->
<div class="bg-white shadow-sm rounded border-0" id="notificationBar" 
	 style="display: none; position: absolute; right: 1rem; top: 3.5rem; width: 300px; z-index: 1040;">
	<div class="p-2 border-bottom d-flex justify-content-between align-items-center">
		<span class="fw-medium">Notifications</span>
	</div>
	<div style="max-height: 300px; overflow-y: auto;">
		<ul id="notifications" class="list-group list-group-flush mb-0"></ul>
	</div>
</div>

<!-- Load jQuery -->
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<!-- Load Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		// Load initial notifications
		loadNotifications();
		
		// Toggle notification panel
		$("#notificationBtn").click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			$("#notificationBar").toggle();
			
			// Load notifications when opening
			if($("#notificationBar").is(":visible")) {
				loadNotifications();
			}
		});
		
		// Close notification panel when clicking outside
		$(document).click(function(e) {
			if (!$(e.target).closest('#notificationBar, #notificationBtn').length) {
				$("#notificationBar").hide();
			}
		});
		
		// Toggle sidebar with the navigation button
		$("#navToggle").click(function() {
			$("body").toggleClass("sidebar-collapsed");
		});
		
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
					$("#notifications").html('<li class="list-group-item text-center py-3"><span class="text-secondary">No notifications</span></li>');
				}
			});
		}
		
		// Refresh notifications every 30 seconds
		setInterval(loadNotifications, 30000);
	});
</script>

<!-- Only necessary CSS for responsive behavior -->
<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Initial check for screen size
		checkScreenSize();
		
		// Listen for window resize
		window.addEventListener('resize', checkScreenSize);
		
		function checkScreenSize() {
			if (window.innerWidth < 992) {
				document.body.classList.add('sidebar-collapsed');
			} else {
				document.body.classList.remove('sidebar-collapsed');
			}
		}
	});
</script>
