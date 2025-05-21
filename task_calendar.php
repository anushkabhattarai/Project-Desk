<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Calendar - ProjectDesk</title>
    
    <!-- Required CSS -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css' rel='stylesheet' />
    
    <!-- Required JS -->
    <script src='https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.js'></script>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'inc/nav.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="margin-top: 60px;">
            <div class="calendar-wrapper">
                <div class="calendar-nav text-center">
                    <a href="task_calendar.php" class="btn btn-lg calendar-type-btn active">
                        <i class="fa fa-tasks me-2"></i> Tasks Calendar
                    </a>
                    <a href="note_calendar.php" class="btn btn-lg calendar-type-btn">
                        <i class="fa fa-sticky-note me-2"></i> Notes Calendar
                    </a>
                </div>
                <div class="calendar-container mt-4">
                    <div id="calendar"></div>
                    
                    <!-- Event Details Modal -->
                    <div class="modal fade" id="eventModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="event-date mb-3 text-muted">
                                        <i class="fa fa-calendar-o me-2"></i>
                                        <span id="eventDate"></span>
                                    </div>
                                    <div class="event-type mb-2">
                                        <span class="badge rounded-pill" id="eventType"></span>
                                    </div>
                                    <div class="event-description mt-3"></div>
                                    <div class="event-status mt-3"></div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="#" class="btn btn-primary" id="viewDetailsBtn">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.calendar-wrapper {
    padding: 1rem;
    background: #f8fafc;
    min-height: calc(100vh - 61px);
}

.calendar-nav {
    margin-bottom: 20px;
}

.calendar-type-btn {
    font-weight: 500;
    color: #64748b;
    border-radius: 8px;
    padding: 8px 20px;
}

.calendar-type-btn.active {
    background: #eff6ff;
    color: #3b82f6;
}

.calendar-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.fc {
    background: white !important;
}

.fc .fc-toolbar.fc-header-toolbar {
    margin: 0 0 2em 0;
}

.fc .fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    color: #334155 !important;
}

.fc .fc-col-header-cell {
    background: #f8fafc !important;
    padding: 12px 0 !important;
}

.fc .fc-col-header-cell-cushion {
    color: #64748b !important;
    font-weight: 600 !important;
    font-size: 0.875rem !important;
    padding: 6px !important;
}

.fc .fc-daygrid-day {
    background: white !important;
}

.fc .fc-daygrid-day.fc-day-today {
    background: #eff6ff !important;
}

.fc .fc-daygrid-day-number {
    color: #334155 !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    padding: 8px !important;
}

.fc .fc-button {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #64748b !important;
    font-weight: 500 !important;
    box-shadow: none !important;
}

.fc .fc-button:hover {
    background: #f1f5f9 !important;
    color: #334155 !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background: #3b82f6 !important;
    color: white !important;
    border-color: #3b82f6 !important;
}

.fc-day-today {
    background: rgba(59, 130, 246, 0.05) !important;
}

.fc .fc-day:hover {
    background: rgba(59, 130, 246, 0.02) !important;
}

.task-event {
    background: rgb(16, 107, 225) !important;  /* Solid blue background */
    color: #ffffff !important;  /* White text */
    border: none !important;
    font-weight: 600 !important;
    border-radius: 4px !important;
    padding: 8px 12px !important;
    margin: 3px 4px !important;
    box-shadow: 0 2px 4px rgba(16, 107, 225, 0.2) !important;
}

.event-content {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.event-title {
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    color: #ffffff !important;  /* White text */
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) !important;
}

.modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 1.75rem 1.75rem 0.75rem;
    background: linear-gradient(to right, #f8fafc, #f1f5f9);
}

.modal-body {
    padding: 1.25rem 1.75rem;
}

.modal-footer {
    padding: 1.25rem 1.75rem;
    background: linear-gradient(to right, #f8fafc, #f1f5f9);
}

.event-description {
    color: #475569;
    font-size: 1rem;
    line-height: 1.6;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.event-date {
    font-size: 0.95rem;
    color: #64748b;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'calc(100vh - 200px)',
        headerToolbar: {
            left: 'prev',
            center: 'title',
            right: 'next'
        },
        views: {
            dayGridMonth: {
                titleFormat: { month: 'long', year: 'numeric' }
            }
        },
        events: function(info, successCallback, failureCallback) {
            fetch('get_calendar_events.php?view=task')
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    failureCallback(error);
                });
        },
        displayEventTime: false,
        eventDidMount: function(info) {
            // Add tooltip
            $(info.el).tooltip({
                title: info.event.extendedProps.description,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        eventClick: function(info) {
            var event = info.event;
            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            var modalTitle = document.querySelector('#eventModal .modal-title');
            var modalBody = document.querySelector('#eventModal .modal-body');
            var eventDate = document.getElementById('eventDate');
            var eventType = document.getElementById('eventType');
            var viewDetailsBtn = document.getElementById('viewDetailsBtn');
            
            // Set title with icon
            modalTitle.innerHTML = `
                <i class="fa fa-tasks me-2"></i>
                ${event.title}`;
            
            // Format date nicely
            const formattedDate = new Date(event.start).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            eventDate.textContent = formattedDate;
            
            // Set type badge with better styling
            eventType.className = 'badge rounded-pill bg-primary';
            eventType.textContent = 'Task';
            
            // Set description with better formatting
            modalBody.querySelector('.event-description').innerHTML = `
                <div class="border-top border-bottom py-3 my-2">
                    ${event.extendedProps.description || 'No description available'}
                </div>`;
            
            // Show status for tasks
            if (event.extendedProps.status) {
                const statusColor = event.extendedProps.status === 'completed' ? 'success' : 
                                  event.extendedProps.status === 'in_progress' ? 'warning' : 'secondary';
                modalBody.querySelector('.event-status').innerHTML = `
                    <div class="d-flex align-items-center">
                        <span class="me-2">Status:</span>
                        <span class="badge bg-${statusColor}">${event.extendedProps.status}</span>
                    </div>`;
            } else {
                modalBody.querySelector('.event-status').innerHTML = '';
            }
            
            // Set correct view details link based on role
            if (event.extendedProps.user_role === 'employee') {
                viewDetailsBtn.href = `edit-task-employee.php?id=${event.id.replace('t_', '')}`;
            } else {
                viewDetailsBtn.href = `edit_task.php?id=${event.id.replace('t_', '')}`;
            }
            
            modal.show();
        },
        eventContent: function(arg) {
            return {
                html: `
                    <div class="event-content">
                        <div class="event-details">
                            <div class="event-title">${arg.event.title}</div>
                        </div>
                    </div>
                `
            };
        }
    });
    
    calendar.render();
});
</script>
</body>
</html>