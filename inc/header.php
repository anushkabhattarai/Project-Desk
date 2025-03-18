<header class="bg-white" style="border-bottom: 1px solid rgba(0,0,0,0.08);">
	<div class="d-flex justify-content-between align-items-center px-4 py-3">
		<!-- Left side with logo -->
		<div class="d-flex align-items-center">
			<h2 class="mb-0 fs-4">
				Project<b class="text-primary">Desk</b>
				<label for="checkbox" class="ms-2">
					<i id="navbtn" class="fa fa-bars text-secondary" aria-hidden="true"></i>
				</label>
			</h2>
		</div>
		
		<!-- Right side with notifications and profile -->
		<div class="d-flex align-items-center gap-4">
			<!-- Notification bell - adjusted position -->
			<div class="position-relative me-2">
				<span class="notification btn p-0" id="notificationBtn">
					<img src="img/notification.png" 
						 width="32" 
						 height="32" 
						 alt="Notifications"
						 style="filter: opacity(0.6); margin-left: -8px;">
					<span id="notificationNum" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
						<span class="visually-hidden">notifications</span>
					</span>
				</span>
			</div>

			<!-- Profile dropdown - fixed admin display -->
			<div class="dropdown">
				<button class="btn btn-link text-decoration-none dropdown-toggle p-0" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
					<div class="d-flex align-items-center gap-2">
						<!-- Profile image -->
						<img src="<?php echo $_SESSION['role'] == 'admin' ? 'img/admin.png' : 'img/user.png'; ?>" 
							 class="rounded-circle"
							 width="32" 
							 height="32" 
							 alt="Profile"
							 style="object-fit: cover;">
						<!-- Name and role - simplified -->
						<div class="d-flex flex-column text-start">
							<span class="text-dark fw-medium">
								Admin
							</span>
						</div>
						<i class="fa fa-chevron-down text-secondary ms-2 small"></i>
					</div>
				</button>
				<ul class="dropdown-menu dropdown-menu-end shadow-sm border-light mt-2" aria-labelledby="userDropdown">
					<li><a class="dropdown-item py-2" href="profile.php">
						<i class="fa fa-user me-2 text-secondary"></i> Edit Profile
					</a></li>
					<li><hr class="dropdown-divider"></li>
					<li><a class="dropdown-item py-2 text-danger" href="logout.php">
						<i class="fa fa-sign-out me-2"></i> Logout
					</a></li>
				</ul>
			</div>
		</div>
	</div>
</header>

<!-- Notification panel -->
<div class="notification-bar shadow-sm border bg-white rounded-3" id="notificationBar" style="display: none; position: absolute; right: 1rem; top: 4rem; width: 300px; z-index: 1000;">
	<div class="p-3 border-bottom">
		<h6 class="m-0">Notifications</h6>
	</div>
	<div class="notification-list p-2">
		<ul id="notifications" class="list-unstyled m-0"></ul>
	</div>
</div>

<!-- First load jQuery -->
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

<!-- Then Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Then notification scripts -->
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
			});
		}
		
		// Refresh notifications every 30 seconds
		setInterval(loadNotifications, 30000);
	});
</script>

<style>
	.notification-list {
		max-height: 300px;
		overflow-y: auto;
	}
	
	#notifications li {
		padding: 10px;
		border-bottom: 1px solid #eee;
	}
	
	#notifications li:last-child {
		border-bottom: none;
	}
	
	#notifications li a {
		color: #333;
		text-decoration: none;
		display: block;
	}
	
	#notifications li mark {
		background-color: #e3f2fd;
		padding: 2px 5px;
		border-radius: 3px;
	}
	
	#notifications li small {
		color: #666;
		font-size: 0.8em;
		display: block;
		margin-top: 4px;
	}
	
	#notificationNum:empty {
		display: none;
	}
</style>
