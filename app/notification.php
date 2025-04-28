<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "../DB_connection.php";
    include "Model/Notification.php";

    $notifications = get_all_my_notifications($conn, $_SESSION['id']);
    
    // Header section
    ?>
    <div class="notification-dropdown shadow">
        <div class="dropdown-header d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="m-0 fw-bold">Notifications</h6>
            <a href="app/mark-all-read.php" class="mark-all-read">
                <i class="fa fa-check-circle me-1"></i> Mark all as read
            </a>
        </div>
        
        <div class="notification-content-wrapper">
            <?php if ($notifications == 0) { ?>
                <div class="empty-state p-4 text-center">
                    <i class="fa fa-bell-slash fa-2x mb-2 text-secondary opacity-50"></i>
                    <p class="mb-0 text-muted">No notifications yet</p>
                </div>
            <?php } else { ?>
                <div class="dropdown-day px-3 py-2 bg-light text-secondary small fw-medium">
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
                            $iconBg = 'bg-primary-subtle';
                            $iconColor = 'text-primary';
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
                        
                        // Calculate time ago in a more readable format
                        $date_diff = strtotime('now') - strtotime($notification['date']);
                        $time_ago = '';
                        
                        if ($date_diff < 60) {
                            $time_ago = 'Just now';
                        } elseif ($date_diff < 3600) {
                            $mins = floor($date_diff / 60);
                            $time_ago = $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
                        } elseif ($date_diff < 86400) {
                            $hours = floor($date_diff / 3600);
                            $time_ago = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                        } else {
                            $days = floor($date_diff / 86400);
                            $time_ago = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                        }
                    ?>
                        <a href="app/notification-read.php?notification_id=<?=$notification['id']?>" class="notification-item d-block text-decoration-none py-3 px-3 border-bottom <?= $isUnread ? 'unread' : '' ?>">
                            <div class="d-flex align-items-center position-relative">
                                <div class="icon-wrapper rounded-circle <?= $iconBg ?> p-2 me-3" style="width: 46px; height: 46px;">
                                    <i class="fa <?= $iconClass ?> <?= $iconColor ?> d-flex justify-content-center align-items-center h-100"></i>
                                </div>
                                <div class="notification-content flex-grow-1">
                                    <?php if ($isUnread) { ?>
                                        <div class="unread-indicator position-absolute" style="left: -4px; top: 50%; transform: translateY(-50%);">
                                            <span class="bg-primary rounded-circle d-inline-block" style="width: 10px; height: 10px;"></span>
                                        </div>
                                    <?php } ?>
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <span class="fw-semibold text-dark"><?= $notification['type'] ?></span>
                                        <small class="text-muted ms-2">
                                            <?= $time_ago ?>
                                        </small>
                                    </div>
                                    <p class="mb-0 text-secondary small mt-1">
                                        <?= $notification['message'] ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        
        <div class="dropdown-footer p-3 text-center border-top">
            <a href="notifications.php" class="view-all-link d-inline-block">View all notifications</a>
        </div>
    </div>
<?php 
} else { 
    echo "";
}
?>

<style>
    .notification-dropdown {
        width: 420px;
        max-width: 100%;
        border-radius: 14px;
        overflow: hidden;
        background-color: white;
        border: 1px solid rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        max-height: 85vh;
    }
    
    .dropdown-header {
        background-color: white;
        flex-shrink: 0;
    }
    
    .notification-content-wrapper {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .mark-all-read {
        color: #0d6efd;
        font-size: 13px;
        text-decoration: none;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .mark-all-read:hover {
        background-color: rgba(13, 110, 253, 0.1);
        text-decoration: none;
    }
    
    .notification-list {
        max-height: calc(85vh - 140px);
        overflow-y: auto;
        scrollbar-width: thin;
        flex: 1;
    }
    
    .notification-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .notification-list::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    
    .notification-list::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 20px;
    }
    
    .notification-item {
        transition: all 0.2s ease;
    }
    
    .notification-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .notification-item.unread {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .dropdown-footer {
        background-color: white;
        flex-shrink: 0;
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 1;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    
    .view-all-link {
        color: #6c757d;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        padding: 7px 16px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .view-all-link:hover {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .dropdown-day {
        font-size: 12px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    
    .empty-state {
        padding: 30px 0;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .icon-wrapper i {
        font-size: 18px;
    }
</style>