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
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .note-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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
                    <button class="btn btn-primary rounded-pill px-4" id="addNoteBtn">
                        <i class="fa fa-plus me-2"></i>New Note
                    </button>
                </div>
            </div>

            <!-- Notes Grid -->
            <div class="row g-4" id="notesGrid">
                <!-- Notes will be dynamically added here -->
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <input type="text" 
                           class="form-control form-control-lg border-0" 
                           id="noteTitle" 
                           name="noteTitle" 
                           placeholder="Note Title">
                    <div class="ms-auto me-2">
                        <select class="form-select" id="noteStatus" name="noteStatus">
                            <option value="incomplete">Incomplete</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#textEditor">Text</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#drawingPad">Drawing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#handwriting">Handwriting</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="textEditor">
                            <div id="quillEditor" style="height: 300px;"></div>
                        </div>
                        <div class="tab-pane fade" id="drawingPad">
                            <canvas id="drawingCanvas" class="border" width="800" height="400"></canvas>
                            <div class="btn-group mt-2">
                                <button class="btn btn-outline-secondary" id="clearCanvas">Clear</button>
                                <input type="color" 
                                       class="form-control form-control-color" 
                                       id="colorPicker" 
                                       name="colorPicker" 
                                       value="#000000">
                                <input type="range" 
                                       class="form-range" 
                                       id="brushSize" 
                                       name="brushSize" 
                                       min="1" 
                                       max="20" 
                                       value="5">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="handwriting">
                            <canvas id="handwritingCanvas" class="border" width="800" height="400"></canvas>
                            <div class="btn-group mt-2">
                                <button class="btn btn-outline-secondary" id="clearHandwriting">Clear</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Export/Share
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" id="exportPDF">Export as PDF</a></li>
                            <li><a class="dropdown-item" href="#" id="shareEmail">Share via Email</a></li>
                            <li><a class="dropdown-item" href="#" id="shareTwitter">Share on Twitter</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4" id="saveNote">Save Note</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Quill Editor -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
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

        // Initialize Quill editor
        let quill = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill
            quill = new Quill('#quillEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['clean']
                    ]
                }
            });

            // Initialize event listeners
            document.getElementById('addNoteBtn').addEventListener('click', addNote);
            document.getElementById('floatingAddBtn').addEventListener('click', addNote);
            document.getElementById('saveNote').addEventListener('click', saveNote);
            document.getElementById('searchNotes').addEventListener('input', searchNotes);

            // Initial render
            renderNotes();
        });

        // Render notes function
        function renderNotes(notesToRender = notes) {
            const notesGrid = document.getElementById('notesGrid');
            notesGrid.innerHTML = '';

            // Sort notes (pinned first)
            const sortedNotes = [...notesToRender].sort((a, b) => {
                if (a.pinned === b.pinned) return 0;
                return a.pinned ? -1 : 1;
            });

            sortedNotes.forEach(note => {
                const noteCard = `
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card border-${getStatusColor(note.status)} h-100">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">${note.title}</h6>
                                <span class="badge bg-${getStatusColor(note.status)}">${note.status}</span>
                            </div>
                            <div class="card-body">
                                <div class="card-text small mb-2">${note.content}</div>
                                <small class="text-muted">Created: ${note.created}</small>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-end">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" 
                                        onclick="editNote(${note.id})">
                                    <i class="fa fa-pencil me-1"></i>Edit
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                notesGrid.innerHTML += noteCard;
            });
        }

        // Add new note
        function addNote() {
            const modal = new bootstrap.Modal(document.getElementById('noteModal'));
            document.getElementById('noteTitle').value = '';
            document.getElementById('noteStatus').value = 'incomplete';
            quill.setContents([]);
            modal.show();
        }

        // Save note
        function saveNote() {
            const title = document.getElementById('noteTitle').value;
            const status = document.getElementById('noteStatus').value;
            const content = quill.root.innerHTML;

            const newNote = {
                id: Date.now(), // Use timestamp as ID
                title: title || 'Untitled Note',
                content: content,
                status: status,
                pinned: false,
                created: new Date().toISOString().split('T')[0]
            };

            notes.push(newNote);
            localStorage.setItem('notes', JSON.stringify(notes));
            renderNotes();

            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('noteModal'));
            modal.hide();
        }

        // Edit note
        function editNote(noteId) {
            const note = notes.find(n => n.id === noteId);
            if (note) {
                const modal = new bootstrap.Modal(document.getElementById('noteModal'));
                
                // Set the note data in the modal
                document.getElementById('noteTitle').value = note.title;
                document.getElementById('noteStatus').value = note.status;
                quill.root.innerHTML = note.content;
                
                // Update save button to handle edit
                const saveButton = document.getElementById('saveNote');
                saveButton.onclick = () => updateNote(noteId);
                
                modal.show();
            }
        }

        // Update existing note
        function updateNote(noteId) {
            const noteIndex = notes.findIndex(n => n.id === noteId);
            if (noteIndex !== -1) {
                const updatedNote = {
                    ...notes[noteIndex],
                    title: document.getElementById('noteTitle').value,
                    content: quill.root.innerHTML,
                    status: document.getElementById('noteStatus').value
                };
                
                notes[noteIndex] = updatedNote;
                localStorage.setItem('notes', JSON.stringify(notes));
                renderNotes();
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('noteModal'));
                modal.hide();
                
                // Reset save button to normal save function
                document.getElementById('saveNote').onclick = saveNote;
            }
        }

        // Delete note
        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note?')) {
                notes = notes.filter(n => n.id !== noteId);
                localStorage.setItem('notes', JSON.stringify(notes));
                renderNotes();
            }
        }

        // Toggle pin
        function togglePin(noteId) {
            const note = notes.find(n => n.id === noteId);
            if (note) {
                note.pinned = !note.pinned;
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

        // Helper function for status colors
        function getStatusColor(status) {
            const colors = {
                'incomplete': 'danger',
                'pending': 'warning',
                'completed': 'success'
            };
            return colors[status] || 'secondary';
        }
    </script>
</body>
</html> 