<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Notification.php";

    $notifications = get_all_my_notifications($conn, $_SESSION['id']);
    
    // Header section
    ?>
    <div class="notification-header d-flex justify-content-between align-items-center p-3 border-bottom">
        <h6 class="m-0 fw-bold">Notifications</h6>
        <a href="app/mark-all-read.php" class="text-success text-decoration-none small fw-medium">
            <i class="fa fa-check-circle me-1"></i> Mark all as read
        </a>
    </div>
    
    <?php if ($notifications == 0) { ?>
        <div class="p-4 text-center">
            <i class="fa fa-bell-slash fa-2x mb-2 text-secondary"></i>
            <p class="mb-0 text-muted">No notifications yet</p>
        </div>
    <?php } else { ?>
        <div class="notification-day px-3 py-2 bg-light text-secondary small fw-medium">
            Today
        </div>
        
        <div class="notification-list">
            <?php foreach ($notifications as $notification) { 
                $isUnread = $notification['is_read'] == 0;
                
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
                } elseif (stripos($notification['type'], 'note') !== false) {
                    $iconClass = 'fa-sticky-note';
                    $iconBg = 'bg-info-subtle';
                    $iconColor = 'text-info';
                } elseif (stripos($notification['type'], 'support') !== false || stripos($notification['type'], 'ticket') !== false) {
                    $iconClass = 'fa-life-ring';
                    $iconBg = 'bg-purple-subtle';
                    $iconColor = 'text-purple';
                } elseif (stripos($notification['type'], 'deleted') !== false) {
                    $iconClass = 'fa-trash';
                    $iconBg = 'bg-danger-subtle';
                    $iconColor = 'text-danger';
                } elseif (stripos($notification['type'], 'status') !== false) {
                    $iconClass = 'fa-refresh';
                    $iconBg = 'bg-warning-subtle';
                    $iconColor = 'text-warning';
                } elseif (stripos($notification['type'], 'shared') !== false) {
                    $iconClass = 'fa-share-alt';
                    $iconBg = 'bg-info-subtle';
                    $iconColor = 'text-info';
                } elseif (stripos($notification['type'], 'reply') !== false) {
                    $iconClass = 'fa-reply';
                    $iconBg = 'bg-secondary-subtle';
                    $iconColor = 'text-secondary';
                }
            ?>
                <a href="app/notification-read.php?notification_id=<?=$notification['id']?>" class="notification-item d-block text-decoration-none py-2 px-3 border-bottom <?= $isUnread ? 'bg-light' : '' ?>">
                    <div class="d-flex position-relative">
                        <div class="rounded-circle <?= $iconBg ?> p-2 me-2" style="width: 36px; height: 36px;">
                            <i class="fa <?= $iconClass ?> <?= $iconColor ?> d-flex justify-content-center"></i>
                        </div>
                        <div>
                            <?php if ($isUnread) { ?>
                                <div class="position-absolute" style="left: -4px; top: 15px;">
                                    <span class="bg-success rounded-circle d-inline-block" style="width: 8px; height: 8px;"></span>
                                </div>
                            <?php } ?>
                            <div class="d-flex justify-content-between w-100">
                                <span class="fw-semibold text-dark mb-1"><?= $notification['type'] ?></span>
                                <small class="text-muted ms-2">
                                    <?= date('g\h \a\g\o', strtotime('now') - strtotime($notification['date'])) ?>
                                </small>
                            </div>
                            <p class="mb-0 text-secondary small">
                                <?= $notification['message'] ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php } ?>
        </div>
        
        <div class="p-3 text-center border-top">
            <a href="notifications.php" class="text-decoration-none text-secondary small">View all notifications</a>
        </div>
    <?php } 
} else { 
    echo "";
}
?>

<style>
    .notification-list {
        max-height: 350px;
        overflow-y: auto;
    }
    
    .notification-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>