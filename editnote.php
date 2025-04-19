<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "task_management_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['id']; // Changed from user_id to id to match your session
$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Fetch note data if editing existing note
$note = null;
if ($note_id > 0) {
    $stmt = $conn->prepare("SELECT n.*, u.full_name as author_name 
                           FROM notes n 
                           JOIN users u ON n.user_id = u.id 
                           WHERE n.id = ? AND (n.user_id = ? OR 
                           EXISTS (SELECT 1 FROM note_shares WHERE note_id = n.id AND shared_with = ?))");
    $stmt->bind_param("iii", $note_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    
    if (!$note) {
        header('Location: notes.php');
        exit();
    }
}

// Handle note save/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $title = trim($_POST['title']);
    $content = $_POST['content']; // Get content directly from POST
    $status = $_POST['status'];
    $pinned = isset($_POST['pinned']) ? 1 : 0;
    
    if (empty($title)) {
        $error_message = "Title is required";
    } else {
        if ($note_id > 0) {
            // Update existing note
            $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, status = ?, pinned = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssiii", $title, $content, $status, $pinned, $note_id, $user_id);
        } else {
            // Create new note
            $stmt = $conn->prepare("INSERT INTO notes (title, content, user_id, status, pinned) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisi", $title, $content, $user_id, $status, $pinned);
        }
        
        if ($stmt->execute()) {
            $success_message = "Note saved successfully!";
            if (!$note_id) {
                $note_id = $conn->insert_id;
                header("Location: editnote.php?id=" . $note_id);
                exit();
            }
        } else {
            $error_message = "Error saving note: " . $conn->error;
        }
    }
}

// Handle note sharing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_note'])) {
    $share_with = (int)$_POST['share_with'];
    $can_edit = isset($_POST['can_edit']) ? 1 : 0;
    
    // Check if share already exists
    $stmt = $conn->prepare("SELECT id FROM note_shares WHERE note_id = ? AND shared_with = ?");
    $stmt->bind_param("ii", $note_id, $share_with);
    $stmt->execute();
    $existing_share = $stmt->get_result()->fetch_assoc();
    
    if (!$existing_share) {
        $stmt = $conn->prepare("INSERT INTO note_shares (note_id, shared_by, shared_with, can_edit) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $note_id, $user_id, $share_with, $can_edit);
        
        if ($stmt->execute()) {
            // Create notification
            $note_title = $note['title'];
            $message = "A note titled '$note_title' has been shared with you.";
            $stmt = $conn->prepare("INSERT INTO notifications (message, recipient, type, date) VALUES (?, ?, 'Note Shared', CURRENT_DATE)");
            $stmt->bind_param("si", $message, $share_with);
            $stmt->execute();
            $success_message = "Note shared successfully!";
        } else {
            $error_message = "Error sharing note";
        }
    } else {
        $error_message = "Note is already shared with this user";
    }
}

// Fetch users for sharing
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $note_id ? 'Edit Note' : 'New Note'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --border-color: #e0e0e0;
            --bg-light: #ffffff;
            --text-dark: #2d3436;
            --text-muted: #636e72;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.2s ease;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--text-dark);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .note-container {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: white;
            box-shadow: none;
            border-radius: 0;
        }

        .top-bar {
            position: sticky;
            top: 0;
            z-index: 101;
            background: white;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
            height: 64px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .back-button {
            color: var(--text-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            background: transparent;
            transition: var(--transition);
        }

        .back-button:hover {
            background: #f1f3f4;
            color: var(--primary-color);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-left: auto;
        }

        .header-button {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .header-button:hover {
            background: #f1f3f4;
            border-color: var(--text-muted);
        }

        .header-button i {
            font-size: 1rem;
        }

        .form-select {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: var(--transition);
            background-color: white;
            cursor: pointer;
            min-width: 140px;
        }

        .form-select:hover {
            border-color: var(--text-muted);
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(108, 92, 231, 0.1);
            outline: none;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            min-height: calc(100vh - 120px); /* Adjusted for header + toolbar */
        }

        .editor-section {
            padding: 2rem;
            background: white;
        }

        .note-header {
            margin-bottom: 2rem;
        }

        .note-header input[type="text"] {
            font-size: 2rem;
            font-weight: 600;
            border: none;
            width: 100%;
            padding: 0.5rem 0;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            background: transparent;
        }

        .note-header input[type="text"]:focus {
            outline: none;
            border-bottom: 2px solid var(--primary-color);
        }

        .note-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .editor-toolbar {
            position: sticky;
            top: 64px;
            z-index: 100;
            background: white;
            padding: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 0 4px;
        }

        .toolbar-separator {
            width: 1px;
            height: 24px;
            background: var(--border-color);
            margin: 0 4px;
        }

        .toolbar-button {
            width: 34px;
            height: 34px;
            border-radius: 4px;
            border: none;
            background: transparent;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .toolbar-button:hover {
            background: var(--secondary-color);
            color: white;
        }

        .toolbar-button.active {
            background: var(--primary-color);
            color: white;
        }

        .editor-content {
            min-height: 500px;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .editor-content:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .comments-section {
            background: #f8f9fa;
            border-left: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .comments-header {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .comments-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .badge {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }

        .comment {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .comment-author {
            font-weight: 500;
            color: var(--primary-color);
        }

        .comment-time {
            color: var(--text-muted);
        }

        .comment-form textarea {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            resize: none;
            font-size: 0.9rem;
        }

        .comment-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: #5f51e5;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-outline-secondary:hover {
            background: #f8f9fa;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1050;
        }

        .toast {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: none;
            margin-top: 0.5rem;
        }

        .save-bar {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 1rem 2rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            z-index: 100;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .comments-section {
                border-left: none;
                border-top: 1px solid var(--border-color);
            }
        }

        /* Update button active state styling */
        .btn-light.active {
            background-color: #e8f0fe !important;
            color: #1a73e8 !important;
            border-color: #e8f0fe !important;
        }

        .editor-content {
            padding: 2rem;
            min-height: 500px;
            outline: none;
            font-size: 1rem;
            line-height: 1.6;
        }

        .editor-content:focus {
            outline: none;
        }

        .editor-content img {
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
        }

        /* Placeholder styling */
        .editor-content:empty:before {
            content: attr(data-placeholder);
            color: #6c757d;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="container-fluid note-container">
        <div class="bg-white border-bottom sticky-top">
            <div class="px-3 py-2 border-bottom bg-light">
                <div class="btn-toolbar" role="toolbar" aria-label="Text formatting toolbar">
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-light" data-command="undo" title="Undo">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        <button type="button" class="btn btn-light" data-command="redo" title="Redo">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-light" data-command="bold" title="Bold">
                            <i class="bi bi-type-bold"></i>
                        </button>
                        <button type="button" class="btn btn-light" data-command="italic" title="Italic">
                            <i class="bi bi-type-italic"></i>
                        </button>
                        <button type="button" class="btn btn-light" data-command="underline" title="Underline">
                            <i class="bi bi-type-underline"></i>
                        </button>
                    </div>

                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-light" data-command="justifyLeft" title="Align left">
                            <i class="bi bi-text-left"></i>
                        </button>
                        <button type="button" class="btn btn-light" data-command="justifyCenter" title="Align center">
                            <i class="bi bi-text-center"></i>
                        </button>
                        <button type="button" class="btn btn-light" data-command="justifyRight" title="Align right">
                            <i class="bi bi-text-right"></i>
                        </button>
                    </div>

                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-light" data-command="insertUnorderedList" title="Bullet list">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <button type="button" class="btn btn-light" data-command="insertOrderedList" title="Numbered list">
                            <i class="bi bi-list-ol"></i>
                        </button>
                    </div>

                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light" data-command="insertImage" title="Add image">
                            <i class="bi bi-image"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-3 py-2 d-flex align-items-center">
                <a href="notes.php" class="btn btn-light me-3">
                    <i class="bi bi-arrow-left"></i>
                    Back to Notes
                </a>
                <div class="d-flex align-items-center flex-grow-1">
                    <input type="text" class="form-control form-control-lg border-0 shadow-none" 
                           id="title" name="title" placeholder="Untitled document"
                           value="<?php echo isset($note) ? htmlspecialchars($note['title']) : ''; ?>">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select" id="status" name="status">
                        <option value="not-started" <?php echo (isset($note) && $note['status'] == 'not-started') ? 'selected' : ''; ?>>Not Started</option>
                        <option value="pending" <?php echo (isset($note) && $note['status'] == 'pending') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo (isset($note) && $note['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <button type="button" class="btn btn-primary" id="saveButton">
                        <i class="bi bi-save"></i>
                        Save
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="bi bi-share-fill"></i>
                        Share
                    </button>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="editor-section">
                <div class="editor-content" id="editor" contenteditable="true" 
                     data-placeholder="Start writing your note..."><?php 
                    echo isset($note) ? htmlspecialchars_decode($note['content']) : ''; 
                ?></div>
                
                <input type="hidden" id="content" name="content">
            </div>

            <div class="comments-section">
                <div class="comments-header">
                    <h5>Comments</h5>
                    <span class="badge" id="comment-count">0</span>
                </div>
                
                <div class="comments-content" id="comments-container">
                    <!-- Comments will be dynamically added here -->
                </div>

                <div class="comment-form">
                    <textarea id="comment-text" rows="3" placeholder="Write a comment..."></textarea>
                    <button type="button" class="btn btn-primary w-100" id="add-comment">
                        Comment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-share-fill"></i>
                        Share Note
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="shareForm">
                        <div class="mb-3">
                            <label class="form-label">Share with</label>
                            <select name="share_with" class="form-select" required>
                                <option value="">Select user</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="can_edit" name="can_edit">
                                <label class="form-check-label" for="can_edit">Allow editing</label>
                            </div>
                        </div>
                        <button type="submit" name="share_note" class="btn btn-primary w-100">
                            Share Note
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="noteForm" method="POST" style="display: none;">
        <input type="hidden" name="save_note" value="1">
        <input type="hidden" name="title" id="formTitle">
        <input type="hidden" name="content" id="formContent">
        <input type="hidden" name="status" id="formStatus">
        <input type="hidden" name="pinned" id="formPinned">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let isSaving = false;  // Add flag to track save action

        // Add confirmation before leaving
        window.addEventListener('beforeunload', function(e) {
            if (isSaving) {
                return;  // Don't show warning if we're saving
            }
            
            const editor = document.getElementById('editor');
            const title = document.getElementById('title');
            
            // Check if there are unsaved changes
            if (editor.getAttribute('data-original-content') !== editor.innerHTML ||
                title.getAttribute('data-original-value') !== title.value) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Store original content for change detection
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            const title = document.getElementById('title');
            
            editor.setAttribute('data-original-content', editor.innerHTML);
            title.setAttribute('data-original-value', title.value);

            // Add save button click handler
            document.getElementById('saveButton').addEventListener('click', function() {
                isSaving = true;  // Set flag before saving
                
                // Get values from the editor and form
                const title = document.getElementById('title').value;
                const content = document.getElementById('editor').innerHTML;
                const status = document.getElementById('status').value;
                const pinned = document.getElementById('pinned')?.checked ? 1 : 0;
                
                // Set values in the hidden form
                document.getElementById('formTitle').value = title;
                document.getElementById('formContent').value = content;
                document.getElementById('formStatus').value = status;
                document.getElementById('formPinned').value = pinned;
                
                // Submit the form
                document.getElementById('noteForm').submit();
            });
        });

        // Custom Editor Implementation
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            const contentInput = document.getElementById('content');
            const toolbar = document.querySelector('.btn-toolbar');

            // Initialize editor content for form submission
            document.querySelector('form').addEventListener('submit', function() {
                contentInput.value = editor.innerHTML;
            });

            // Handle toolbar button clicks
            toolbar.addEventListener('click', function(e) {
                const button = e.target.closest('.btn');
                if (!button) return;

                e.preventDefault();
                const command = button.dataset.command;

                // Handle different commands
                switch(command) {
                    case 'insertImage':
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/*';
                        input.onchange = function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    document.execCommand('insertImage', false, e.target.result);
                                };
                                reader.readAsDataURL(file);
                            }
                        };
                        input.click();
                        break;

                    default:
                        document.execCommand(command, false, null);
                        break;
                }

                // Update button states
                updateToolbarState();
            });

            // Update toolbar state based on current selection
            function updateToolbarState() {
                toolbar.querySelectorAll('.btn[data-command]').forEach(button => {
                    const command = button.dataset.command;
                    
                    // Skip certain commands that don't have states
                    if (['insertImage', 'undo', 'redo'].includes(command)) {
                        return;
                    }

                    // Check if the command is active
                    if (document.queryCommandState(command)) {
                        button.classList.add('active');
                    } else {
                        button.classList.remove('active');
                    }
                });
            }

            // Update toolbar state when selection changes
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            editor.addEventListener('input', updateToolbarState);

            // Handle paste to strip formatting
            editor.addEventListener('paste', function(e) {
                e.preventDefault();
                const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            });

            // Initialize toolbar state
            updateToolbarState();

            // Add undo/redo keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    if (e.key === 'z' && !e.shiftKey) {
                        e.preventDefault();
                        document.execCommand('undo', false, null);
                    } else if ((e.key === 'z' && e.shiftKey) || e.key === 'y') {
                        e.preventDefault();
                        document.execCommand('redo', false, null);
                    }
                }
            });

            // Make editor focused when clicking anywhere in the content area
            editor.addEventListener('click', function(e) {
                if (e.target === editor) {
                    const range = document.createRange();
                    const sel = window.getSelection();
                    range.setStart(editor, 0);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });

            // Add visual feedback for active buttons
            const buttons = toolbar.querySelectorAll('.btn-light');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const command = this.dataset.command;
                    if (!['undo', 'redo', 'insertImage'].includes(command)) {
                        this.classList.toggle('active');
                    }
                });
            });

            // Handle keyboard shortcuts for common formatting
            editor.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key.toLowerCase()) {
                        case 'b':
                            e.preventDefault();
                            document.execCommand('bold', false, null);
                            updateToolbarState();
                            break;
                        case 'i':
                            e.preventDefault();
                            document.execCommand('italic', false, null);
                            updateToolbarState();
                            break;
                        case 'u':
                            e.preventDefault();
                            document.execCommand('underline', false, null);
                            updateToolbarState();
                            break;
                    }
                }
            });

            // Save selection state before blur
            let savedSelection = null;
            editor.addEventListener('blur', function() {
                savedSelection = saveSelection();
            });

            editor.addEventListener('focus', function() {
                if (savedSelection) {
                    restoreSelection(savedSelection);
                    savedSelection = null;
                }
            });

            // Helper functions for selection management
            function saveSelection() {
                if (window.getSelection) {
                    const sel = window.getSelection();
                    if (sel.getRangeAt && sel.rangeCount) {
                        return sel.getRangeAt(0);
                    }
                }
                return null;
            }

            function restoreSelection(range) {
                if (range) {
                    if (window.getSelection) {
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                }
            }
        });

        // Client-side comment handling
        const comments = [];
        
        document.getElementById('add-comment').addEventListener('click', function(e) {
            e.preventDefault();
            const commentText = document.getElementById('comment-text').value.trim();
            if (commentText) {
                const comment = {
                    text: commentText,
                    author: '<?php echo $_SESSION['username'] ?? "User"; ?>',
                    timestamp: new Date().toLocaleString()
                };
                comments.push(comment);
                document.getElementById('comment-text').value = '';
                displayComments();
                updateCommentCount();
            }
        });

        function displayComments() {
            const container = document.getElementById('comments-container');
            container.innerHTML = comments.map(comment => `
                <div class="comment">
                    <div class="comment-header">
                        <span class="comment-author">${comment.author}</span>
                        <span class="comment-time">${comment.timestamp}</span>
                    </div>
                    <div class="comment-text">${comment.text}</div>
                </div>
            `).join('');
        }

        function updateCommentCount() {
            document.getElementById('comment-count').textContent = comments.length;
        }

        // Toast notification function
        function showToast(title, message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 3000
            });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Handle share form submission
        document.getElementById('shareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const select = this.querySelector('select');
            const userName = select.options[select.selectedIndex].text;
            showToast('Shared', `Note shared with ${userName}`);
            bootstrap.Modal.getInstance(document.getElementById('shareModal')).hide();
        });
    </script>
</body>
</html>
