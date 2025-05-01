<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Notes - ProjectDesk</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
    body {
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        line-height: 1;
    }

    .page-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    #checkbox {
        display: none;
    }

    header {
        margin: 0 !important;
        padding: 0 !important;
    }

    main {
        margin-left: 250px;
        background: #f8fafc;
        transition: margin-left 0.3s ease;
        flex: 0;
    }

    /* Search box styling */
    .search-box {
        min-width: 250px;
    }

    .search-input {
        padding-right: 35px;
        border-radius: 20px;
    }

    /* Note card styling */
    .note-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }

    .note-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, #0d6efd, #0dcaf0);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .note-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    .note-card:hover::before {
        opacity: 1;
    }

    .note-card .card-body {
        padding: 1.5rem;
    }

    .note-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        max-width: 80%;
    }

    .note-card .card-text {
        color: #6c757d;
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.5;
    }

    .note-actions {
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .note-card:hover .note-actions {
        opacity: 1;
    }

    .note-date {
        font-size: 0.8rem;
        color: #adb5bd;
    }

    /* Button styling */
    .btn-group .btn {
        border-radius: 6px;
        margin: 0 2px;
        padding: 0.5rem 1rem;
    }

    .btn-group .btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        main {
            margin-left: 0;
        }
        
        .search-box {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .btn-group {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .btn-group .btn {
            flex: 1;
        }
        
        .form-select {
            width: 100% !important;
        }
    }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <input type="checkbox" id="checkbox">
        <?php include "inc/header.php" ?>
        <?php include "inc/nav.php" ?>
        
        <main>
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                        <!-- Header Section with Search -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                            <div class="mb-3 mb-lg-0">
                                <h4 class="mb-1 text-primary">Private Notes</h4>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                        <li class="breadcrumb-item active">Private Notes</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="search-box">
                                    <div class="position-relative">
                                        <input type="text" class="form-control search-input" placeholder="Search notes...">
                                        <i class="fa fa-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
                                    </div>
                                </div>
                                <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#newNoteModal">
                                    <i class="fa fa-plus me-2"></i>New Note
                                </button>
                            </div>
                        </div>

                        <!-- Filter and Sort Options -->
                        <div class="bg-white rounded-3 p-3 mb-4 shadow-sm border">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary active" data-filter="all">
                                        All Notes
                                    </button>
                                    <button class="btn btn-outline-primary" data-filter="pinned">
                                        <i class="fa fa-thumb-tack me-1"></i>Pinned
                                    </button>
                                </div>
                                <select class="form-select" style="width: auto;">
                                    <option value="recent">Most Recent</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="title">Title A-Z</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes Grid -->
                        <div class="row g-4" id="notesContainer">
                            <!-- Sample Note Card -->
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                <div class="note-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title text-truncate mb-0">Sample Note Title</h6>
                                            <div class="dropdown">
                                                <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#"><i class="fa fa-pencil me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fa fa-thumb-tack me-2"></i>Pin</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fa fa-share-alt me-2"></i>Share</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#"><i class="fa fa-trash me-2"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <p class="card-text mb-3">This is a sample note content that will be displayed in the card...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="note-date"><i class="fa fa-clock-o me-1"></i>2 days ago</span>
                                            <div class="note-actions">
                                                <button class="btn btn-sm btn-link text-muted p-0 me-2"><i class="fa fa-pencil"></i></button>
                                                <button class="btn btn-sm btn-link text-muted p-0"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- New Note Modal -->
    <div class="modal fade" id="newNoteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-lock me-2"></i>Create New Private Note</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newNoteForm">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" placeholder="Enter note title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="6" placeholder="Write your note here..." required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="pinned" id="pinNote">
                            <label class="form-check-label" for="pinNote">
                                Pin this note
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNoteBtn">
                        <i class="fa fa-save me-2"></i>Save Note
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Load private notes
        function loadPrivateNotes() {
            $.get('app/get_private_notes.php', function(data) {
                $('#notesContainer').html(data);
            });
        }

        // Initial load
        loadPrivateNotes();

        // Search functionality
        $('.search-input').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.note-card').each(function() {
                const title = $(this).find('.card-title').text().toLowerCase();
                const content = $(this).find('.card-text').text().toLowerCase();
                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Filter buttons
        $('.btn-group .btn').click(function() {
            $('.btn-group .btn').removeClass('active');
            $(this).addClass('active');
            const filter = $(this).data('filter');
            
            if (filter === 'pinned') {
                $('.note-card').each(function() {
                    if ($(this).data('pinned')) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else {
                $('.note-card').show();
            }
        });

        // Save new note
        $('#saveNoteBtn').click(function() {
            const formData = new FormData($('#newNoteForm')[0]);
            
            $.ajax({
                url: 'app/save_private_note.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        $('#newNoteModal').modal('hide');
                        $('#newNoteForm')[0].reset();
                        loadPrivateNotes();
                    }
                }
            });
        });
    });
    </script>
</body>
</html> 