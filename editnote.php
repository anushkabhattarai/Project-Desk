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

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['id'];
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
    $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(3, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        header('Location: notes.php');
        exit();
    }

    // Count how many people have access to this note
    $stmt = $conn->prepare("SELECT COUNT(*) as share_count FROM note_shares WHERE note_id = ?");
    $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
    $stmt->execute();
    $share_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $share_count = $share_result['share_count'];

    // Get list of users who have access
    $stmt = $conn->prepare("
        SELECT ns.id as share_id, u.full_name, ns.can_edit, ns.created_at 
        FROM note_shares ns 
        JOIN users u ON ns.shared_with = u.id 
        WHERE ns.note_id = ? 
        ORDER BY ns.created_at DESC
    ");
    $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
    $stmt->execute();
    $shared_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initial comment loading block
$comments = [];
$comment_count = 0;
if ($note_id > 0) {
    $stmt = $conn->prepare("SELECT c.*, u.full_name as author_name 
                           FROM note_comments c 
                           JOIN users u ON c.user_id = u.id 
                           WHERE c.note_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $comment_count = count($comments);
}

// After database connection, modify the share limit check to be per-note
if ($note_id > 0) {
    // Get current share count for this specific note
    $stmt = $conn->prepare("SELECT COUNT(*) as current_shares FROM note_shares WHERE note_id = ?");
    $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
    $stmt->execute();
    $current_shares = $stmt->fetch(PDO::FETCH_ASSOC)['current_shares'];

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
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $plan_result = $stmt->fetch(PDO::FETCH_ASSOC);

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
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_plan = $stmt->fetch(PDO::FETCH_ASSOC);
$has_subscription = !empty($user_plan);

// Fetch users for sharing
$stmt = $conn->prepare("SELECT id, full_name, username FROM users WHERE id != ?");
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle note save/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'redirect' => null];
    
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $status = $_POST['status'];
    $pinned = isset($_POST['pinned']) ? 1 : 0;
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if (empty($title)) {
        $response['message'] = "Title is required";
        echo json_encode($response);
        exit;
    }

    try {
        if ($edit_id > 0) {
            // Update existing note
            $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, status = ?, pinned = ?, is_private = ? WHERE id = ? AND user_id = ?");
            $stmt->bindParam(1, $title, PDO::PARAM_STR);
            $stmt->bindParam(2, $content, PDO::PARAM_STR);
            $stmt->bindParam(3, $status, PDO::PARAM_STR);
            $stmt->bindParam(4, $pinned, PDO::PARAM_INT);
            $stmt->bindParam(5, $is_private, PDO::PARAM_INT);
            $stmt->bindParam(6, $edit_id, PDO::PARAM_INT);
            $stmt->bindParam(7, $user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Note updated successfully!";
            }
        } else {
            // Create new note
            $stmt = $conn->prepare("INSERT INTO notes (title, content, user_id, status, pinned, is_private) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bindParam(1, $title, PDO::PARAM_STR);
            $stmt->bindParam(2, $content, PDO::PARAM_STR);
            $stmt->bindParam(3, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(4, $status, PDO::PARAM_STR);
            $stmt->bindParam(5, $pinned, PDO::PARAM_INT);
            $stmt->bindParam(6, $is_private, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $new_note_id = $conn->lastInsertId();
                $response['success'] = true;
                $response['message'] = "Note created successfully!";
                $response['redirect'] = "editnote.php?id=" . $new_note_id;
            }
        }
    } catch (PDOException $e) {
        $response['message'] = "Error saving note: " . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Handle comment submission - AJAX endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    header('Content-Type: application/json');
    
    $comment_text = trim($_POST['comment_text']);
    // Process mentions in the comment
    $mentioned_users = [];
    preg_match_all('/@\[([^\]]+)\]\((\d+)\)/', $comment_text, $matches);
    if (!empty($matches[2])) {
        $mentioned_users = array_unique($matches[2]);
    }
    
    if (!empty($comment_text) && $note_id > 0) {
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Insert comment
            $stmt = $conn->prepare("INSERT INTO note_comments (note_id, user_id, comment) VALUES (:note_id, :user_id, :comment)");
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment_text, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert comment");
            }
            
            $comment_id = $conn->lastInsertId();

            // Add notifications for mentioned users
            if (!empty($mentioned_users)) {
                $notify_stmt = $conn->prepare("
                    INSERT INTO notifications (recipient, message, type, date) 
                    VALUES (:recipient, :message, 'mention', CURRENT_DATE)
                ");
                
                foreach ($mentioned_users as $mentioned_id) {
                    $message = $_SESSION['username'] . " mentioned you in a comment on note '" . $note['title'] . "'";
                    $notify_stmt->execute([
                        ':recipient' => $mentioned_id,
                        ':message' => $message
                    ]);
                }
            }

            // Get comment details with user info
            $stmt = $conn->prepare("
                SELECT c.*, u.full_name as author_name 
                FROM note_comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = :comment_id
            ");
            $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $new_comment = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get updated comment count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM note_comments WHERE note_id = :note_id");
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();
            $count_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_count = $count_result['count'];

            // Commit transaction
            $conn->commit();

            echo json_encode([
                'success' => true,
                'comment' => $new_comment,
                'total_comments' => $new_count,
                'html' => "<div class='comment mb-3'>
                            <div class='comment-header'>
                                <span class='comment-author'>" . htmlspecialchars($new_comment['author_name']) . "</span>
                                <span class='comment-time'>" . date('M j, Y g:i A', strtotime($new_comment['created_at'])) . "</span>
                            </div>
                            <div class='comment-text'>" . htmlspecialchars($new_comment['comment']) . "</div>
                          </div>"
            ]);
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please enter a comment']);
        exit;
    }
}

// Handle note sharing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_note'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    // Add current user ID to share record
    $share_with = isset($_POST['share_with']) ? (int)$_POST['share_with'] : 0;
    $can_edit = isset($_POST['can_edit']) ? 1 : 0;

    try {
        // Check if note is already shared with this user
        $stmt = $conn->prepare("SELECT id FROM note_shares WHERE note_id = ? AND shared_with = ?");
        $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $share_with, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $response['message'] = "Note is already shared with this user.";
            echo json_encode($response);
            exit;
        }

        // Share the note with shared_by field
        $stmt = $conn->prepare("INSERT INTO note_shares (note_id, shared_by, shared_with, can_edit) VALUES (?, ?, ?, ?)");
        $stmt->bindParam(1, $note_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $user_id, PDO::PARAM_INT); // Add current user as shared_by
        $stmt->bindParam(3, $share_with, PDO::PARAM_INT);
        $stmt->bindParam(4, $can_edit, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Note shared successfully!";
        } else {
            $response['message'] = "Failed to share note.";
        }
    } catch (PDOException $e) {
        error_log("Share error: " . $e->getMessage());
        $response['message'] = "Error sharing note: " . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Add this new PHP handler near other POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_permission'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    $share_id = isset($_POST['share_id']) ? (int)$_POST['share_id'] : 0;
    $can_edit = isset($_POST['can_edit']) ? 1 : 0;
    
    try {
        // Update share permissions
        $stmt = $conn->prepare("UPDATE note_shares SET can_edit = ? WHERE id = ? AND note_id = ?");
        $stmt->bindParam(1, $can_edit, PDO::PARAM_INT);
        $stmt->bindParam(2, $share_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $note_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Permissions updated successfully!";
        }
    } catch (PDOException $e) {
        $response['message'] = "Error updating permissions: " . $e->getMessage();
    }
    
    echo json_encode($response);
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

        /* Author Info Styles */
        .note-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-light);
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .author-info {
            display: flex;
            flex-direction: column;
        }

        .author-name {
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .note-date {
            font-size: 0.8rem;
            color: var(--text-muted);
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

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            color: white;
            background: var(--primary-color);
        }

        .btn-outline-secondary {
            color: var(--text-muted);
            border-color: var(--border-color);
            background: transparent;
            opacity: 0.7;
        }

        .btn-outline-secondary:hover {
            cursor: not-allowed;
        }

        .btn-outline-secondary:not([disabled]) {
            opacity: 1;
            color: var(--primary-color);
            border-color: var(--primary-color);
            cursor: pointer;
        }

        .btn-outline-secondary:not([disabled]):hover {
            background: var(--primary-color);
            color: white;
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

        /* User Search Styles */
        .user-search-container {
            position: relative;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: var(--shadow-md);
        }

        .search-results.active {
            display: block;
        }

        .search-result-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-result-item:hover {
            background: var(--bg-light);
        }

        .search-result-item:not(:last-child) {
            border-bottom: 1px solid var(--border-color);
        }

        .user-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .user-username {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Members Modal Styles */
        .members-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .member-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: var(--transition);
        }

        .member-info {
            flex-grow: 1;
        }

        .member-actions {
            margin-left: auto;
        }

        .toggle-permission {
            white-space: nowrap;
            font-size: 0.875rem;
        }

        .member-item:hover {
            background: var(--bg-light);
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .member-info {
            display: flex;
            flex-direction: column;
        }

        .member-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .member-role {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Mention Styles */
        .mention-popup {
            position: absolute;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-md);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            min-width: 200px;
            display: none;
        }

        .mention-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .mention-item:hover {
            background: var(--bg-light);
        }

        .mention {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
        }

        .mention-popup {
            min-width: 250px;
            max-width: 300px;
            padding: 0.5rem;
        }

        .mention-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            cursor: pointer;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }

        .mention-user-info {
            display: flex;
            flex-direction: column;
        }

        .mention-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .mention-username {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .mention-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            background-color: var(--bg-light);
            color: var(--primary-color);
            border-radius: 1rem;
            font-weight: 500;
        }

        .mention-item:hover {
            background-color: var(--bg-light);
        }

        .mention-item.no-results {
            color: var(--text-muted);
            justify-content: center;
            cursor: default;
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
                <button type="button" class="btn btn-outline-secondary" onclick="showMembers()" <?php echo $note_id ? '' : 'disabled'; ?> title="<?php echo $note_id ? '' : 'Save the note first to manage members'; ?>">
                    <i class="bi bi-people-fill"></i>
                    Members
                    <?php if (isset($share_count) && $share_count > 0): ?>
                    <span class="badge bg-primary ms-1"><?php echo $share_count + 1; ?></span>
                    <?php endif; ?>
                </button>
                
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
            <?php if (isset($note) && isset($note['author_name'])): ?>
            <div class="note-author">
                <div class="author-avatar">
                    <?php 
                    $author_initial = strtoupper(substr($note['author_name'], 0, 1));
                    echo htmlspecialchars($author_initial); 
                    ?>
                </div>
                <div class="author-info">
                    <span class="author-name"><?php echo htmlspecialchars($note['author_name']); ?></span>
                    <span class="note-date"><?php echo date('M j, Y', strtotime($note['created_at'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
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
        <input type="hidden" name="edit_id" id="formEditId" value="<?php echo $note_id; ?>">
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
                                <div class="user-search-container">
                                    <input type="text" 
                                           class="form-control" 
                                           id="userSearch" 
                                           placeholder="Type username to share with..."
                                           autocomplete="off">
                                    <input type="hidden" name="share_with" id="selectedUserId">
                                    <div id="searchResults" class="search-results"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="can_edit" name="can_edit">
                                    <label class="form-check-label" for="can_edit">Allow editing</label>
                                </div>
                            </div>
                            <button type="submit" name="share_note" class="btn btn-primary w-100" id="shareSubmitBtn" disabled>
                                Share Note
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Modal -->
    <div class="modal fade" id="membersModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-people-fill"></i>
                        Note Members
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="members-list">
                        <!-- Owner -->
                        <div class="member-item">
                            <div class="member-avatar">
                                <?php echo strtoupper(substr($note['author_name'], 0, 1)); ?>
                            </div>
                            <div class="member-info">
                                <span class="member-name"><?php echo htmlspecialchars($note['author_name']); ?></span>
                                <span class="member-role">Owner</span>
                            </div>
                        </div>
                        <!-- Shared Users -->
                        <?php foreach ($shared_users as $user): ?>
                        <div class="member-item">
                            <div class="member-avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div class="member-info flex-grow-1">
                                <span class="member-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <span class="member-role" id="role-<?php echo $user['share_id']; ?>">
                                    <?php echo $user['can_edit'] ? 'Can edit' : 'Can view'; ?>
                                </span>
                            </div>
                            <?php if ($note['user_id'] == $user_id): ?>
                            <div class="member-actions">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary toggle-permission" 
                                        data-share-id="<?php echo $user['share_id']; ?>"
                                        data-current-permission="<?php echo $user['can_edit']; ?>">
                                    <?php echo $user['can_edit'] ? 'Set to view only' : 'Allow editing'; ?>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
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
                    
                    const data = await response.json();
                    
                    // Remove saving toast
                    savingToast.remove();
                    
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        // If there's a redirect URL, update the members button before redirecting
                        if (data.redirect) {
                            // Enable the members button and update its style
                            const membersBtn = document.querySelector('.btn-outline-secondary[disabled]');
                            if (membersBtn) {
                                membersBtn.classList.remove('btn-outline-secondary');
                                membersBtn.classList.add('btn-outline-primary');
                                membersBtn.disabled = false;
                                membersBtn.title = '';
                            }
                            
                            // Enable share button
                            if (shareButton) {
                                shareButton.disabled = false;
                                shareButton.title = '';
                            }
                            
                            // Redirect after a short delay to show the success message
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        }
                    } else {
                        throw new Error(data.message || 'Failed to save note');
                    }
                } catch (error) {
                    // Remove saving toast
                    savingToast.remove();
                    showToast(error.message || 'Error saving note. Please try again.', 'error');
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

            if (addCommentButton && commentText) {
                addCommentButton.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const text = commentText.value.trim();
                    
                    if (!text) {
                        showToast('Please enter a comment', 'error');
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('add_comment', '1');
                        formData.append('comment_text', text);

                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            // Clear comment input
                            commentText.value = '';
                            
                            // Add new comment HTML to container
                            if (commentsContainer) {
                                commentsContainer.insertAdjacentHTML('afterbegin', data.html);
                            }
                            
                            // Update comment count
                            if (commentCount) {
                                commentCount.textContent = data.total_comments;
                            }
                            
                            showToast('Comment added successfully!', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to add comment');
                        }
                    } catch (error) {
                        console.error('Comment error:', error);
                        showToast(error.message || 'Error adding comment. Please try again.', 'error');
                    }
                });
            }

            // Initialize comments
            const commentCountElement = document.getElementById('comment-count');
            if (commentCountElement) {
                commentCountElement.textContent = '<?php echo $comment_count; ?>';
            }
            
            const commentsContainerElement = document.getElementById('comments-container');
            if (commentsContainerElement) {
                commentsContainerElement.innerHTML = `<?php echo implode('', array_map(function($comment) {
                    return "<div class='comment mb-3'>
                        <div class='comment-header'>
                            <span class='comment-author'>" . htmlspecialchars($comment['author_name']) . "</span>
                            <span class='comment-time'>" . date('M j, Y g:i A', strtotime($comment['created_at'])) . "</span>
                        </div>
                        <div class='comment-text'>" . htmlspecialchars($comment['comment']) . "</div>
                    </div>";
                }, $comments)); ?>`;
            }
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

        // User Search Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userSearch = document.getElementById('userSearch');
            const searchResults = document.getElementById('searchResults');
            const selectedUserId = document.getElementById('selectedUserId');
            const shareSubmitBtn = document.getElementById('shareSubmitBtn');
            let searchTimeout;

            userSearch.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();

                if (searchTerm.length < 1) {
                    searchResults.innerHTML = '';
                    searchResults.classList.remove('active');
                    selectedUserId.value = '';
                    shareSubmitBtn.disabled = true;
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`search_users.php?term=${encodeURIComponent(searchTerm)}`)
                        .then(response => response.json())
                        .then(users => {
                            searchResults.innerHTML = '';
                            if (users.length > 0) {
                                users.forEach(user => {
                                    const div = document.createElement('div');
                                    div.className = 'search-result-item';
                                    div.innerHTML = `
                                        <div class="user-name">${user.full_name}</div>
                                        <div class="user-username">@${user.username}</div>
                                    `;
                                    div.addEventListener('click', () => {
                                        userSearch.value = user.username;
                                        selectedUserId.value = user.id;
                                        searchResults.classList.remove('active');
                                        shareSubmitBtn.disabled = false;
                                    });
                                    searchResults.appendChild(div);
                                });
                                searchResults.classList.add('active');
                            } else {
                                searchResults.innerHTML = '<div class="search-result-item">No users found</div>';
                                searchResults.classList.add('active');
                            }
                        });
                }, 300);
            });

            // Close search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!userSearch.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.remove('active');
                }
            });
        });

        function showMembers() {
            const modal = new bootstrap.Modal(document.getElementById('membersModal'));
            modal.show();
        }

        // Add this new function to handle permission updates
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-permission').forEach(button => {
                button.addEventListener('click', async function() {
                    const shareId = this.dataset.shareId;
                    const currentPermission = this.dataset.currentPermission === '1';
                    const newPermission = !currentPermission;
                    
                    try {
                        const formData = new FormData();
                        formData.append('update_permission', '1');
                        formData.append('share_id', shareId);
                        formData.append('can_edit', newPermission ? '1' : '0');
                        
                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Update button text and data attribute
                            this.textContent = newPermission ? 'Set to view only' : 'Allow editing';
                            this.dataset.currentPermission = newPermission ? '1' : '0';
                            
                            // Update role text
                            const roleSpan = document.getElementById(`role-${shareId}`);
                            if (roleSpan) {
                                roleSpan.textContent = newPermission ? 'Can edit' : 'Can view';
                            }
                            
                            showToast('Permissions updated successfully!', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to update permissions');
                        }
                    } catch (error) {
                        showToast(error.message || 'Error updating permissions', 'error');
                    }
                });
            });
        });

        // Mention functionality
        document.addEventListener('DOMContentLoaded', function() {
            const commentText = document.getElementById('comment-text');
            const mentionPopup = document.createElement('div');
            mentionPopup.className = 'mention-popup';
            commentText.parentNode.appendChild(mentionPopup);
            
            let mentionSearch = '';
            let mentionStartIndex = -1;
            
            commentText.addEventListener('input', async function(e) {
                const text = this.value;
                const caretPos = this.selectionStart;
                
                // Find @ symbol before caret
                let i = caretPos - 1;
                while (i >= 0 && text[i] !== ' ' && text[i] !== '\n') {
                    if (text[i] === '@') {
                        mentionStartIndex = i;
                        mentionSearch = text.substring(i + 1, caretPos);
                        
                        // Position popup below the @mention
                        const coordinates = getCaretCoordinates(this, mentionStartIndex);
                        mentionPopup.style.left = coordinates.left + 'px';
                        mentionPopup.style.top = (coordinates.top + 20) + 'px';
                        mentionPopup.style.display = 'block';
                        
                        try {
                            const response = await fetch(`get_mentions.php?term=${encodeURIComponent(mentionSearch)}&note_id=${<?php echo $note_id; ?>}`);
                            const users = await response.json();
                            
                            if (users.length > 0) {
                                mentionPopup.innerHTML = users.map(user => `
                                    <div class="mention-item" data-user-id="${user.id}" data-username="${user.username}">
                                        <div class="mention-user-info">
                                            <div class="mention-name">${user.full_name}</div>
                                            <div class="mention-username">@${user.username}</div>
                                        </div>
                                    </div>
                                `).join('');
                                mentionPopup.classList.add('active');
                            } else {
                                mentionPopup.innerHTML = '<div class="mention-item no-results">No users found</div>';
                            }
                        } catch (error) {
                            console.error('Error fetching mentions:', error);
                        }
                        return;
                    }
                    i--;
                }
                
                if (mentionStartIndex === -1 || caretPos <= mentionStartIndex) {
                    mentionPopup.style.display = 'none';
                    mentionPopup.classList.remove('active');
                    mentionSearch = '';
                }
            });

            mentionPopup.addEventListener('click', function(e) {
                const mentionItem = e.target.closest('.mention-item');
                if (!mentionItem || !mentionItem.dataset.userId) return;
                
                const text = commentText.value;
                const newText = text.substring(0, mentionStartIndex) +
                               `@[${mentionItem.dataset.username}](${mentionItem.dataset.userId})` +
                               text.substring(commentText.selectionStart);
                               
                commentText.value = newText;
                mentionPopup.style.display = 'none';
                mentionSearch = '';
                mentionStartIndex = -1;
                commentText.focus();
            });
            
            document.addEventListener('click', function(e) {
                if (!mentionPopup.contains(e.target) && e.target !== commentText) {
                    mentionPopup.style.display = 'none';
                }
            });
        });

        // Helper function to get caret coordinates
        function getCaretCoordinates(element, position) {
            const div = document.createElement('div');
            const style = div.style;
            const computed = window.getComputedStyle(element);
            
            style.whiteSpace = 'pre-wrap';
            style.wordWrap = 'break-word';
            style.position = 'absolute';
            style.visibility = 'hidden';
            
            // Copy styles that affect text dimensions
            ['fontFamily', 'fontSize', 'fontWeight', 'paddingLeft', 'paddingTop', 
             'paddingRight', 'paddingBottom', 'width'].forEach(prop => {
                style[prop] = computed[prop];
            });
            
            div.textContent = element.value.substring(0, position);
            document.body.appendChild(div);
            
            const rect = element.getBoundingClientRect();
            const coordinates = {
                top: rect.top + div.offsetHeight - element.scrollTop,
                left: rect.left + div.offsetWidth - element.scrollLeft
            };
            
            document.body.removeChild(div);
            return coordinates;
        }
    </script>
</body>
</html>