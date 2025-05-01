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

// Fetch comments for the note
$comments = [];
$comment_count = 0;
if ($note_id > 0) {
    $stmt = $conn->prepare("SELECT c.*, u.full_name as author_name 
                           FROM note_comments c 
                           JOIN users u ON c.user_id = u.id 
                           WHERE c.note_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $comment_count = count($comments);
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
                
                // If the note was just created, get the current user's name for notifications
                $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $creator = $result->fetch_assoc();
                $creator_name = $creator['full_name'];
                
                header("Location: editnote.php?id=" . $note_id);
                exit();
            } else {
                // For existing notes, notify shared users about the update
                $stmt = $conn->prepare("SELECT shared_with FROM note_shares WHERE note_id = ?");
                $stmt->bind_param("i", $note_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $shared_user_id = $row['shared_with'];
                    
                    // Create notification for the update
                    $message = "Note '$title' has been updated";
                    $stmt2 = $conn->prepare("INSERT INTO notifications (message, recipient, type, date) VALUES (?, ?, 'Note Updated', CURRENT_DATE)");
                    $stmt2->bind_param("si", $message, $shared_user_id);
                    $stmt2->execute();
                }
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

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment_text = trim($_POST['comment_text']);
    if (!empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO note_comments (note_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $note_id, $user_id, $comment_text);
        if ($stmt->execute()) {
            // Fetch the newly added comment with user details
            $stmt = $conn->prepare("SELECT c.*, u.full_name as author_name 
                                  FROM note_comments c 
                                  JOIN users u ON c.user_id = u.id 
                                  WHERE c.id = LAST_INSERT_ID()");
            $stmt->execute();
            $new_comment = $stmt->get_result()->fetch_assoc();
            
            // Return the new comment as JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'comment' => $new_comment,
                'total_comments' => $comment_count + 1
            ]);
            exit;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
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
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #6b7280;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
        }

        /* Top Navigation Bar */
        .top-nav {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }

        .nav-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .back-button {
            color: var(--text-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-button:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }

        .note-title-input {
            flex: 1;
            font-size: 1.25rem;
            font-weight: 500;
            border: none;
            padding: 0.5rem;
            margin: 0 1rem;
            border-radius: 0.375rem;
            transition: var(--transition);
        }

        .note-title-input:focus {
            outline: none;
            background: var(--bg-light);
        }

        /* Main Content Layout */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Editor Section */
        .editor-section {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .editor-toolbar {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-light);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .toolbar-group {
            display: flex;
            gap: 0.25rem;
            padding: 0.25rem;
            background: white;
            border-radius: 0.375rem;
        }

        .toolbar-button {
            padding: 0.5rem;
            border: none;
            background: transparent;
            border-radius: 0.25rem;
            color: var(--text-dark);
            cursor: pointer;
            transition: var(--transition);
        }

        .toolbar-button:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }

        .toolbar-button.active {
            background: var(--primary-color);
            color: white;
        }

        .editor-content {
            padding: 2rem;
            min-height: calc(100vh - 300px);
            outline: none;
        }

        /* Comments Section */
        .comments-section {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            height: fit-content;
            position: sticky;
            top: 5rem;
        }

        .comments-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .comments-header h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .comment-count {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .comments-list {
            max-height: calc(100vh - 400px);
            overflow-y: auto;
            padding: 1.25rem;
        }

        .comment {
            padding: 1rem;
            border-radius: 0.5rem;
            background: var(--bg-light);
            margin-bottom: 1rem;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .comment-author {
            font-weight: 500;
            color: var(--primary-color);
        }

        .comment-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .comment-text {
            font-size: 0.875rem;
            color: var(--text-dark);
        }

        .comment-form {
            padding: 1.25rem;
            border-top: 1px solid var(--border-color);
        }

        .comment-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            resize: none;
            transition: var(--transition);
        }

        .comment-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .status-select {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-dark);
            background-color: white;
            min-width: 140px;
            transition: var(--transition);
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: var(--shadow-md);
        }

        .modal-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-body {
            padding: 1.25rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .comments-section {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                align-items: stretch;
            }

            .action-buttons {
                flex-wrap: wrap;
            }

            .status-select {
                width: 100%;
            }
        }

        /* Toast Styles */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1050;
        }

        .toast {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            min-width: 250px;
            opacity: 0;
            transition: opacity 0.15s ease-in-out;
        }

        .toast.show {
            opacity: 1;
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        .toast.saving {
            border-left: 4px solid var(--primary-color);
        }

        .toast-body {
            padding: 1rem;
            color: var(--text-dark);
        }

        .toast .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        .toast-header {
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            background: transparent;
        }

        .toast-header .btn-close {
            margin-right: -0.375rem;
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container">
        <!-- Saving Toast -->
        <div class="toast align-items-center saving-toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Saving...</span>
                        </div>
                        Saving your note...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-content">
            <a href="notes.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
                Back to Notes
            </a>
            <input type="text" class="note-title-input" id="title" name="title" 
                   placeholder="Untitled note" 
                   value="<?php echo isset($note) ? htmlspecialchars($note['title']) : ''; ?>">
            <div class="action-buttons">
                <select class="status-select" id="status" name="status">
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
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Editor Section -->
        <div class="editor-section">
            <div class="editor-toolbar">
                <div class="toolbar-group">
                    <button type="button" class="toolbar-button" data-command="undo" title="Undo">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button type="button" class="toolbar-button" data-command="redo" title="Redo">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>

                <div class="toolbar-group">
                    <button type="button" class="toolbar-button" data-command="bold" title="Bold">
                        <i class="bi bi-type-bold"></i>
                    </button>
                    <button type="button" class="toolbar-button" data-command="italic" title="Italic">
                        <i class="bi bi-type-italic"></i>
                    </button>
                    <button type="button" class="toolbar-button" data-command="underline" title="Underline">
                        <i class="bi bi-type-underline"></i>
                    </button>
                </div>

                <div class="toolbar-group">
                    <button type="button" class="toolbar-button" data-command="justifyLeft" title="Align left">
                        <i class="bi bi-text-left"></i>
                    </button>
                    <button type="button" class="toolbar-button" data-command="justifyCenter" title="Align center">
                        <i class="bi bi-text-center"></i>
                    </button>
                    <button type="button" class="toolbar-button" data-command="justifyRight" title="Align right">
                        <i class="bi bi-text-right"></i>
                    </button>
                </div>

                <div class="toolbar-group">
                    <button type="button" class="toolbar-button" data-command="insertUnorderedList" title="Bullet list">
                        <i class="bi bi-list-ul"></i>
                    </button>
                    <button type="button" class="toolbar-button" data-command="insertOrderedList" title="Numbered list">
                        <i class="bi bi-list-ol"></i>
                    </button>
                </div>

                <div class="toolbar-group">
                    <button type="button" class="toolbar-button" data-command="insertImage" title="Add image">
                        <i class="bi bi-image"></i>
                    </button>
                </div>
            </div>

            <div class="editor-content" id="editor" contenteditable="true" 
                 data-placeholder="Start writing your note..."><?php 
                echo isset($note) ? htmlspecialchars_decode($note['content']) : ''; 
            ?></div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <div class="comments-header">
                <h5>Comments</h5>
                <span class="comment-count" id="comment-count">0</span>
            </div>
            
            <div class="comments-list" id="comments-container">
                <!-- Comments will be dynamically added here -->
            </div>

            <div class="comment-form">
                <textarea id="comment-text" class="comment-input" rows="3" 
                          placeholder="Write a comment..."></textarea>
                <button type="button" class="btn btn-primary w-100" id="add-comment">
                    Add Comment
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Saving -->
    <form id="noteForm" method="POST">
        <input type="hidden" name="save_note" value="1">
        <input type="hidden" name="title" id="formTitle">
        <input type="hidden" name="content" id="formContent">
        <input type="hidden" name="status" id="formStatus">
        <input type="hidden" name="pinned" id="formPinned" value="0">
    </form>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success', duration = 3000) {
            const toastContainer = document.querySelector('.toast-container');
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast align-items-center ${type}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            // Create toast content
            let icon = '';
            switch(type) {
                case 'success':
                    icon = '<i class="bi bi-check-circle me-2"></i>';
                    break;
                case 'error':
                    icon = '<i class="bi bi-x-circle me-2"></i>';
                    break;
                case 'saving':
                    icon = '<div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Saving...</span></div>';
                    break;
            }
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="d-flex align-items-center">
                            ${icon}
                            ${message}
                        </div>
                    </div>
                    ${type !== 'saving' ? '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' : ''}
                </div>
            `;
            
            // Add toast to container
            toastContainer.appendChild(toast);
            
            // Show toast
            toast.classList.add('show');
            
            // Remove toast after duration (except for saving toast)
            if (type !== 'saving') {
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 150);
                }, duration);
            }
            
            return toast;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            const title = document.getElementById('title');
            
            // Store original content for change detection
            editor.setAttribute('data-original-content', editor.innerHTML);
            title.setAttribute('data-original-value', title.value);

            // Save button click handler
            document.getElementById('saveButton').addEventListener('click', async function() {
                // Show saving toast
                const savingToast = showToast('Saving your note...', 'saving');
                
                try {
                    // Get current values
                    const titleValue = document.getElementById('title').value;
                    const contentValue = editor.innerHTML;
                    const statusValue = document.getElementById('status').value;
                    
                    // Update form values
                    document.getElementById('formTitle').value = titleValue;
                    document.getElementById('formContent').value = contentValue;
                    document.getElementById('formStatus').value = statusValue;
                    
                    // Submit the form using fetch
                    const form = document.getElementById('noteForm');
                    const formData = new FormData(form);
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Remove saving toast
                    savingToast.remove();
                    
                    if (response.ok) {
                        showToast('Note saved successfully!', 'success');
                        // Update original content to prevent unnecessary warnings
                        editor.setAttribute('data-original-content', editor.innerHTML);
                        title.setAttribute('data-original-value', title.value);
                    } else {
                        throw new Error('Failed to save note');
                    }
                } catch (error) {
                    // Remove saving toast
                    savingToast.remove();
                    showToast('Error saving note. Please try again.', 'error');
                }
            });

            // Update content when typing
            editor.addEventListener('input', function() {
                document.getElementById('formContent').value = this.innerHTML;
            });

            // Initialize content
            document.getElementById('formContent').value = editor.innerHTML;
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

        // Update comment handling code
        document.getElementById('add-comment').addEventListener('click', function(e) {
            e.preventDefault();
            const commentText = document.getElementById('comment-text').value.trim();
            if (commentText) {
                const formData = new FormData();
                formData.append('add_comment', '1');
                formData.append('comment_text', commentText);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('comment-text').value = '';
                        addCommentToDOM(data.comment);
                        updateCommentCount(data.total_comments);
                    }
                });
            }
        });

        function addCommentToDOM(comment) {
            const container = document.getElementById('comments-container');
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
            commentElement.innerHTML = `
                <div class="comment-header">
                    <span class="comment-author">${comment.author_name}</span>
                    <span class="comment-time">${new Date(comment.created_at).toLocaleString()}</span>
                </div>
                <div class="comment-text">${comment.comment}</div>
            `;
            container.insertBefore(commentElement, container.firstChild);
        }

        function updateCommentCount(count) {
            document.getElementById('comment-count').textContent = count;
        }

        // Initialize existing comments
        <?php foreach ($comments as $comment): ?>
            addCommentToDOM(<?php echo json_encode($comment); ?>);
        <?php endforeach; ?>
        updateCommentCount(<?php echo $comment_count; ?>);

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