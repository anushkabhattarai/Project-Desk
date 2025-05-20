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

// Check if user has an active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = :user_id AND status = 'active' AND end_date >= CURRENT_DATE");
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->execute();
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

// If user doesn't have an active subscription and is not an admin, redirect to plans
if (!$subscription && $_SESSION['role'] !== 'admin') {
    header("Location: plans.php");
    exit;
}

$title = "Notes";

// Handle AJAX requests for status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['noteId']) && isset($_POST['status'])) {
        $noteId = intval($_POST['noteId']);
        $status = $_POST['status'];
        
        try {
            // Check if user owns the note or has edit permission
            $sql = "SELECT n.id 
                    FROM notes n 
                    LEFT JOIN note_shares ns ON n.id = ns.note_id AND ns.shared_with = :user_id
                    WHERE n.id = :id 
                    AND (n.user_id = :user_id OR ns.can_edit = 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $noteId);
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update the status
                $updateSql = "UPDATE notes SET status = :status WHERE id = :id";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindParam(':status', $status);
                $updateStmt->bindParam(':id', $noteId);
                
                if ($updateStmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Status updated successfully';
                }
            } else {
                $response['message'] = 'You do not have permission to update this note';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Error updating status';
            error_log("Status update error: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Invalid request parameters';
    }
    
    echo json_encode($response);
    exit;
}

// Function to get user's current plan limits
function getUserPlanLimits($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT p.note_limit, p.private_note_limit, p.is_unlimited 
        FROM plans p 
        INNER JOIN subscriptions s ON p.id = s.plan_id 
        WHERE s.user_id = :user_id AND s.status = 'active' 
        AND s.end_date >= CURRENT_DATE
        ORDER BY s.created_at DESC 
        LIMIT 1
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no active subscription, return basic plan limits
    if (!$plan) {
        return [
            'note_limit' => 10,
            'private_note_limit' => 5,
            'is_unlimited' => 0
        ];
    }
    
    return $plan;
}

// Function to count user's current notes
function countUserNotes($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notes WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Function to count user's current private notes
function countUserPrivateNotes($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notes WHERE user_id = :user_id AND is_private = 1");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Get user's current plan limits
$planLimits = getUserPlanLimits($conn, $_SESSION['id']);
$currentNotes = countUserNotes($conn, $_SESSION['id']);
$currentPrivateNotes = countUserPrivateNotes($conn, $_SESSION['id']);

// Check if user has reached any limits
$noteLimit = $planLimits['note_limit'];
$privateNoteLimit = $planLimits['private_note_limit'];

$canCreateNote = $planLimits['is_unlimited'] || $currentNotes < $noteLimit;
$canCreatePrivateNote = $planLimits['is_unlimited'] || $currentPrivateNotes < $privateNoteLimit;

// Modify the SQL query to fetch notes based on user role
if ($_SESSION['role'] === 'admin') {
    $sql = "SELECT n.*, u.full_name as owner_name,
            CASE 
                WHEN n.user_id = :user_id THEN 1 
                WHEN ns.can_edit = 1 THEN 1
                ELSE 0
            END as can_edit
            FROM notes n
            LEFT JOIN note_shares ns ON n.id = ns.note_id AND ns.shared_with = :user_id
            LEFT JOIN users u ON n.user_id = u.id
            ORDER BY n.pinned DESC, n.created_at DESC";
} else {
    $sql = "SELECT n.*, u.full_name as owner_name,
            CASE 
                WHEN n.user_id = :user_id THEN 1 
                WHEN ns.can_edit = 1 THEN 1
                ELSE 0
            END as can_edit
            FROM notes n
            LEFT JOIN note_shares ns ON n.id = ns.note_id AND ns.shared_with = :user_id
            LEFT JOIN users u ON n.user_id = u.id
            WHERE n.user_id = :user_id OR ns.shared_with = :user_id
            ORDER BY n.pinned DESC, n.created_at DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->execute();
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            font-size: 0.8rem;
            color: #94a3b8;
            margin: 0.25rem 0 0.5rem 0;
            height: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .note-card .timestamp i {
            font-size: 0.9rem;
        }

        .note-card .timestamp .ms-2 {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.1rem 0.5rem;
            background-color: #f1f5f9;
            border-radius: 12px;
            color: #64748b;
        }

        .note-card .timestamp .ms-2 i {
            color: #94a3b8;
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
            padding-top: 0 !important;
        }

        /* Remove margin from header area */
        .d-flex.justify-content-between.align-items-center.mb-4 {
            margin-bottom: 1rem !important;
            padding-top: 0 !important;
        }

        /* Make the breadcrumb more compact */
        .breadcrumb {
            margin-top: 0 !important;
            padding: 0 !important;
        }

        /* Update main content area padding */
        main {
            padding-top: 40px !important;
        }

        /* Add these hierarchical view styles to your existing <style> section */
        .hierarchical-container {
            display: flex;
            flex-direction: column;
        }

        .hierarchy-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .hierarchy-header {
            padding: 10px 16px;
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
            border-bottom: 1px solid #eee;
        }

        .hierarchy-items {
            padding: 16px;
            border-left: 1px dashed #e0e0e0;
            margin-left: 16px;
        }

        .hierarchy-note {
            transition: all 0.2s ease;
            border-left: 3px solid #e0e0e0;
            margin-bottom: 0.5rem;
        }

        .hierarchy-note:hover {
            transform: translateX(3px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .hierarchy-note[data-status="not-started"] {
            border-left-color: #dc2626;
        }

        .hierarchy-note[data-status="pending"] {
            border-left-color: #F59E0B;
        }

        .hierarchy-note[data-status="completed"] {
            border-left-color: #10B981;
        }

        /* Dropdown menu active item style */
        .dropdown-item.active {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 500;
        }

        /* Add Kanban board styles */
        .kanban-column {
            min-height: 500px;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }

        .kanban-card {
            cursor: move;
            transition: all 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 0.5rem;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .kanban-card.dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }

        .kanban-card.completed-note {
            cursor: not-allowed;
            opacity: 0.8;
            background-color: #f8f9fa;
        }

        .kanban-card.completed-note:hover {
            transform: none;
            box-shadow: none;
        }

        .kanban-column .card-header {
            border-bottom: none;
            padding: 1rem;
        }

        .kanban-column .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
        }

        .list-group-item {
            border: 1px solid rgba(0, 0, 0, 0.125);
            padding: 0.75rem 1.25rem;
            margin-bottom: -1px;
            background-color: #fff;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        .list-group-item .status-select-wrapper {
            min-width: 120px;
        }

        .list-group-item .btn-group {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "inc/header.php"; ?>
    <?php include "inc/nav.php" ?>
    
    <!-- Main content area -->
    <main style="margin-left: 250px; margin-top: 60px;">
        <div class="container-fluid px-4 pt-2">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="alert alert-info m-3">
                    <i class="fa fa-info-circle me-2"></i>
                    You are viewing all notes from all users as an administrator
                </div>
            <?php endif; ?>
            
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
                    
                    <!-- View selector -->
                    <div class="btn-group" role="group" aria-label="View options">
                        <button type="button" class="btn btn-outline-secondary view-btn" data-view="kanban">
                            <i class="fa fa-columns"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary view-btn" data-view="hierarchical">
                            <i class="fa fa-sitemap"></i>
                        </button>

                        <button type="button" class="btn btn-outline-secondary view-btn" data-view="cards">
                            <i class="fa fa-th"></i>
                        </button>
                    </div>
                    
                    <?php if ($canCreateNote): ?>
                        <a href="editnote.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> New Note
                        </a>
                    <?php else: ?>
                        <div class="text-end">
                            <button class="btn btn-secondary" disabled>
                                <i class="bi bi-plus-lg"></i> New Note
                            </button>
                            <div class="text-danger mt-1">
                                You have reached your plan's note limit (<?php echo $noteLimit; ?> notes).
                                <a href="plans.php" class="text-primary">Upgrade your plan</a> to create more notes.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes Grid -->
            <div class="row g-3" id="notesGrid">
                <?php if (!$canCreateNote): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            You have reached your plan's note limit (<?php echo $noteLimit; ?> notes).
                            <a href="plans.php" class="alert-link">Upgrade your plan</a> to create more notes.
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$canCreatePrivateNote): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            You have reached your plan's private note limit (<?php echo $privateNoteLimit; ?> private notes).
                            <a href="plans.php" class="alert-link">Upgrade your plan</a> to create more private notes.
                        </div>
                    </div>
                <?php endif; ?>
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
        let currentView = 'hierarchical';
        // Initialize notes from PHP data
        let notes = <?php echo json_encode($notes); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize event listeners
            document.getElementById('searchNotes').addEventListener('input', searchNotes);
            
            // Add view selector listeners
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentView = this.dataset.view;
                    renderNotes();
                });
            });

            document.querySelector('.view-btn[data-view="hierarchical"]').classList.add('active');
            renderNotes();

            // Add drag and drop event listeners
            document.addEventListener('dragstart', function(e) {
                if (e.target.classList.contains('kanban-card')) {
                    // Check if the note is completed
                    const noteId = e.target.dataset.noteId;
                    const note = notes.find(n => n.id == noteId);
                    
                    if (note && note.status === 'completed') {
                        e.preventDefault();
                        return;
                    }
                    
                    e.target.classList.add('dragging');
                    e.dataTransfer.setData('text/plain', noteId);
                }
            });

            document.addEventListener('dragend', function(e) {
                if (e.target.classList.contains('kanban-card')) {
                    e.target.classList.remove('dragging');
                }
            });

            document.addEventListener('dragover', function(e) {
                e.preventDefault();
                const column = e.target.closest('.kanban-column');
                if (column) {
                    const draggingCard = document.querySelector('.dragging');
                    if (draggingCard) {
                        const cards = [...column.querySelectorAll('.kanban-card:not(.dragging)')];
                        const afterCard = cards.reduce((closest, card) => {
                            const box = card.getBoundingClientRect();
                            const offset = e.clientY - box.top - box.height / 2;
                            if (offset < 0 && offset > closest.offset) {
                                return { offset: offset, element: card };
                            } else {
                                return closest;
                            }
                        }, { offset: Number.NEGATIVE_INFINITY }).element;

                        if (afterCard) {
                            column.insertBefore(draggingCard, afterCard);
                        } else {
                            column.appendChild(draggingCard);
                        }
                    }
                }
            });

            document.addEventListener('drop', function(e) {
                e.preventDefault();
                const column = e.target.closest('.kanban-column');
                if (column) {
                    const noteId = e.dataTransfer.getData('text/plain');
                    const newStatus = column.dataset.status;
                    
                    // Check if the note is completed
                    const note = notes.find(n => n.id == noteId);
                    if (note && note.status === 'completed') {
                        return;
                    }
                    
                    updateNoteStatus(noteId, newStatus);
                }
            });
        });

        // Update renderNotes function to handle database notes
        function renderNotes(notesToRender = notes) {
            const notesGrid = document.getElementById('notesGrid');
            notesGrid.innerHTML = '';
            
            if (currentView === 'cards') {
                notesGrid.className = 'row g-3';
            } else if (currentView === 'hierarchical') {
                notesGrid.className = 'hierarchical-container';
                
                const statusGroups = {
                    'not-started': { title: 'Not Started', notes: [], color: '#dc2626' },
                    'pending': { title: 'In Progress', notes: [], color: '#F59E0B' },
                    'completed': { title: 'Completed', notes: [], color: '#10B981' }
                };

                notesToRender.forEach(note => {
                    const status = note.status || 'not-started';
                    const targetGroup = statusGroups[status];
                    if (targetGroup) {
                        targetGroup.notes.push(note);
                    }
                });

                Object.entries(statusGroups).forEach(([status, group]) => {
                    if (group.notes.length > 0) {
                        const hierarchySection = document.createElement('div');
                        hierarchySection.className = 'hierarchy-section mb-4';
                        hierarchySection.innerHTML = `
                            <div class="hierarchy-header" style="border-left: 4px solid ${group.color}; padding-left: 12px;">
                                <h5 class="mb-2 d-flex align-items-center">
                                    <span class="me-2">${group.title}</span>
                                    <span class="badge bg-secondary rounded-pill">${group.notes.length}</span>
                                </h5>
                            </div>
                            <div class="hierarchy-items ps-4">
                                ${group.notes.map(note => `
                                    <div class="card mb-2 hierarchy-note" data-note-id="${note.id}">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">${note.title}</h6>
                                                <div class="d-flex gap-2">
                                                    ${note.can_edit ? `
                                                        <div class="status-select-wrapper">
                                                            <select class="status-select ${note.status}" 
                                                                    onchange="updateNoteStatus(${note.id}, this.value)">
                                                                <option value="not-started" ${note.status === 'not-started' ? 'selected' : ''}>Not Started</option>
                                                                <option value="pending" ${note.status === 'pending' ? 'selected' : ''}>In Progress</option>
                                                                <option value="completed" ${note.status === 'completed' ? 'selected' : ''}>Completed</option>
                                                            </select>
                                                        </div>
                                                    ` : `
                                                        <span class="badge ${note.status}">${note.status === 'pending' ? 'In Progress' : note.status.charAt(0).toUpperCase() + note.status.slice(1)}</span>
                                                    `}
                                                    <a href="editnote.php?id=${note.id}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    ${note.user_id == <?php echo $_SESSION['id']; ?> || '<?php echo $_SESSION['role']; ?>' === 'admin' ? `
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${note.id})">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="small text-muted mt-1 mb-2">
                                                <i class="fa fa-clock-o me-1"></i>${new Date(note.created_at).toLocaleDateString()}
                                                ${note.owner_name ? `<span class="ms-2"><i class="fa fa-user me-1"></i>${note.owner_name}</span>` : ''}
                                            </div>
                                            <p class="card-text small text-muted">${note.content ? note.content.replace(/<[^>]*>/g, '').slice(0, 100) + '...' : ''}</p>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                        notesGrid.appendChild(hierarchySection);
                    }
                });
            } else if (currentView === 'kanban') {
                notesGrid.className = 'row g-3';
                notesGrid.innerHTML = `
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-danger bg-opacity-10 text-danger">
                                <h5 class="mb-0">Not Started</h5>
                            </div>
                            <div class="card-body kanban-column" data-status="not-started">
                                <div class="d-flex flex-column gap-2">
                                    ${renderKanbanCards(notesToRender.filter(note => note.status === 'not-started'))}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-warning bg-opacity-10 text-warning">
                                <h5 class="mb-0">In Progress</h5>
                            </div>
                            <div class="card-body kanban-column" data-status="pending">
                                <div class="d-flex flex-column gap-2">
                                    ${renderKanbanCards(notesToRender.filter(note => note.status === 'pending'))}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-success bg-opacity-10 text-success">
                                <h5 class="mb-0">Completed</h5>
                            </div>
                            <div class="card-body kanban-column" data-status="completed">
                                <div class="d-flex flex-column gap-2">
                                    ${renderKanbanCards(notesToRender.filter(note => note.status === 'completed'))}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            if (notesToRender.length === 0) {
                notesGrid.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fa fa-sticky-note-o fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No notes found</h5>
                        <p class="text-muted mb-3">Get started by creating your first note</p>
                        <button class="btn btn-primary" onclick="window.location.href='editnote.php'">
                            <i class="fa fa-plus me-2"></i>Create Note
                        </button>
                    </div>
                `;
                return;
            }

            if (currentView === 'cards') {
                // Cards view
                notesToRender.forEach(note => {
                    const date = new Date(note.created_at);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric',
                        year: 'numeric'
                    });
                    
                    const truncatedContent = note.content.replace(/<[^>]*>/g, '').slice(0, 150) + '...';
                    const status = note.status || 'not-started';
                    
                    const noteElement = `
                        <div class="col-md-6 col-lg-3">
                            <div class="note-card ${status}" data-note-id="${note.id}">
                                <h3 class="username">${note.title}</h3>
                                <div class="timestamp">
                                    <i class="fa fa-clock-o me-1"></i>${formattedDate}
                                    ${note.owner_name ? `<span class="ms-2"><i class="fa fa-user me-1"></i>${note.owner_name}</span>` : ''}
                                </div>
                                <p class="note-content">${truncatedContent}</p>
                                <div class="note-card-footer">
                                    <div class="status-select-wrapper">
                                        <select class="status-select ${status}" 
                                                onchange="updateNoteStatus(${note.id}, this.value)">
                                            <option value="not-started" ${status === 'not-started' ? 'selected' : ''}>Not Started</option>
                                            <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="completed" ${status === 'completed' ? 'selected' : ''}>Completed</option>
                                        </select>
                                    </div>
                                    <div class="btn-group">
                                        <a href="editnote.php?id=${note.id}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        ${note.user_id == <?php echo $_SESSION['id']; ?> || '<?php echo $_SESSION['role']; ?>' === 'admin' ? `
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${note.id})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    notesGrid.innerHTML += noteElement;
                });
            }
        }

        // Update the renderKanbanCards function to show owner name
        function renderKanbanCards(notes) {
            if (notes.length === 0) {
                return `
                    <div class="text-center text-muted py-3">
                        <i class="fa fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No notes</p>
                    </div>
                `;
            }

            return notes.map(note => {
                const date = new Date(note.created_at);
                const formattedDate = date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    year: 'numeric'
                });
                
                const truncatedContent = note.content ? note.content.replace(/<[^>]*>/g, '').slice(0, 100) + '...' : '';
                const isCompleted = note.status === 'completed';
                
                return `
                    <div class="card kanban-card ${isCompleted ? 'completed-note' : ''}" 
                         data-note-id="${note.id}" 
                         draggable="${!isCompleted}">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2">${note.title}</h6>
                            <p class="card-text small text-muted mb-2">${truncatedContent}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    <i class="fa fa-clock-o me-1"></i>${formattedDate}
                                    ${note.owner_name ? `<span class="ms-2"><i class="fa fa-user me-1"></i>${note.owner_name}</span>` : ''}
                                </div>
                                <div class="btn-group">
                                    <a href="editnote.php?id=${note.id}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    ${note.user_id == <?php echo $_SESSION['id']; ?> || '<?php echo $_SESSION['role']; ?>' === 'admin' ? `
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${note.id})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Delete note
        function deleteNote(noteId) {
            const note = notes.find(n => n.id === noteId);
            if (!note) return;

            if (note.user_id != <?php echo $_SESSION['id']; ?> && 
                '<?php echo $_SESSION['role']; ?>' !== 'admin') {
                alert('You do not have permission to delete this note');
                return;
            }

            if (confirm('Are you sure you want to delete this note?')) {
                notes = notes.filter(n => n.id !== noteId);
                localStorage.setItem('notes', JSON.stringify(notes));
                renderNotes();
                
                // Show a temporary success message
                const messageDiv = document.createElement('div');
                messageDiv.className = 'alert alert-success alert-dismissible fade show';
                messageDiv.innerHTML = `
                    Note deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insert at the top of the container
                const container = document.querySelector('.container-fluid');
                container.insertBefore(messageDiv, container.firstChild);
                
                // Remove the alert after 3 seconds
                setTimeout(() => {
                    messageDiv.remove();
                }, 3000);
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

        // Update note status function
        function updateNoteStatus(noteId, newStatus) {
            fetch('notes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'updateStatus',
                    noteId: noteId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the note's status in our local notes array
                    const noteIndex = notes.findIndex(note => note.id == noteId);
                    if (noteIndex !== -1) {
                        notes[noteIndex].status = newStatus;
                    }

                    // Show success notification
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                    notification.innerHTML = `
                        Status updated successfully
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(notification);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                    
                    // Re-render the current view to reflect the changes
                    renderNotes();
                } else {
                    alert('Failed to update status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the status');
            });
        }

        // Add plan limit checks to the UI
        const canCreateNote = <?php echo json_encode($canCreateNote); ?>;
        const canCreatePrivateNote = <?php echo json_encode($canCreatePrivateNote); ?>;
        
        // Update the "New Note" button state
        const newNoteBtn = document.querySelector('.btn-primary[onclick*="editnote.php"]');
        if (newNoteBtn && !canCreateNote) {
            newNoteBtn.classList.add('disabled');
            newNoteBtn.title = 'You have reached your plan\'s note limit';
        }
    </script>
</body>
</html>