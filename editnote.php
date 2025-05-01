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

// After database connection, add these functions
function getUserPlanLimits($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT p.note_limit, p.private_note_limit, p.share_limit, p.is_unlimited 
        FROM plans p 
        INNER JOIN subscriptions s ON p.id = s.plan_id 
        WHERE s.user_id = ? AND s.status = 'active' 
        AND s.end_date >= CURRENT_DATE
        ORDER BY s.created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();
    
    // If no active subscription, return basic plan limits
    if (!$plan) {
        return [
            'note_limit' => 10,
            'private_note_limit' => 0,
            'share_limit' => 5,  // Basic plan can share up to 5 notes
            'is_unlimited' => false
        ];
    }
    
    return $plan;
}

function countUserNotes($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function countUserPrivateNotes($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ? AND is_private = 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function countUserSharedNotes($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT note_id) as count FROM note_shares WHERE shared_by = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

// Get user's current plan limits
$planLimits = getUserPlanLimits($conn, $user_id);
$currentNotes = countUserNotes($conn, $user_id);
$currentPrivateNotes = countUserPrivateNotes($conn, $user_id);
$currentSharedNotes = countUserSharedNotes($conn, $user_id);

// Check if user has reached any limits
$noteLimit = $planLimits['note_limit'];
$privateNoteLimit = $planLimits['private_note_limit'];
$shareLimit = $planLimits['share_limit'];

$canCreateNote = $planLimits['is_unlimited'] || $currentNotes < $noteLimit;
$canCreatePrivateNote = $planLimits['is_unlimited'] || $currentPrivateNotes < $privateNoteLimit;
$canShareNote = $planLimits['is_unlimited'] || $currentSharedNotes < $shareLimit;

// If creating a new note and user has reached limit, redirect to notes page
if (!$note_id && !$canCreateNote) {
    $_SESSION['error_message'] = "You have reached your plan's note limit of {$noteLimit} notes. Please upgrade your plan to create more notes.";
    header('Location: notes.php');
    exit();
}

// After database connection, add this to check share count
if ($note_id > 0) {
    // Get current share count for this note
    $stmt = $conn->prepare("SELECT COUNT(*) as current_shares FROM note_shares WHERE note_id = ?");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $current_shares = $stmt->get_result()->fetch_assoc()['current_shares'];

    // Get user's plan limit
    $stmt = $conn->prepare("
        SELECT p.share_limit, p.is_unlimited 
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
    
    $share_limit = $plan_result ? ($plan_result['is_unlimited'] ? null : $plan_result['share_limit']) : 5;
    $has_reached_limit = !is_null($share_limit) && $current_shares >= $share_limit;
}

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
        // Check note limit if creating a new note
        if (!$note_id && !$canCreateNote) {
            $error_message = "You have reached your plan's note limit of {$noteLimit} notes. Please upgrade your plan to create more notes.";
        } else {
            // Check private note limit if trying to create a private note
            if ($is_private && !$canCreatePrivateNote && (!$note || !$note['is_private'])) {
                $error_message = "You have reached your plan's private note limit. Please upgrade to create more private notes.";
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
    }
}

// Handle note sharing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_note'])) {
    if (!$canShareNote) {
        $error_message = "You have reached your plan's share limit of 5 notes. Please upgrade to share more notes.";
    } else {
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
            }
        } else {
            $error_message = "Note is already shared with this user";
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
                    <?php if (isset($share_count) && $share_count > 0): ?>
                        <div class="share-stats mb-3">
                            <div class="current-shares">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span><?php echo $share_count; ?> people have access</span>
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

                    <?php if (!$canShareNote): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            You have reached your plan's share limit of 5 notes. 
                            <a href="plans.php" class="alert-link">Upgrade your plan</a> to share more notes.
                        </div>
                    <?php else: ?>
                        <form method="POST" id="shareForm">
                            <div class="mb-3">
                                <label class="form-label">Share with</label>
                                <div class="share-limit-info mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Basic Plan allows sharing with up to 5 people
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

    <!-- Share Limit Modal -->
    <div class="modal fade" id="shareLimitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="share-limit-icon mb-3">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <h5 class="modal-title mb-3">Sharing Limit Reached</h5>
                    <p class="text-muted mb-4">
                        You've reached the sharing limit for the Basic Plan. 
                        To share with more users, please upgrade to the Premium Plan.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="plans.php?highlight=premium" class="btn btn-primary">
                            <i class="bi bi-arrow-up-circle me-2"></i>
                            Upgrade to Premium
                        </a>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Maybe Later
                        </button>
                    </div>
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

        // Update share form submission
        document.getElementById('shareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            <?php if ($has_reached_limit): ?>
            // Show share limit modal if limit reached
            const shareLimitModal = new bootstrap.Modal(document.getElementById('shareLimitModal'));
            shareLimitModal.show();
            return;
            <?php endif; ?>
            
            const formData = new FormData(this);
            formData.append('share_note', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                if (html.includes('Share limit reached for your current plan')) {
                    // Show share limit modal if limit reached during submission
                    const shareLimitModal = new bootstrap.Modal(document.getElementById('shareLimitModal'));
                    shareLimitModal.show();
                } else {
                    // Refresh the page to show updated share count
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error sharing note. Please try again.', 'error');
            });
        });
    </script>
</body>
</html>