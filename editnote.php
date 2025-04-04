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
    $content = trim($_POST['content']);
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
            $error_message = "Error saving note";
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
            --primary-color: #1a73e8;
            --border-color: rgba(0, 0, 0, 0.1);
            --bg-light: #ffffff;
            --text-dark: #202124;
            --text-muted: #5f6368;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.2s ease;
        }
        
        body {
            background-color: #fafafa;
            color: var(--text-dark);
            font-family: 'Google Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .note-container {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            padding: 12px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 16px;
            height: 56px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .back-button {
            color: var(--text-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            opacity: 0.8;
            transition: var(--transition);
        }

        .back-button:hover {
            opacity: 1;
            color: var(--primary-color);
        }

        .main-content {
            display: flex;
            flex: 1;
            overflow: hidden;
            background: #fff;
        }

        .editor-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
            overflow: hidden;
            background: white;
        }

        .note-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            background: white;
        }

        .note-header input {
            font-size: 28px;
            font-weight: 300;
            border: none;
            padding: 0;
            margin: 0;
            width: 100%;
            outline: none;
            transition: var(--transition);
        }

        .note-header input:focus {
            font-weight: 400;
        }

        .note-header small {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 8px;
            display: block;
        }

        .editor-toolbar {
            padding: 8px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 4px;
            background: white;
            flex-wrap: wrap;
        }

        .editor-toolbar button {
            padding: 8px;
            background: none;
            border: none;
            border-radius: 6px;
            color: var(--text-dark);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            transition: var(--transition);
            opacity: 0.8;
        }

        .editor-toolbar button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            opacity: 1;
        }

        .editor-toolbar button.active {
            background-color: rgba(26, 115, 232, 0.1);
            color: var(--primary-color);
            opacity: 1;
        }

        .editor-content {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            font-size: 16px;
            line-height: 1.6;
        }

        .comments-section {
            width: 320px;
            display: flex;
            flex-direction: column;
            background: white;
            border-left: 1px solid var(--border-color);
        }

        .comments-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
        }

        .comments-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
        }

        .badge {
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
            font-weight: 500;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 12px;
        }

        .comments-content {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .comment-form {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            background: white;
        }

        .comment-form textarea {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            resize: none;
            margin-bottom: 12px;
            font-size: 14px;
            transition: var(--transition);
        }

        .comment-form textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
        }

        .save-bar {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: white;
        }

        .btn {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: #1557b0;
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-outline-secondary {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
        }

        .btn-outline-secondary:hover {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.2);
        }

        .form-select {
            padding: 8px 32px 8px 12px;
            font-size: 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-select:hover {
            border-color: rgba(0, 0, 0, 0.2);
        }

        .form-select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .form-check-input {
            margin: 0;
            cursor: pointer;
        }

        /* Modal Styles */
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
        }

        .modal-body {
            padding: 24px;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
        }

        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            border: none;
            margin-top: 8px;
            opacity: 0;
            transform: translateY(100%);
            transition: all 0.3s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-header {
            background: none;
            border: none;
            padding: 12px 16px 4px;
        }

        .toast-body {
            padding: 8px 16px 12px;
        }

        /* Status colors */
        .status-not-started { color: #ea4335; }
        .status-pending { color: #fbbc04; }
        .status-completed { color: #34a853; }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="container-fluid note-container">
        <div class="top-bar">
            <a href="notes.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
                Back to Notes
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#shareModal">
                    <i class="bi bi-share-fill"></i>
                    Share
                </button>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="not-started" <?php echo (isset($note) && $note['status'] == 'not-started') ? 'selected' : ''; ?>>Not Started</option>
                    <option value="pending" <?php echo (isset($note) && $note['status'] == 'pending') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo (isset($note) && $note['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="pinned" name="pinned" <?php echo (isset($note) && $note['pinned']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="pinned">Pin note</label>
                </div>
            </div>
        </div>

        <form method="POST" class="flex-grow-1 d-flex flex-column">
            <div class="main-content">
                <div class="editor-section">
                    <div class="note-header">
                        <input type="text" id="title" name="title" placeholder="Title" required 
                               value="<?php echo isset($note) ? htmlspecialchars($note['title']) : ''; ?>">
                        <small>Last edited <?php echo isset($note) ? date('M d, Y', strtotime($note['updated_at'])) : 'Never'; ?></small>
                        <div class="mt-2">
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="not-started" <?php echo (isset($note) && $note['status'] == 'not-started') ? 'selected' : ''; ?>>Not Started</option>
                                <option value="pending" <?php echo (isset($note) && $note['status'] == 'pending') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo (isset($note) && $note['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="editor-toolbar">
                        <button type="button" data-command="bold" title="Bold">
                            <i class="bi bi-type-bold"></i>
                        </button>
                        <button type="button" data-command="italic" title="Italic">
                            <i class="bi bi-type-italic"></i>
                        </button>
                        <button type="button" data-command="underline" title="Underline">
                            <i class="bi bi-type-underline"></i>
                        </button>
                        <button type="button" data-command="strikeThrough" title="Strike through">
                            <i class="bi bi-type-strikethrough"></i>
                        </button>
                        
                        <div class="separator"></div>
                        
                        <button type="button" data-command="justifyLeft" title="Align left">
                            <i class="bi bi-text-left"></i>
                        </button>
                        <button type="button" data-command="justifyCenter" title="Align center">
                            <i class="bi bi-text-center"></i>
                        </button>
                        <button type="button" data-command="justifyRight" title="Align right">
                            <i class="bi bi-text-right"></i>
                        </button>
                        
                        <div class="separator"></div>
                        
                        <button type="button" data-command="insertUnorderedList" title="Bullet list">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <button type="button" data-command="insertOrderedList" title="Numbered list">
                            <i class="bi bi-list-ol"></i>
                        </button>
                        
                        <div class="separator"></div>
                        
                        <button type="button" data-command="createLink" title="Insert link">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                        <button type="button" data-command="insertQuote" title="Quote">
                            <i class="bi bi-quote"></i>
                        </button>
                        <button type="button" data-command="insertCode" title="Code">
                            <i class="bi bi-code-slash"></i>
                        </button>
                        <button type="button" data-command="insertTable" title="Table">
                            <i class="bi bi-table"></i>
                        </button>
                        
                        <div class="separator"></div>
                        
                        <button type="button" data-command="undo" title="Undo">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        <button type="button" data-command="redo" title="Redo">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="editor-content" id="editor" contenteditable="true" 
                         data-placeholder="Start writing your note..."><?php 
                        echo isset($note) ? htmlspecialchars_decode($note['content']) : ''; 
                    ?></div>
                    
                    <input type="hidden" id="content" name="content">

                    <div class="save-bar">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='notes.php'">
                            Cancel
                        </button>
                        <button type="submit" name="save_note" class="btn btn-primary">
                            Save Note
                        </button>
                    </div>
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
        </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add confirmation before leaving
        window.addEventListener('beforeunload', function(e) {
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
        });

        // Custom Editor Implementation
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            const toolbar = document.querySelector('.editor-toolbar');
            const contentInput = document.getElementById('content');
            
            // Update hidden input with editor content when form is submitted
            document.querySelector('form').addEventListener('submit', function() {
                contentInput.value = editor.innerHTML;
            });

            // Handle toolbar buttons
            toolbar.addEventListener('click', function(e) {
                const button = e.target.closest('button');
                if (!button) return;

                e.preventDefault();
                const command = button.dataset.command;

                switch(command) {
                    case 'createLink':
                        const url = prompt('Enter the URL:');
                        if (url) document.execCommand('createLink', false, url);
                        break;

                    case 'insertQuote':
                        document.execCommand('formatBlock', false, 'blockquote');
                        break;

                    case 'insertCode':
                        const selection = window.getSelection();
                        if (selection.toString().length > 0) {
                            document.execCommand('insertHTML', false, 
                                `<code>${selection.toString()}</code>`);
                        } else {
                            document.execCommand('insertHTML', false, 
                                '<pre><code>Enter your code here</code></pre>');
                        }
                        break;

                    case 'insertTable':
                        const rows = prompt('Enter number of rows:', '3');
                        const cols = prompt('Enter number of columns:', '3');
                        if (rows && cols) {
                            let table = '<table><tr>';
                            for (let i = 0; i < cols; i++) {
                                table += '<th>Header ' + (i + 1) + '</th>';
                            }
                            table += '</tr>';
                            for (let i = 0; i < rows; i++) {
                                table += '<tr>';
                                for (let j = 0; j < cols; j++) {
                                    table += '<td>Cell ' + (i + 1) + ',' + (j + 1) + '</td>';
                                }
                                table += '</tr>';
                            }
                            table += '</table>';
                            document.execCommand('insertHTML', false, table);
                        }
                        break;

                    default:
                        document.execCommand(command, false, null);
                }

                // Update button states
                updateButtonStates();
            });

            // Update toolbar button states based on current selection
            function updateButtonStates() {
                toolbar.querySelectorAll('button[data-command]').forEach(button => {
                    const command = button.dataset.command;
                    switch(command) {
                        case 'insertQuote':
                        case 'insertCode':
                        case 'insertTable':
                        case 'createLink':
                            break;
                        default:
                            button.classList.toggle('active', 
                                document.queryCommandState(command));
                    }
                });
            }

            // Update button states when selection changes
            editor.addEventListener('keyup', updateButtonStates);
            editor.addEventListener('mouseup', updateButtonStates);
            editor.addEventListener('input', updateButtonStates);

            // Handle paste to strip formatting
            editor.addEventListener('paste', function(e) {
                e.preventDefault();
                const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            });

            // Initialize button states
            updateButtonStates();
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

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            // Get the editor content and save it to the hidden input
            const editor = document.getElementById('editor');
            const contentInput = document.getElementById('content');
            contentInput.value = editor.innerHTML;

            const title = document.getElementById('title').value;
            
            // Validate required fields
            if (!title.trim()) {
                e.preventDefault();
                showToast('Error', 'Title is required');
                return;
            }

            showToast('Success', 'Note saved successfully!');
        });

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
