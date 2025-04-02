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
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .note-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            padding: 1.75rem;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            height: 280px; /* Fixed height */
            overflow: hidden;
        }
        
        .note-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
        }

        .note-card .username {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
            padding-right: 0.5rem;
            letter-spacing: -0.02em;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 1.8rem; /* Fixed height for title */
        }

        .note-card .timestamp {
            font-size: 0.85rem;
            color: #94a3b8;
            margin: 0.5rem 0 1rem 0;
            height: 1.2rem; /* Fixed height for timestamp */
        }

        .note-card .note-content {
            color: #4a5568;
            font-size: 0.95rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 1rem;
            flex: 1;
            height: 6.4rem; /* Fixed height for 4 lines of text */
        }

        .note-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: auto;
            height: 3.5rem; /* Fixed height for footer */
        }

        .edit-btn {
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
            background: #3b82f6;
            color: white;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            border: none;
            white-space: nowrap;
        }

        .edit-btn:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-1px);
            text-decoration: none;
        }

        .note-card .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .status-badge.not-started {
            background-color: #F5F5F5;
            color: #666666;
        }

        .status-badge.pending {
            background-color: #FFF7E6;
            color: #D46B08;
        }

        .status-badge.completed {
            background-color: #F0FFF4;
            color: #237804;
        }

        .status-badge i {
            font-size: 0.75rem;
            margin-right: 0.4rem;
        }

        .edit-button {
            padding: 0.35rem 1rem;
            font-size: 0.85rem;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            background: white;
            color: #666;
            transition: all 0.3s ease;
        }

        .edit-button:hover {
            background: #f8f9fa;
            color: #333;
            border-color: #d0d0d0;
        }

        .edit-button i {
            font-size: 0.8rem;
            margin-right: 0.25rem;
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

        .card {
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid #eee;
            padding: 8px 0;
        }
        .ql-container.ql-snow {
            border: none;
        }
        .modal-content {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .badge {
            font-weight: normal;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "inc/header.php"; ?>
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
                        <input type="text" class="form-control" placeholder="Search notes..." id="searchNotes" name="searchNotes">
                    </div>
                    <button class="btn btn-primary rounded-pill px-4" onclick="window.location.href='editnote.php'">
                        <i class="fa fa-plus me-2"></i>New Note
                    </button>
                </div>
            </div>

            <!-- Notes Grid -->
            <div class="row g-3" id="notesGrid">
                <!-- Notes will be dynamically added here -->
            </div>
        </div>
    </main>

    <!-- Floating Action Button -->
    <button class="btn btn-primary rounded-circle position-fixed shadow" 
            style="bottom: 2rem; right: 2rem; width: 56px; height: 56px; z-index: 1000;"
            onclick="window.location.href='editnote.php'">
        <i class="fa fa-plus"></i>
    </button>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Store notes in localStorage to persist data
        let notes = JSON.parse(localStorage.getItem('notes')) || [
            {
                id: 1,
                title: 'Sample Note 1',
                content: 'This is a sample note content...',
                status: 'incomplete',
                pinned: false,
                created: '2024-02-20'
            }
        ];

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize event listeners
            document.getElementById('searchNotes').addEventListener('input', searchNotes);

            // Initial render
            renderNotes();
        });

        // Render notes function
        function renderNotes(notesToRender = notes) {
            const notesGrid = document.getElementById('notesGrid');
            notesGrid.innerHTML = '';

            const sortedNotes = [...notesToRender].sort((a, b) => {
                if (a.pinned === b.pinned) return 0;
                return a.pinned ? -1 : 1;
            });

            sortedNotes.forEach(note => {
                // Format the date in a more readable way
                const date = new Date(note.created);
                const formattedDate = date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    year: 'numeric'
                });
                
                // Truncate content and remove HTML tags
                const truncatedContent = note.content.replace(/<[^>]*>/g, '').slice(0, 150) + '...';
                
                // Get status class and text
                const status = note.status || 'not_started';
                const statusClass = status.replace('_', '-');
                const statusText = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                const noteCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="note-card">
                            <h3 class="username">${note.title}</h3>
                            <div class="timestamp">
                                <i class="fa fa-clock-o me-1"></i>${formattedDate}
                            </div>
                            <p class="note-content">${truncatedContent}</p>
                            <div class="note-card-footer">
                                <div class="status-badge ${statusClass}">
                                    <i class="fa fa-circle"></i>${statusText}
                                </div>
                                <a href="editnote.php?id=${note.id}" class="edit-btn">
                                    Edit Note
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                notesGrid.innerHTML += noteCard;
            });
        }

        // Delete note
        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note?')) {
                notes = notes.filter(n => n.id !== noteId);
                localStorage.setItem('notes', JSON.stringify(notes));
                renderNotes();
            }
        }

        // Search notes
        function searchNotes(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredNotes = notes.filter(note => 
                note.title.toLowerCase().includes(searchTerm) || 
                note.content.toLowerCase().includes(searchTerm)
            );
            renderNotes(filteredNotes);
        }

        // Helper functions for status colors
        function getStatusBgColor(status) {
            const colors = {
                'incomplete': '#FFF1F0',
                'pending': '#FFF7E6',
                'completed': '#F0FFF4'
            };
            return colors[status] || '#F5F5F5';
        }

        function getStatusTextColor(status) {
            const colors = {
                'incomplete': '#CF1322',
                'pending': '#D46B08',
                'completed': '#237804'
            };
            return colors[status] || '#666666';
        }
    </script>
</body>
</html> 