<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Include necessary files
include "DB_connection.php";
$title = "Edit Note";

// Get note ID from URL
$noteId = isset($_GET['id']) ? $_GET['id'] : null;
$note = null;

// Get note content from localStorage via JavaScript
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
    <style>
        :root[data-theme="light"] {
            --bg-color: #ffffff;
            --text-color: #1a1a1a;
            --border-color: #e0e0e0;
            --toolbar-bg: #f8f9fa;
            --button-hover: #e9ecef;
            --button-active: #dee2e6;
            --breadcrumb-color: #666;
            --link-color: #0066cc;
        }

        :root[data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --border-color: #333;
            --toolbar-bg: #1a1a1a;
            --button-hover: #333;
            --button-active: #444;
            --breadcrumb-color: #999;
            --link-color: #66b3ff;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }
        
        .editor-container {
            min-height: 100vh;
            padding: 2rem;
        }

        .editor-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 1rem 2rem;
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
            z-index: 1000;
        }

        .editor-toolbar {
            background-color: var(--toolbar-bg);
            border: none;
            padding: 0.75rem 0;
            margin-top: 0;
            border-bottom: 1px solid var(--border-color);
        }

        .editor-toolbar button {
            background: none;
            border: none;
            color: var(--text-color);
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .editor-toolbar button:hover {
            background-color: var(--button-hover);
        }

        .editor-toolbar button.active {
            background-color: var(--button-active);
        }

        .editor-content {
            margin-top: 0.5rem;
            padding: 0 2rem;
        }

        .editor-content [contenteditable="true"] {
            outline: none;
            padding: 1rem 0;
            color: var(--text-color) !important;
        }

        .action-buttons {
            position: fixed;
            top: 1rem;
            right: 2rem;
            display: flex;
            gap: 1rem;
        }

        .action-button {
            background-color: #333;
            color: #fff;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .action-button:hover {
            background-color: #444;
            color: #fff;
        }

        .action-button.primary {
            background-color: #7c3aed;
        }

        .action-button.primary:hover {
            background-color: #6d28d9;
        }

        .breadcrumb {
            margin: 0;
            padding: 0;
            background: none;
        }

        .breadcrumb-item a {
            color: var(--breadcrumb-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--text-color);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: #666;
        }

        #editor {
            background-color: var(--bg-color);
            color: var(--text-color) !important;
            border: none;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .ql-toolbar {
            border: none !important;
            border-bottom: 1px solid #333 !important;
        }

        .ql-container {
            border: none !important;
        }

        .ql-editor {
            padding: 2rem 0;
            color: var(--text-color) !important;
        }

        .ql-snow .ql-stroke {
            stroke: var(--text-color);
        }

        .ql-snow .ql-fill {
            fill: var(--text-color);
        }

        .ql-snow .ql-picker {
            color: var(--text-color);
        }

        .ql-snow .ql-picker-options {
            background-color: var(--bg-color);
        }

        .ql-snow .ql-tooltip {
            background-color: var(--bg-color);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .ql-snow .ql-tooltip input[type=text] {
            color: var(--text-color);
            background-color: var(--bg-color);
            border-color: var(--border-color);
        }

        .save-indicator {
            position: fixed;
            bottom: 6rem;
            right: 2rem;
            background-color: #333;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: none;
        }
        
        .save-indicator.show {
            display: block;
            animation: fadeOut 2s forwards;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }

        .theme-toggle {
            background-color: var(--bg-color);
            color: var(--text-color);
            border-color: var(--border-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background-color: var(--button-hover);
        }

        .image-upload-btn {
            position: relative;
            overflow: hidden;
        }

        .image-upload-btn input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Ensure proper text color in the editor content */
        .ql-editor p, .ql-editor h1, .ql-editor h2, .ql-editor h3, 
        .ql-editor h4, .ql-editor h5, .ql-editor h6, 
        .ql-editor ol, .ql-editor ul, .ql-editor pre, 
        .ql-editor blockquote {
            color: var(--text-color) !important;
        }

        /* Update action buttons for better contrast in light mode */
        [data-theme="light"] .action-button {
            background-color: #f0f0f0;
            color: #333;
        }

        [data-theme="light"] .action-button:hover {
            background-color: #e0e0e0;
            color: #1a1a1a;
        }

        [data-theme="light"] .action-button.primary {
            background-color: #7c3aed;
            color: #ffffff;
        }

        [data-theme="light"] .action-button.primary:hover {
            background-color: #6d28d9;
        }

        /* Update title container styles */
        .note-title-container {
            margin-top: 4.5rem;  /* Reduced from 5rem */
            margin-bottom: 0;    /* Removed bottom margin */
            padding: 0 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .note-title-input {
            width: 100%;
            background: transparent;
            border: none;
            font-size: 2rem;     /* Increased from 1.5rem */
            font-weight: 600;    /* Made slightly bolder */
            color: var(--text-color);
            padding: 0.5rem 0;   /* Adjusted padding */
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .note-title-input::placeholder {
            color: var(--text-color);
            opacity: 0.5;
        }

        .note-title-input:hover {
            opacity: 0.9;
        }

        .note-title-input:focus {
            outline: none;
            opacity: 1;
        }

        /* Add save button styles */
        .save-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background-color: #7c3aed;
            color: #ffffff;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .save-button:hover {
            background-color: #6d28d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .save-button i {
            font-size: 1.1rem;
        }

        [data-theme="light"] .save-button {
            background-color: #7c3aed;
            color: #ffffff;
        }

        [data-theme="light"] .save-button:hover {
            background-color: #6d28d9;
        }

        /* Add success notification styles */
        .success-notification {
            position: fixed;
            top: 1rem;
            right: 50%;
            transform: translateX(50%);
            background-color: #10B981;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 20px;
            display: none;
            align-items: center;
            gap: 0.5rem;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease-out;
        }

        .success-notification.show {
            display: flex;
        }

        @keyframes slideDown {
            from {
                transform: translate(50%, -100%);
                opacity: 0;
            }
            to {
                transform: translate(50%, 0);
                opacity: 1;
            }
        }

        [data-theme="light"] .success-notification {
            background-color: #059669;
            color: white;
        }
    </style>
</head>
<body>
    <div class="editor-container">
        <!-- Editor Header -->
        <div class="editor-header">
            <div class="d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="notes.php">Notes</a></li>
                        <li class="breadcrumb-item active" id="noteTitle">Loading...</li>
                    </ol>
                </nav>
                
                <div class="d-flex gap-3 align-items-center">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <i class="fa fa-moon-o me-2"></i>
                        <span id="themeText">Dark Mode</span>
                    </button>
                    
                    <div class="action-buttons">
                        <button class="action-button" onclick="printNote()">
                            <i class="fa fa-print"></i>
                            Print
                        </button>
                        <button class="action-button" onclick="shareNote()">
                            <i class="fa fa-share-alt"></i>
                            Share
                        </button>
                        <button class="action-button primary" onclick="downloadNote()">
                            <i class="fa fa-download"></i>
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Note Title Input with smaller size -->
        <div class="note-title-container">
            <input type="text" 
                   class="note-title-input" 
                   id="noteTitleInput" 
                   placeholder="Untitled note"
                   autocomplete="off">
        </div>

        <!-- Editor Toolbar -->
        <div class="editor-toolbar">
            <div class="d-flex gap-2">
                <div class="btn-group">
                    <button onclick="formatText('bold')" class="format-btn" data-format="bold" title="Bold">
                        <i class="fa fa-bold"></i>
                    </button>
                    <button onclick="formatText('italic')" class="format-btn" data-format="italic" title="Italic">
                        <i class="fa fa-italic"></i>
                    </button>
                    <button onclick="formatText('underline')" class="format-btn" data-format="underline" title="Underline">
                        <i class="fa fa-underline"></i>
                    </button>
                </div>

                <div class="btn-group">
                    <button onclick="formatText('header', 1)" class="format-btn" data-format="header" title="Heading 1">
                        <i class="fa fa-header"></i>1
                    </button>
                    <button onclick="formatText('header', 2)" class="format-btn" data-format="header" title="Heading 2">
                        <i class="fa fa-header"></i>2
                    </button>
                </div>

                <div class="btn-group">
                    <button onclick="formatList('bullet')" class="format-btn" data-format="list" title="Bullet List">
                        <i class="fa fa-list-ul"></i>
                    </button>
                    <button onclick="formatList('ordered')" class="format-btn" data-format="list" title="Numbered List">
                        <i class="fa fa-list-ol"></i>
                    </button>
                </div>

                <button class="image-upload-btn" title="Insert Image">
                    <i class="fa fa-image"></i>
                    <input type="file" accept="image/*" onchange="insertImage(this)">
                </button>
            </div>
        </div>

        <!-- Editor Content -->
        <div class="editor-content">
            <div id="editor"></div>
        </div>

        <!-- Add Save Button -->
        <button class="save-button" onclick="saveNoteManually()">
            <i class="fa fa-save"></i>
            Save
        </button>

        <!-- Save Indicator -->
        <div class="save-indicator" id="saveIndicator">Changes saved</div>

        <!-- Add this right after the editor-container div opening -->
        <div class="success-notification" id="successNotification">
            <i class="fa fa-check-circle"></i>
            Note saved successfully!
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Quill Editor -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        // Initialize variables
        let currentNote = null;
        const noteId = <?php echo $noteId ? $noteId : 'null' ?>;
        
        // Initialize Quill editor with image handling
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: false,
                clipboard: {
                    matchVisual: false
                }
            },
            formats: ['bold', 'italic', 'underline', 'strike', 'header', 'list', 'link', 'image']
        });

        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const themeText = document.getElementById('themeText');
            const themeIcon = document.querySelector('.theme-toggle i');
            
            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light');
                themeText.textContent = 'Light Mode';
                themeIcon.className = 'fa fa-sun-o me-2';
            } else {
                html.setAttribute('data-theme', 'dark');
                themeText.textContent = 'Dark Mode';
                themeIcon.className = 'fa fa-moon-o me-2';
            }
        }

        // Image insertion
        function insertImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Enhanced format text function
        function formatText(format, value = null) {
            const range = quill.getSelection();
            if (range) {
                if (value !== null) {
                    quill.format(format, value);
                } else {
                    const currentFormat = quill.getFormat(range);
                    quill.format(format, !currentFormat[format]);
                }
                updateToolbarState();
            }
        }

        // Load note content when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            const themeText = document.getElementById('themeText');
            const themeIcon = document.querySelector('.theme-toggle i');
            
            if (savedTheme === 'light') {
                themeText.textContent = 'Light Mode';
                themeIcon.className = 'fa fa-sun-o me-2';
            }

            if (noteId) {
                loadNote(noteId);
            } else {
                createNewNote();
            }
        });

        // Load note content
        function loadNote(id) {
            const notes = JSON.parse(localStorage.getItem('notes')) || [];
            currentNote = notes.find(note => note.id === id);
            
            if (currentNote) {
                // Set note title in breadcrumb and title input
                document.getElementById('noteTitle').textContent = currentNote.title;
                document.getElementById('noteTitleInput').value = currentNote.title;
                
                // Set editor content
                quill.root.innerHTML = currentNote.content;
            }
        }

        // Auto-save functionality
        let saveTimeout;
        quill.on('text-change', function() {
            if (!currentNote) return;
            
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveNote, 1000);
        });

        // Save note
        function saveNote() {
            if (!currentNote) return;

            const notes = JSON.parse(localStorage.getItem('notes')) || [];
            const noteIndex = notes.findIndex(note => note.id === currentNote.id);
            
            if (noteIndex !== -1) {
                notes[noteIndex].content = quill.root.innerHTML;
                localStorage.setItem('notes', JSON.stringify(notes));
                
                // Show success notification
                const notification = document.getElementById('successNotification');
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 2000);
            }
        }

        // Format text functions
        function formatList(type) {
            const currentList = quill.getFormat().list;
            quill.format('list', currentList === type ? false : type);
            updateToolbarState();
        }

        // Update toolbar state based on current format
        function updateToolbarState() {
            const format = quill.getFormat();
            document.querySelectorAll('.format-btn').forEach(btn => {
                const formatType = btn.dataset.format;
                if (format[formatType]) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        // Action button functions
        function printNote() {
            window.print();
        }

        function shareNote() {
            // Implement share functionality
            alert('Share functionality coming soon!');
        }

        function downloadNote() {
            const content = quill.root.innerHTML;
            const blob = new Blob([content], { type: 'text/html' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${currentNote?.title || 'note'}.html`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Selection change handler
        quill.on('selection-change', function() {
            updateToolbarState();
        });

        // Save theme preference
        function saveThemePreference(theme) {
            localStorage.setItem('theme', theme);
        }

        // Auto-save title changes
        let titleSaveTimeout;
        document.getElementById('noteTitleInput').addEventListener('input', function(e) {
            if (!currentNote) return;
            
            clearTimeout(titleSaveTimeout);
            titleSaveTimeout = setTimeout(() => {
                saveNoteTitle(e.target.value);
            }, 1000);
        });

        // Save note title
        function saveNoteTitle(newTitle) {
            if (!currentNote) return;

            const notes = JSON.parse(localStorage.getItem('notes')) || [];
            const noteIndex = notes.findIndex(note => note.id === currentNote.id);
            
            if (noteIndex !== -1) {
                notes[noteIndex].title = newTitle;
                localStorage.setItem('notes', JSON.stringify(notes));
                
                // Update breadcrumb
                document.getElementById('noteTitle').textContent = newTitle;
                
                // Show success notification
                const notification = document.getElementById('successNotification');
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 2000);
            }
        }

        // Create new note if no ID provided
        function createNewNote() {
            const newNote = {
                id: Date.now(),
                title: 'Untitled note',
                content: '',
                created: new Date().toISOString().split('T')[0]
            };
            
            const notes = JSON.parse(localStorage.getItem('notes')) || [];
            notes.push(newNote);
            localStorage.setItem('notes', JSON.stringify(notes));
            
            currentNote = newNote;
            
            // Set initial values
            document.getElementById('noteTitle').textContent = newNote.title;
            document.getElementById('noteTitleInput').value = newNote.title;
            quill.root.innerHTML = '';
        }

        // Add manual save function
        function saveNoteManually() {
            if (!currentNote) return;

            const notes = JSON.parse(localStorage.getItem('notes')) || [];
            const noteIndex = notes.findIndex(note => note.id === currentNote.id);
            
            if (noteIndex !== -1) {
                // Save both title and content
                notes[noteIndex].title = document.getElementById('noteTitleInput').value;
                notes[noteIndex].content = quill.root.innerHTML;
                localStorage.setItem('notes', JSON.stringify(notes));
                
                // Update breadcrumb
                document.getElementById('noteTitle').textContent = notes[noteIndex].title;
                
                // Show success notification
                const notification = document.getElementById('successNotification');
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 2000);
            }
        }

        // Add keyboard shortcut for save
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault(); // Prevent browser save dialog
                saveNoteManually();
            }
        });
    </script>
</body>
</html>