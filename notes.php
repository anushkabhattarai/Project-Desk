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
        /* Remove existing status badge styles and replace with new toggle styles */
        .status-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: 32px;
            background: #f8f9fa;
            padding: 2px;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            position: relative;
        }

        .status-option {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            border: none;
            background: none;
            white-space: nowrap;
        }

        .status-option.active {
            color: white;
        }

        .status-slider {
            position: absolute;
            height: calc(100% - 4px);
            border-radius: 14px;
            transition: all 0.3s ease;
            left: 2px;
            top: 2px;
        }

        /* Status-specific colors */
        .status-slider.not-started {
            background: #666666;
            width: 85px;
        }

        .status-slider.pending {
            background: #D46B08;
            left: calc(85px + 4px);
            width: 70px;
        }

        .status-slider.completed {
            background: #237804;
            left: calc(155px + 6px);
            width: 80px;
        }

        /* Update note card styles for smaller size */
        .note-card {
            background: white;
            border-radius: 16px; /* Slightly reduced from 20px */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            padding: 1.25rem; /* Reduced from 1.75rem */
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.03);
            margin-bottom: 1rem; /* Reduced from 1.5rem */
            display: flex;
            flex-direction: column;
            height: 220px; /* Reduced from 280px */
            border-top: 4px solid transparent;
        }

        /* Adjust title size */
        .note-card .username {
            font-size: 1.1rem; /* Reduced from 1.25rem */
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
            height: 1.5rem; /* Reduced from 1.8rem */
        }

        /* Adjust timestamp size and spacing */
        .note-card .timestamp {
            font-size: 0.8rem; /* Reduced from 0.85rem */
            color: #94a3b8;
            margin: 0.25rem 0 0.5rem 0; /* Reduced from 0.5rem 0 1rem 0 */
            height: 1rem; /* Reduced from 1.2rem */
        }

        /* Adjust content area */
        .note-card .note-content {
            color: #4a5568;
            font-size: 0.9rem; /* Reduced from 0.95rem */
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 0.5rem; /* Reduced from 1rem */
            flex: 1;
            height: 5.4rem; /* Reduced from 6.4rem */
        }

        /* Adjust footer spacing */
        .note-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem; /* Reduced from 1rem */
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: auto;
        }

        /* Make the status select more compact */
        .status-select {
            padding: 0.35rem 2rem 0.35rem 0.75rem; /* Reduced padding */
            font-size: 0.85rem;
            border-radius: 8px; /* Reduced from 12px */
            border: 1px solid #e0e0e0;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px; /* Reduced from 140px */
            font-weight: 500;
        }

        /* Make the edit button more compact */
        .edit-btn {
            padding: 0.35rem 1rem; /* Reduced from 0.5rem 1.25rem */
            border-radius: 8px; /* Reduced from 12px */
            background: #3b82f6;
            color: white;
            font-size: 0.85rem; /* Reduced from 0.9rem */
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

        /* Progress tracking buttons styles */
        .progress-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .progress-btn {
            padding: 0.35rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            border: 1px solid #e0e0e0;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            opacity: 0.7;
        }

        .progress-btn.not-started {
            color: #666666;
            border-color: #d1d1d1;
        }

        .progress-btn.pending {
            color: #D46B08;
            border-color: #FFE4BA;
        }

        .progress-btn.completed {
            color: #237804;
            border-color: #D4F7DC;
        }

        .progress-btn.active {
            opacity: 1;
            font-weight: 500;
        }

        .progress-btn.not-started.active {
            background: #F5F5F5;
            border-color: #666666;
        }

        .progress-btn.pending.active {
            background: #FFF7E6;
            border-color: #D46B08;
        }

        .progress-btn.completed.active {
            background: #F0FFF4;
            border-color: #237804;
        }

        /* Add new status dropdown styles */
        .status-dropdown {
            position: relative;
            display: inline-block;
        }

        .status-dropdown select {
            appearance: none;
            padding: 0.4rem 2.5rem 0.4rem 1rem;
            font-size: 0.85rem;
            border-radius: 15px;
            border: 1px solid #e0e0e0;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 130px;
        }

        .status-dropdown::after {
            content: '\f107';
            font-family: 'FontAwesome';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }

        /* Status-specific styles */
        .status-not-started {
            color: #666666 !important;
            border-color: #d1d1d1 !important;
        }

        .status-pending {
            color: #D46B08 !important;
            border-color: #FFE4BA !important;
        }

        .status-completed {
            color: #237804 !important;
            border-color: #D4F7DC !important;
        }

        /* Status indicator styles */
        .status-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
            transition: background-color 0.3s ease;
        }

        .status-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        /* Status-specific styles */
        .status-not-started .status-indicator {
            background-color: #EF4444;
        }

        .status-pending .status-indicator {
            background-color: #F59E0B;
        }

        .status-completed .status-indicator {
            background-color: #10B981;
        }

        .status-label.not-started {
            color: #991B1B;
            background-color: #FEE2E2;
        }

        .status-label.pending {
            color: #92400E;
            background-color: #FEF3C7;
        }

        .status-label.completed {
            color: #065F46;
            background-color: #D1FAE5;
        }

        /* Update note card to handle new indicators */
        .note-card {
            position: relative;
            overflow: hidden;
        }

        /* Dropdown style updates */
        .status-dropdown select {
            padding: 0.4rem 2.5rem 0.4rem 1rem;
            font-weight: 500;
        }

        .status-dropdown select.not-started {
            background-color: #FEE2E2;
            color: #991B1B;
            border-color: #FCA5A5;
        }

        .status-dropdown select.pending {
            background-color: #FEF3C7;
            color: #92400E;
            border-color: #FCD34D;
        }

        .status-dropdown select.completed {
            background-color: #D1FAE5;
            color: #065F46;
            border-color: #6EE7B7;
        }

        /* Status indicator and card styles */
        .note-card {
            position: relative;
            border-top: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .note-card.not-started {
            border-top-color: #EF4444;
        }

        .note-card.pending {
            border-top-color: #F59E0B;
        }

        .note-card.completed {
            border-top-color: #10B981;
        }

        /* Status label styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .status-badge i {
            font-size: 0.9rem;
        }

        /* Status-specific badge styles */
        .status-badge.not-started {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-badge.pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-badge.completed {
            background-color: #D1FAE5;
            color: #065F46;
        }

        /* Status select wrapper with dropdown arrow */
        .status-select-wrapper {
            position: relative;
            display: inline-block;
        }

        .status-select-wrapper::after {
            content: '\f107';  /* FontAwesome dropdown arrow */
            font-family: 'FontAwesome';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            font-size: 1rem;
            color: currentColor;
        }

        /* Status select styles with updated red colors */
        .status-select {
            appearance: none;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
            font-weight: 500;
        }

        /* Updated red colors for Not Started state */
        .status-select.not-started {
            color: #dc2626 !important;             /* Brighter red text */
            border-color: #ef4444 !important;      /* Brighter red border */
            background-color: #fee2e2 !important;  /* Light red background */
        }

        .note-card.not-started {
            border-top-color: #dc2626 !important;  /* Brighter red top border */
        }

        /* Update the dropdown arrow color for not-started status */
        .status-select-wrapper:has(.status-select.not-started)::after {
            color: #dc2626 !important;
        }

        .status-select-wrapper:has(.status-select.pending)::after {
            color: #F59E0B;
        }

        .status-select-wrapper:has(.status-select.completed)::after {
            color: #10B981;
        }

        /* Add hover effect */
        .status-select:hover {
            border-color: currentColor;
            opacity: 0.9;
        }

        /* Remove default select arrow in various browsers */
        .status-select::-ms-expand {
            display: none;
        }

        .status-select:-moz-focusring {
            color: transparent;
            text-shadow: 0 0 0 #000;
        }

        /* Add or update these styles */
        .container-fluid {
            padding-top: 1rem !important; /* Reduced from default */
        }

        /* Adjust header area spacing */
        .d-flex.justify-content-between.align-items-center.mb-4 {
            margin-bottom: 2rem !important; /* Reduced from 4 */
            padding-top: 0 !important;
        }

        /* Make the breadcrumb more compact */
        .breadcrumb {
            margin-top: 0.25rem !important;
        }

        /* Update main content area padding */
        main {
            padding-top: 60px !important; /* Reduced from 70px */
        }
    </style>
</head>
<body class="bg-light">
    <?php include "inc/header.php"; ?>
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area -->
    <main style="margin-left: 250px; padding-top: 60px;">
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
                const date = new Date(note.created);
                const formattedDate = date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    year: 'numeric'
                });
                
                const truncatedContent = note.content.replace(/<[^>]*>/g, '').slice(0, 150) + '...';
                const status = note.status || 'not-started';
                
                const noteCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="note-card ${status}" data-note-id="${note.id}">
                            <h3 class="username">${note.title}</h3>
                            <div class="timestamp">
                                <i class="fa fa-clock-o me-1"></i>${formattedDate}
                            </div>
                            <p class="note-content">${truncatedContent}</p>
                            <div class="note-card-footer">
                                <div class="status-select-wrapper">
                                    <select class="status-select ${status}" 
                                            onchange="updateNoteStatus(${note.id}, this.value)">
                                        <option value="not-started" ${status === 'not-started' ? 'selected' : ''}>
                                            Not Started
                                        </option>
                                        <option value="pending" ${status === 'pending' ? 'selected' : ''}>
                                            Pending
                                        </option>
                                        <option value="completed" ${status === 'completed' ? 'selected' : ''}>
                                            Completed
                                        </option>
                                    </select>
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

        // Update the status update function for immediate visual feedback
        function updateNoteStatus(noteId, newStatus) {
            const notes = JSON.parse(localStorage.getItem('notes')) || [];
            const noteIndex = notes.findIndex(note => note.id === noteId);
            
            if (noteIndex !== -1) {
                // Update the status in the notes array
                notes[noteIndex].status = newStatus;
                localStorage.setItem('notes', JSON.stringify(notes));

                // Get the card element
                const card = document.querySelector(`[data-note-id="${noteId}"]`);
                if (card) {
                    // Remove all status classes
                    card.classList.remove('not-started', 'pending', 'completed');
                    // Add new status class
                    card.classList.add(newStatus);

                    // Update select styling
                    const select = card.querySelector('.status-select');
                    if (select) {
                        select.className = 'status-select ' + newStatus;
                    }

                    // Force status color update
                    const statusConfig = {
                        'not-started': {
                            color: '#dc2626',
                            text: 'Not Started'
                        },
                        'pending': {
                            color: '#F59E0B',
                            text: 'Pending'
                        },
                        'completed': {
                            color: '#10B981',
                            text: 'Completed'
                        }
                    };

                    // Ensure immediate color change
                    card.style.borderTopColor = statusConfig[newStatus].color;
                }
            }
        }
    </script>
</body>
</html> 