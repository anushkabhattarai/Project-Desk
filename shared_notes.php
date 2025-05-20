<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

include "DB_connection.php";
include "app/Model/Task.php";
include "app/Model/User.php";

$user_id = $_SESSION['id'];
$is_admin = $_SESSION['role'] === 'admin';

// Fetch shared notes with search and sorting
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where_clause = $is_admin ? "WHERE 1=1" : "WHERE ns.shared_with = ?";
$params = $is_admin ? [] : [$user_id];

if ($search) {
    $where_clause .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$order_clause = match($sort) {
    'az' => 'ORDER BY n.title ASC',
    'za' => 'ORDER BY n.title DESC',
    'oldest' => 'ORDER BY ns.created_at ASC',
    default => 'ORDER BY ns.created_at DESC'
};

$query = "SELECT n.*, 
          u_sharer.full_name as shared_by_name, 
          u_recipient.full_name as shared_with_name,
          ns.can_edit, 
          ns.created_at as shared_date 
          FROM notes n 
          INNER JOIN note_shares ns ON n.id = ns.note_id 
          INNER JOIN users u_sharer ON ns.shared_by = u_sharer.id 
          INNER JOIN users u_recipient ON ns.shared_with = u_recipient.id 
          $where_clause $order_clause";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Notes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main-content {
            margin-left: 250px;
            margin-top: 60px; /* Reduced from 70px */
            padding: 15px 20px; /* Adjusted padding */
            transition: margin-left 0.3s ease;
        }

        #checkbox:checked ~ .main-content {
            margin-left: 0;
        }

        .page-header {
            margin-bottom: 1rem; /* Reduced more */
            padding-top: 5px; /* Added small top padding */
        }

        .page-header h4 {
            font-size: 1.75rem;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.25rem; /* Reduced from 0.5rem */
        }

        .page-description {
            color: #718096;
            font-size: 0.95rem;
            margin-top: 0.25rem; /* Reduced space between breadcrumb and description */
            margin-bottom: 0.5rem; /* Added to reduce space below description */
        }

        .card {
            margin-top: 0.5rem; /* Reduced top margin of card */
        }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 0.5rem;
        }

        .note-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.08);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .note-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
        }

        .note-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            background: rgba(13,110,253,0.02);
        }

        .shared-by-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(13,110,253,0.1);
            color: #0d6efd;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .note-title {
            font-size: 1.25rem;
            color: #2d3748;
            margin: 0.5rem 0;
            font-weight: 600;
        }

        .note-meta {
            color: #718096;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .note-content {
            padding: 1.5rem;
            color: #4a5568;
            line-height: 1.6;
            flex-grow: 1;
        }

        .note-footer {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            gap: 0.75rem;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .empty-state img {
            width: 250px;
            margin-bottom: 2rem;
            opacity: 0.8;
        }

        .empty-state h4 {
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #718096;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Loading animation */
        .loading {
            position: relative;
            min-height: 200px;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .breadcrumb {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb-item {
            font-size: 0.875rem;
            color: #64748b;
        }

        .breadcrumb-item a {
            color: #3b82f6;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding-right: 0.5rem;
            color: #94a3b8;
        }
    </style>
</head>
<body class="bg-light">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <div class="main-content">
        <div class="page-header">
            <h4>Shared Notes</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="notes.php">Notes</a></li>
                    <li class="breadcrumb-item active">Shared Notes</li>
                </ol>
            </nav>
            <p class="page-description">View and manage notes that have been shared with you</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div class="search-form flex-grow-1 me-3">
                        <form method="GET" class="d-flex">
                            <div class="input-group">
                                <input type="text" name="search" 
                                       class="form-control" 
                                       placeholder="Search shared notes..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="btn-group">
                        <a href="?sort=newest<?= $search ? '&search='.urlencode($search) : '' ?>" 
                           class="btn <?= $sort == 'newest' ? 'btn-primary' : 'btn-light' ?>">Newest</a>
                        <a href="?sort=oldest<?= $search ? '&search='.urlencode($search) : '' ?>" 
                           class="btn <?= $sort == 'oldest' ? 'btn-primary' : 'btn-light' ?>">Oldest</a>
                        <a href="?sort=az<?= $search ? '&search='.urlencode($search) : '' ?>" 
                           class="btn <?= $sort == 'az' ? 'btn-primary' : 'btn-light' ?>">A-Z</a>
                        <a href="?sort=za<?= $search ? '&search='.urlencode($search) : '' ?>" 
                           class="btn <?= $sort == 'za' ? 'btn-primary' : 'btn-light' ?>">Z-A</a>
                    </div>
                </div>

                <?php if (count($result) > 0): ?>
                    <div class="notes-grid">
                        <?php foreach ($result as $note): ?>
                            <div class="note-card">
                                <div class="note-header">
                                    <div class="shared-by-badge">
                                        <i class="fa fa-share-alt me-2"></i>
                                        Shared by <?= htmlspecialchars($note['shared_by_name']) ?>
                                    </div>
                                    <h3 class="note-title"><?= htmlspecialchars($note['title']) ?></h3>
                                    <div class="note-meta">
                                        <span><i class="fa fa-calendar-o me-1"></i> 
                                            <?= date('M d, Y', strtotime($note['shared_date'])) ?>
                                        </span>
                                        <?php if ($is_admin): ?>
                                            <span class="text-info">
                                                <i class="fa fa-user me-1"></i> 
                                                Shared with: <?= htmlspecialchars($note['shared_with_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($note['can_edit']): ?>
                                            <span class="text-success">
                                                <i class="fa fa-edit me-1"></i> Can edit
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="note-content">
                                    <?php 
                                        // Display first 200 characters of content with HTML preserved
                                        $content = html_entity_decode($note['content']);
                                        echo substr(strip_tags($content), 0, 200);
                                        if(strlen(strip_tags($content)) > 200) echo '...';
                                    ?>
                                </div>

                                <div class="note-footer">
                                    <a href="view_shared_note.php?id=<?= $note['id'] ?>" 
                                       class="btn btn-primary btn-action">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <?php if ($note['can_edit']): ?>
                                        <a href="editnote.php?id=<?= $note['id'] ?>&shared=1" 
                                           class="btn btn-light btn-action">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <img src="img/empty-notes.svg" alt="No Shared Notes" class="mb-4">
                        <h4 class="text-muted mb-2">No Shared Notes Found</h4>
                        <p class="text-muted">When someone shares a note with you, it will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(4)");
        active.classList.add("active");
    </script>
</body>
</html>
