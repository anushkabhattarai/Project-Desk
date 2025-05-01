<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'inc/header.php';
require_once 'inc/nav.php';
?>

<div class="container-fluid" style="margin-left: 250px; padding: 20px;">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Shared Notes</h4>
                    <p class="text-muted mb-0">Notes shared with you and by you</p>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline-primary active" data-filter="received">
                        <i class="fa fa-inbox me-2"></i>Received
                    </button>
                    <button class="btn btn-outline-primary" data-filter="shared">
                        <i class="fa fa-share-alt me-2"></i>Shared by Me
                    </button>
                </div>
            </div>

            <!-- Notes Grid -->
            <div class="row g-4" id="notesContainer">
                <!-- Notes will be loaded here dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Share Note Modal -->
<div class="modal fade" id="shareNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shareNoteForm">
                    <input type="hidden" name="note_id" id="shareNoteId">
                    <div class="mb-3">
                        <label class="form-label">Share with</label>
                        <select class="form-select" name="shared_with" required>
                            <option value="">Select user...</option>
                            <!-- Users will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_edit" id="canEdit">
                            <label class="form-check-label" for="canEdit">
                                Allow editing
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="shareNoteBtn">Share</button>
            </div>
        </div>
    </div>
</div>

<style>
.note-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
}

.note-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.note-card .card-body {
    padding: 1.5rem;
}

.note-card .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.note-card .card-text {
    color: #6c757d;
    font-size: 0.9rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.note-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.note-card:hover .note-actions {
    opacity: 1;
}

.note-meta {
    font-size: 0.8rem;
    color: #adb5bd;
    margin-top: 1rem;
}

.shared-by {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.shared-by img {
    width: 24px;
    height: 24px;
    border-radius: 50%;
}

.btn-group .btn {
    border-radius: 6px;
    margin: 0 2px;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    color: white;
}
</style>

<script>
$(document).ready(function() {
    let currentFilter = 'received';

    // Load shared notes
    function loadSharedNotes(filter) {
        $.get('app/get_shared_notes.php', { filter: filter }, function(data) {
            $('#notesContainer').html(data);
        });
    }

    // Initial load
    loadSharedNotes(currentFilter);

    // Filter buttons
    $('.btn-group .btn').click(function() {
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        loadSharedNotes(currentFilter);
    });

    // Load users for sharing
    function loadUsers() {
        $.get('app/get_users.php', function(data) {
            const select = $('select[name="shared_with"]');
            select.find('option:not(:first)').remove();
            data.forEach(user => {
                select.append(`<option value="${user.id}">${user.full_name}</option>`);
            });
        });
    }

    // Share note
    $('#shareNoteBtn').click(function() {
        const formData = new FormData($('#shareNoteForm')[0]);
        
        $.ajax({
            url: 'app/share_note.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#shareNoteModal').modal('hide');
                    $('#shareNoteForm')[0].reset();
                    loadSharedNotes(currentFilter);
                }
            }
        });
    });

    // Open share modal
    $(document).on('click', '.share-note', function() {
        const noteId = $(this).data('note-id');
        $('#shareNoteId').val(noteId);
        loadUsers();
        $('#shareNoteModal').modal('show');
    });
});
</script> 