<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";
    include "app/Model/Notification.php";
    // include "app/Model/User.php";

    $notifications = get_all_my_notifications($conn, $_SESSION['id']);

 ?>
<!DOCTYPE html>
<html>
<head>
	<title>Notifications</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body, .body, .section-1 {
			background-color: #f8f9fa !important;
		}
		.notification-card {
			border-radius: 15px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.05);
			background: white;
		}
		.notification-header {
			border-bottom: 1px solid #eee;
			padding: 15px 20px;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.notification-day {
			background-color: #f8f9fa;
			padding: 10px 20px;
			font-weight: 500;
			color: #6c757d;
			border-bottom: 1px solid #eee;
		}
		.notification-item {
			padding: 15px 20px;
			border-bottom: 1px solid #eee;
			position: relative;
		}
		.notification-item.unread {
			background-color: #e8f4f8;
		}
		.notification-item:last-child {
			border-bottom: none;
		}
		.notification-icon {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background-color: #eee;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-right: 15px;
		}
		.notification-mark-all {
			color: #28a745;
			text-decoration: none;
			font-weight: 500;
		}
		.notification-mark-all:hover {
			text-decoration: underline;
		}
		.notification-info {
			color: #28a745;
		}
		.notification-warning {
			color: #dc3545;
		}
		.notification-footer {
			padding: 15px 20px;
			border-top: 1px solid #eee;
			text-align: center;
		}
		.notification-dot {
			width: 8px;
			height: 8px;
			background: #28a745;
			border-radius: 50%;
			display: inline-block;
			margin-right: 10px;
		}
		.notification-time {
			position: absolute;
			top: 15px;
			right: 20px;
			color: #6c757d;
			font-size: 0.85rem;
		}
	</style>
</head>
<body class="bg-white">
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
			<div class="container-fluid px-4 py-3">
				<div class="row">
					<div class="col-md-8 mx-auto">
						<div class="notification-card mb-4">
							<div class="notification-header">
								<h4 class="mb-0 fw-bold">Notifications</h4>
								<a href="app/mark-all-read.php" class="notification-mark-all">
									<i class="fa fa-check-circle me-1"></i> Mark all as read
								</a>
							</div>
							
							<?php if (isset($_GET['success'])) { ?>
								<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
									<?php echo stripcslashes($_GET['success']); ?>
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
							<?php } ?>
							
							<?php if ($notifications != 0) { ?>
								<div class="notification-day">
									Today
								</div>
								
								<!-- Notification Items -->
								<?php $i=0; foreach ($notifications as $notification) { 
									$isUnread = $notification['is_read'] == 0 ? 'unread' : '';
									
									// Determine icon based on notification type
									$iconClass = 'fa-bell';
									$iconBg = 'bg-primary-subtle';
									$iconColor = 'text-primary';
									
									if (stripos($notification['type'], 'task') !== false) {
										$iconClass = 'fa-tasks';
									} elseif (stripos($notification['type'], 'payment') !== false) {
										$iconClass = 'fa-money';
										$iconBg = 'bg-success-subtle';
										$iconColor = 'text-success';
									} elseif (stripos($notification['type'], 'reminder') !== false) {
										$iconClass = 'fa-clock-o';
										$iconBg = 'bg-warning-subtle';
										$iconColor = 'text-warning';
									}
								?>
								<div class="notification-item <?= $isUnread ?>">
									<div class="d-flex">
										<div class="notification-icon <?= $iconBg ?>">
											<i class="fa <?= $iconClass ?> <?= $iconColor ?>"></i>
										</div>
										<div>
											<?php if ($notification['is_read'] == 0) { ?>
												<span class="notification-dot"></span>
											<?php } ?>
											<strong class="d-block mb-1"><?= $notification['type'] ?></strong>
											<p class="mb-0 text-secondary">
												<?= $notification['message'] ?>
											</p>
										</div>
									</div>
									<span class="notification-time">
										<?= date('g\h \a\g\o', strtotime('now') - strtotime($notification['date'])) ?>
									</span>
								</div>
								<?php } ?>
								
								<div class="notification-footer">
									<a href="#" class="text-decoration-none text-secondary">View all notifications</a>
								</div>
								
							<?php } else { ?>
								<div class="text-center py-5">
									<i class="fa fa-bell-slash fa-3x text-secondary mb-3"></i>
									<h5 class="text-muted">No notifications yet</h5>
									<p class="text-secondary">When you receive notifications, they will appear here.</p>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(4)");
		active.classList.add("active");
	</script>
</body>
</html>
<?php }else{ 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
 ?>