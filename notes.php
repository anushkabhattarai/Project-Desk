<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Include necessary files
include "DB_connection.php";
$title = "Notes";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .note-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .note-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .dark-mode {
            background-color: #1a1a1a !important;
            color: #ffffff;
        }

        .dark-mode .note-card {
            background: #2d2d2d;
            border: 1px solid #404040;
        }

        .search-box {
            position: relative;
            min-width: 300px;
        }

        .search-box input {
            padding-left: 35px;
            border-radius: 20px;
        }

        .search-box::before {
            content: '\f002';
            font-family: 'FontAwesome';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area -->
    <main style="margin-left: 250px; padding-top: 70px;">
        <div class="container-fluid px-4 py-3">
            <!-- Header Area -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">My Notes</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item active">Notes</li>
                        </ol>
                    </nav>
                </div>
                
                <!-- Search and Actions -->
                <div class="d-flex gap-3 align-items-center">
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="Search notes..." id="searchNotes">
                    </div>
                    <button class="btn btn-primary rounded-pill px-4" id="addNoteBtn">
                        <i class="fa fa-plus me-2"></i>New Note
                    </button>
                </div>
            </div>

            <!-- Notes Grid -->
            <div class="row g-4" id="notesGrid">
                <!-- Sample Note Card -->
                <div class="col-md-4 col-lg-3">
                    <div class="note-card p-3 h-100">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="card-title mb-0">Sample Note</h6>
                            <div class="note-actions">
                                <button class="btn btn-link btn-sm p-0 text-muted"><i class="fa fa-thumbtack"></i></button>
                                <button class="btn btn-link btn-sm p-0 text-muted"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-link btn-sm p-0 text-danger"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                        <p class="card-text small text-muted">This is a sample note content...</p>
                        <small class="text-muted">Created: 2024-02-20</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Action Button -->
    <button class="btn btn-primary rounded-circle position-fixed shadow" 
            style="bottom: 2rem; right: 2rem; width: 56px; height: 56px; z-index: 1000;"
            id="floatingAddBtn">
        <i class="fa fa-plus"></i>
    </button>

    <!-- Note Editor Modal -->
    <div class="modal fade" id="noteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <input type="text" class="form-control form-control-lg border-0" placeholder="Note Title">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="toolbar border-bottom pb-2 mb-2">
                        <button class="btn btn-sm btn-light" data-format="bold"><i class="fa fa-bold"></i></button>
                        <button class="btn btn-sm btn-light" data-format="italic"><i class="fa fa-italic"></i></button>
                        <button class="btn btn-sm btn-light" data-format="underline"><i class="fa fa-underline"></i></button>
                        <button class="btn btn-sm btn-light" data-format="list"><i class="fa fa-list"></i></button>
                    </div>
                    <div class="editor p-3" contenteditable="true" style="min-height: 200px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4" id="saveNote">Save Note</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Initialize Bootstrap components
        const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
        
        // Show modal when clicking add buttons
        document.getElementById('addNoteBtn').addEventListener('click', () => noteModal.show());
        document.getElementById('floatingAddBtn').addEventListener('click', () => noteModal.show());
        
        // Dark mode toggle
        const darkModeToggle = document.querySelector('#darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', () => {
                document.body.classList.toggle('dark-mode');
            });
        }
    </script>
</body>
</html> 