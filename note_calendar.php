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
    <title>Notes Calendar - ProjectDesk</title>
    
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
                    <a href="task_calendar.php" class="btn btn-lg calendar-type-btn">
                        <i class="fa fa-tasks me-2"></i> Tasks Calendar
                    </a>
                    <a href="note_calendar.php" class="btn btn-lg calendar-type-btn active">
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
                                    <div class="event-description mt-3"></div>
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
/* Copy all styles from task_calendar.php */
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
        events: function(info, successCallback, failureCallback) {
            fetch('get_calendar_events.php?view=note')
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
        eventClick: function(info) {
            var event = info.event;
            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            var modalTitle = document.querySelector('#eventModal .modal-title');
            var modalBody = document.querySelector('#eventModal .modal-body');
            var eventDate = document.getElementById('eventDate');
            var viewDetailsBtn = document.getElementById('viewDetailsBtn');
            
            modalTitle.innerHTML = `<i class="fa fa-sticky-note me-2"></i>${event.title}`;
            
            const formattedDate = new Date(event.start).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            eventDate.textContent = formattedDate;
            
            modalBody.querySelector('.event-description').innerHTML = `
                <div class="border-top border-bottom py-3 my-2">
                    ${event.extendedProps.description || 'No description available'}
                </div>`;
            
            viewDetailsBtn.href = `editnote.php?id=${event.id.replace('n_', '')}`;
            
            modal.show();
        }
    });
    
    calendar.render();
});
</script>

</body>
</html>
