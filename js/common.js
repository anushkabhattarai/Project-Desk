// Initialize all dropdowns and common functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

    // Handle notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationBar = document.getElementById('notificationBar');

    if (notificationBtn && notificationBar) {
        // Load initial notifications
        loadNotifications();
        
        // Refresh notifications every 30 seconds
        setInterval(loadNotifications, 30000);
    }

    // Handle sidebar toggle
    const navToggle = document.getElementById('navToggle');
    if (navToggle) {
        navToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
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
        
        // If no notifications, show a message
        if (!data.trim()) {
            $("#notifications").html('<li class="dropdown-item text-center py-3"><span class="text-secondary">No notifications</span></li>');
        }
    });
} 