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

    // Count how many people have access to this note
    $stmt = $conn->prepare("SELECT COUNT(*) as share_count FROM note_shares WHERE note_id = ?");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $share_result = $stmt->get_result();
    $share_count = $share_result->fetch_assoc()['share_count'];

    // Get list of users who have access
    $stmt = $conn->prepare("
        SELECT u.full_name, ns.can_edit, ns.created_at 
        FROM note_shares ns 
        JOIN users u ON ns.shared_with = u.id 
        WHERE ns.note_id = ? 
        ORDER BY ns.created_at DESC
    ");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $shared_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch comments for the note and set initial count
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

    // Set initial comment count in the UI
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const commentCount = document.getElementById('comment-count');
            if (commentCount) {
                commentCount.textContent = '$comment_count';
            }
            
            const commentsContainer = document.getElementById('comments-container');
            if (commentsContainer) {
                commentsContainer.innerHTML = `" . implode('', array_map(function($comment) {
                    return "<div class='comment mb-3'>
                        <div class='comment-header'>
                            <span class='comment-author'>{$comment['author_name']}</span>
                            <span class='comment-time'>" . date('M j, Y g:i A', strtotime($comment['created_at'])) . "</span>
                        </div>
                        <div class='comment-text'>{$comment['comment']}</div>
                    </div>";
                }, $comments)) . "`;
            }
        });
    </script>";
}

// After database connection, modify the share limit check to be per-note
if ($note_id > 0) {
    // Get current share count for this specific note
    $stmt = $conn->prepare("SELECT COUNT(*) as current_shares FROM note_shares WHERE note_id = ?");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $current_shares = $stmt->get_result()->fetch_assoc()['current_shares'];

    // Get user's plan type
    $stmt = $conn->prepare("
        SELECT p.is_unlimited, p.name as plan_name
        FROM plans p 
        INNER JOIN subscriptions s ON p.id = s.plan_id 
        WHERE s.user_id = ? AND s.status = 'active' 
        AND s.end_date >= CURRENT_DATE
        ORDER BY s.created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $plan_result = $stmt->get_result()->fetch_assoc();

    // Set share limit based on plan
    $is_premium = $plan_result && $plan_result['is_unlimited'];
    $share_limit_per_note = 5; // Basic plan limit per note
    $share_limit_reached = !$is_premium && $current_shares >= $share_limit_per_note;

    if ($share_limit_reached) {
        $error_message = sprintf(
            '<div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle me-2"></i>
                This note has reached its sharing limit (%d users).
                <a href="plans.php?highlight=premium" class="alert-link">Upgrade to Premium</a> for unlimited sharing per note.
            </div>',
            $share_limit_per_note
        );
    }
}

// After database connection, check if user has an active subscription
$stmt = $conn->prepare("
    SELECT p.is_unlimited, p.name as plan_name
    FROM plans p 
    INNER JOIN subscriptions s ON p.id = s.plan_id 
    WHERE s.user_id = ? AND s.status = 'active' 
    AND s.end_date >= CURRENT_DATE
    ORDER BY s.created_at DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_plan = $stmt->get_result()->fetch_assoc();
$has_subscription = !empty($user_plan);

// Fetch users for sharing
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle note save/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $status = $_POST['status'];
    $pinned = isset($_POST['pinned']) ? 1 : 0;
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    if (empty($title)) {
        $error_message = "Title is required";
    } else {
        if ($note_id > 0) {
            // Update existing note
            $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, status = ?, pinned = ?, is_private = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssiiii", $title, $content, $status, $pinned, $is_private, $note_id, $user_id);
        } else {
            // Create new note
            $stmt = $conn->prepare("INSERT INTO notes (title, content, user_id, status, pinned, is_private) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssissi", $title, $content, $user_id, $status, $pinned, $is_private);
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
    // First check if user has an active subscription
    $stmt = $conn->prepare("
        SELECT p.is_unlimited, p.name as plan_name
        FROM plans p 
        INNER JOIN subscriptions s ON p.id = s.plan_id 
        WHERE s.user_id = ? AND s.status = 'active' 
        AND s.end_date >= CURRENT_DATE
        ORDER BY s.created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_plan = $stmt->get_result()->fetch_assoc();

    if (!$user_plan) {
        $error_message = 'You need an active subscription to share notes. <a href="plans.php" class="alert-link">Subscribe to a plan</a> to enable sharing.';
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => strip_tags($error_message)]);
            exit;
        }
        return;
    }

    // Get current share count for this note
    $stmt = $conn->prepare("SELECT COUNT(*) as current_shares FROM note_shares WHERE note_id = ?");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $current_shares = $stmt->get_result()->fetch_assoc()['current_shares'];

    // Check share limits based on plan
    $is_premium = $user_plan['is_unlimited'];
    $share_limit_per_note = 5;

    if (!$is_premium && $current_shares >= $share_limit_per_note) {
        $error_message = sprintf(
            'You have reached the sharing limit (%d users) for this note on the Basic Plan. <a href="plans.php?highlight=premium">Upgrade to Premium</a> for unlimited sharing.',
            $share_limit_per_note
        );
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => strip_tags($error_message)]);
            exit;
        }
        return;
    }

    $share_with = (int)$_POST['share_with'];
    $can_edit = isset($_POST['can_edit']) ? 1 : 0;
    
    // Check if share already exists
    $stmt = $conn->prepare("SELECT id FROM note_shares WHERE note_id = ? AND shared_with = ?");
    $stmt->bind_param("ii", $note_id, $share_with);
    $stmt->execute();
    $existing_share = $stmt->get_result()->fetch_assoc();
    
    if (!$existing_share) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert the share record
            $stmt = $conn->prepare("INSERT INTO note_shares (note_id, shared_by, shared_with, can_edit) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $note_id, $user_id, $share_with, $can_edit);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to share note");
            }
            
            // Create notification
            $note_title = $note['title'];
            $message = "A note titled '$note_title' has been shared with you" . ($can_edit ? " with edit permissions." : ".");
            $stmt = $conn->prepare("INSERT INTO notifications (message, recipient, type, date) VALUES (?, ?, 'Note Shared', CURRENT_DATE)");
            $stmt->bind_param("si", $message, $share_with);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create notification");
            }
            
            // Commit transaction
            $conn->commit();
            $success_message = "Note shared successfully!";
            
            // If this is an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $success_message]);
                exit;
            }
            
            // Refresh share count and shared users list
            $stmt = $conn->prepare("SELECT COUNT(*) as share_count FROM note_shares WHERE note_id = ?");
            $stmt->bind_param("i", $note_id);
            $stmt->execute();
            $share_result = $stmt->get_result();
            $share_count = $share_result->fetch_assoc()['share_count'];
            
            $stmt = $conn->prepare("
                SELECT u.full_name, ns.can_edit, ns.created_at 
                FROM note_shares ns 
                JOIN users u ON ns.shared_with = u.id 
                WHERE ns.note_id = ? 
                ORDER BY ns.created_at DESC
            ");
            $stmt->bind_param("i", $note_id);
            $stmt->execute();
            $shared_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_message = $e->getMessage();
            
            // If this is an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit;
            }
        }
    } else {
        $error_message = "Note is already shared with this user";
        
        // If this is an AJAX request, return JSON response
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error_message]);
            exit;
        }
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment_text = trim($_POST['comment_text']);
    if (!empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO note_comments (note_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $note_id, $user_id, $comment_text);
        if ($stmt->execute()) {
            // Get the new total comment count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM note_comments WHERE note_id = ?");
            $stmt->bind_param("i", $note_id);
            $stmt->execute();
            $new_count = $stmt->get_result()->fetch_assoc()['count'];
            
            // Fetch the newly added comment with user details
            $stmt = $conn->prepare("SELECT c.*, u.full_name as author_name 
                                  FROM note_comments c 
                                  JOIN users u ON c.user_id = u.id 
                                  WHERE c.id = LAST_INSERT_ID()");
            $stmt->execute();
            $new_comment = $stmt->get_result()->fetch_assoc();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'comment' => $new_comment,
                'total_comments' => $new_count,
                'html' => "<div class='comment mb-3'>
                            <div class='comment-header'>
                                <span class='comment-author'>{$new_comment['author_name']}</span>
                                <span class='comment-time'>" . date('M j, Y g:i A', strtotime($new_comment['created_at'])) . "</span>
                            </div>
                            <div class='comment-text'>{$new_comment['comment']}</div>
                          </div>"
            ]);
            exit;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

// Immediately before the share modal HTML, ensure all variables are defined
if (!isset($share_limit_reached)) {
    $share_limit_reached = false;
}
if (!isset($is_premium)) {
    $is_premium = false;
}
if (!isset($share_limit_per_note)) {
    $share_limit_per_note = 5;
}
if (!isset($share_count)) {
    $share_count = 0;
}
if (!isset($shared_users)) {
    $shared_users = [];
}
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
            font-size: 1.5rem;
            font-weight: 600;
            border: none;
            padding: 0.75rem 1rem;
            margin: 0 1rem;
            border-radius: 0.5rem;
            transition: var(--transition);
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .note-title-input:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .note-title-input::placeholder {
            color: var(--text-muted);
            font-weight: 500;
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background: var(--primary-color);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary i {
            font-size: 1.1em;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .status-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
            background-color: white;
            min-width: 160px;
            transition: var(--transition);
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Status Select Options Styling */
        .status-select option {
            padding: 0.5rem;
            font-weight: 500;
        }

        .status-select option[value="not-started"] {
            color: #6b7280;
        }

        .status-select option[value="pending"] {
            color: #f59e0b;
        }

        .status-select option[value="completed"] {
            color: #10b981;
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
                padding: 0.5rem;
            }

            .note-title-input {
                font-size: 1.25rem;
                margin: 0.5rem 0;
            }

            .action-buttons {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .status-select {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .btn-primary {
                width: 100%;
                justify-content: center;
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

        /* Share Modal Improvements */
        .share-limit-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            color: var(--text-muted);
        }

        .share-limit-info i {
            font-size: 1rem;
            color: var(--primary-color);
        }

        .share-limit-info small {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Share Modal Styles */
        .share-stats {
            background-color: var(--bg-light);
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .current-shares {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .current-shares i {
            font-size: 1.25rem;
        }

        .shared-users-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .shared-user-item {
            padding: 0.5rem;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            background: white;
        }

        .shared-user-item:last-child {
            margin-bottom: 0;
        }

        .shared-user-item .user-name {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
        }

        .shared-user-item .share-details {
            display: block;
            font-size: 0.875rem;
        }

        .modal-body hr {
            border-color: var(--border-color);
            margin: 1rem 0;
        }

        /* Share Limit Modal Styles */
        #shareLimitModal .modal-content {
            border-radius: 1rem;
            background: white;
        }

        #shareLimitModal .share-limit-icon {
            width: 64px;
            height: 64px;
            background: var(--bg-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        #shareLimitModal .share-limit-icon i {
            font-size: 28px;
            color: var(--primary-color);
        }

        #shareLimitModal .modal-title {
            color: var(--text-dark);
            font-weight: 600;
        }

        #shareLimitModal .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        #shareLimitModal .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        #shareLimitModal .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        #shareLimitModal .btn-light {
            background: var(--bg-light);
            border: none;
            color: var(--text-dark);
        }

        #shareLimitModal .btn-light:hover {
            background: #e5e7eb;
        }

        #shareLimitModal p {
            font-size: 0.95rem;
            line-height: 1.5;
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
                <button type="button" class="btn btn-primary" onclick="handleShareClick()" id="shareButton" <?php echo $note_id ? '' : 'disabled'; ?> title="<?php echo $note_id ? '' : 'Please save the note before sharing.'; ?>">
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
                    <?php if (isset($share_count) && $share_count > 0): ?>
                        <div class="share-stats mb-3">
                            <div class="current-shares">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span>
                                    <?php if ($is_premium): ?>
                                        <?php echo $share_count; ?> people have access
                                    <?php else: ?>
                                        <?php echo $share_count; ?> of <?php echo $share_limit_per_note; ?> people have access
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="shared-users-list mt-2">
                                <?php foreach ($shared_users as $shared_user): ?>
                                    <div class="shared-user-item">
                                        <span class="user-name"><?php echo htmlspecialchars($shared_user['full_name']); ?></span>
                                        <span class="share-details">
                                            <small class="text-muted">
                                                <?php echo $shared_user['can_edit'] ? 'Can edit' : 'Can view'; ?> • 
                                                Shared <?php echo date('M j, Y', strtotime($shared_user['created_at'])); ?>
                                            </small>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <hr class="my-3">
                        </div>
                    <?php endif; ?>

                    <?php if ($share_limit_reached): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            This note has reached its sharing limit of <?php echo $share_limit_per_note; ?> users for Basic Plan. 
                            <a href="plans.php" class="alert-link">Upgrade to Premium</a> for unlimited sharing per note.
                        </div>
                    <?php else: ?>
                        <form method="POST" id="shareForm">
                            <div class="mb-3">
                                <label class="form-label">Share with</label>
                                <div class="share-limit-info mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        <?php if ($is_premium): ?>
                                            Premium Plan: Share with unlimited people
                                        <?php else: ?>
                                            Basic Plan: Share with up to <?php echo $share_limit_per_note; ?> people per note
                                        <?php endif; ?>
                                    </small>
                                </div>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Upgrade Modal -->
    <div class="modal fade" id="planUpgradeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center px-4 py-4">
                    <i class="fa fa-lock fa-3x text-warning mb-3"></i>
                    <h4 class="mb-3">Subscription Required</h4>
                    <p class="text-muted mb-4">
                        You need an active subscription to share notes. Choose a plan that suits your needs and start sharing!
                    </p>
                    <a href="plans.php" class="btn btn-primary btn-lg px-5">
                        View Plans
                    </a>
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
            const saveButton = document.getElementById('saveButton');
            const titleInput = document.getElementById('title');
            const editor = document.getElementById('editor');
            const statusSelect = document.getElementById('status');
            const noteForm = document.getElementById('noteForm');
            const shareForm = document.getElementById('shareForm');
            const shareButton = document.getElementById('shareButton');
            const hasSubscription = <?php echo $has_subscription ? 'true' : 'false' ?>;

            // Save functionality
            saveButton.addEventListener('click', async function() {
                // Show saving toast
                const savingToast = showToast('Saving your note...', 'saving');
                
                try {
                    // Update form values
                    document.getElementById('formTitle').value = titleInput.value;
                    document.getElementById('formContent').value = editor.innerHTML;
                    document.getElementById('formStatus').value = statusSelect.value;
                    
                    // Submit the form using fetch
                    const formData = new FormData(noteForm);
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Remove saving toast
                    savingToast.remove();
                    
                    if (response.ok) {
                        const text = await response.text();
                        if (text.includes('Note saved successfully!')) {
                            showToast('Note saved successfully!', 'success');
                            // Enable share button after saving
                            if (shareButton) {
                                shareButton.disabled = false;
                                shareButton.title = '';
                            }
                        } else {
                            throw new Error('Failed to save note');
                        }
                    } else {
                        throw new Error('Failed to save note');
                    }
                } catch (error) {
                    // Remove saving toast
                    savingToast.remove();
                    showToast('Error saving note. Please try again.', 'error');
                }
            });

            // Share functionality
            if (shareForm) {
                shareForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Show saving toast
                    const savingToast = showToast('Sharing note...', 'saving');
                    
                    try {
                        const formData = new FormData(this);
                        formData.append('share_note', '1');
                        
                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        const data = await response.json();
                        
                        // Remove saving toast
                        savingToast.remove();
                        
                        if (data.success) {
                            showToast(data.message || 'Note shared successfully!', 'success');
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('shareModal'));
                            if (modal) {
                                modal.hide();
                            }
                            // Reload the page to update the shared users list
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast(data.message || 'Error sharing note', 'error');
                        }
                    } catch (error) {
                        // Remove saving toast
                        savingToast.remove();
                        showToast('Error sharing note. Please try again.', 'error');
                        console.error('Share error:', error);
                    }
                });
            }

            // Handle share button click for users without subscription
            if (shareButton) {
                shareButton.addEventListener('click', function(e) {
                    if (!hasSubscription) {
                        e.preventDefault();
                        e.stopPropagation();
                        const modal = new bootstrap.Modal(document.getElementById('planUpgradeModal'));
                        modal.show();
                    }
                });
            }
        });

        // Custom Editor Implementation
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            const toolbarButtons = document.querySelectorAll('.toolbar-button');

            // Handle toolbar button clicks
            toolbarButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const command = this.dataset.command;
                    
                    // Focus the editor first
                    editor.focus();
                    
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
                            
                        case 'undo':
                            document.execCommand('undo', false, null);
                            break;
                            
                        case 'redo':
                            document.execCommand('redo', false, null);
                            break;
                            
                        default:
                            document.execCommand(command, false, null);
                            // Toggle active state for formatting buttons
                            if (!['insertImage', 'undo', 'redo'].includes(command)) {
                                this.classList.toggle('active');
                            }
                            break;
                    }
                    
                    // Update toolbar state after command
                    updateToolbarState();
                });
            });

            // Update toolbar state based on selection
            function updateToolbarState() {
                toolbarButtons.forEach(button => {
                    const command = button.dataset.command;
                    if (['insertImage', 'undo', 'redo'].includes(command)) return;
                    
                    if (document.queryCommandState(command)) {
                        button.classList.add('active');
                    } else {
                        button.classList.remove('active');
                    }
                });
            }

            // Update toolbar state on selection change
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            editor.addEventListener('input', updateToolbarState);

            // Handle paste to strip formatting
            editor.addEventListener('paste', function(e) {
                e.preventDefault();
                const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            });

            // Add keyboard shortcuts
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
                        case 'z':
                            e.preventDefault();
                            if (e.shiftKey) {
                                document.execCommand('redo', false, null);
                            } else {
                                document.execCommand('undo', false, null);
                            }
                            break;
                    }
                }
            });

            // Initialize toolbar state
            updateToolbarState();
        });

        // Comments functionality
        document.addEventListener('DOMContentLoaded', function() {
            const addCommentButton = document.getElementById('add-comment');
            const commentText = document.getElementById('comment-text');
            const commentsContainer = document.getElementById('comments-container');
            const commentCount = document.getElementById('comment-count');

            addCommentButton.addEventListener('click', async function(e) {
                e.preventDefault();
                const text = commentText.value.trim();
                
                if (!text) return;

                try {
                    const formData = new FormData();
                    formData.append('add_comment', '1');
                    formData.append('comment_text', text);

                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        // Clear comment input
                        commentText.value = '';
                        
                        // Add new comment HTML to container
                        commentsContainer.insertAdjacentHTML('afterbegin', data.html);
                        
                        // Update comment count
                        commentCount.textContent = data.total_comments;
                        
                        showToast('Comment added successfully!', 'success');
                    } else {
                        throw new Error('Failed to add comment');
                    }
                } catch (error) {
                    showToast('Error adding comment. Please try again.', 'error');
                }
            });
        });

        function handleShareClick() {
            const hasSubscription = <?php echo $has_subscription ? 'true' : 'false' ?>;
            if (!hasSubscription) {
                const modal = new bootstrap.Modal(document.getElementById('planUpgradeModal'));
                modal.show();
                return;
            }
            const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
            shareModal.show();
        }
    </script>
</body>
</html>