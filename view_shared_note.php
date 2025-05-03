<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

include "DB_connection.php";

$user_id = $_SESSION['id'];
$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch note details with share permissions
$query = "SELECT n.*, u.full_name as author_name, ns.can_edit 
          FROM notes n 
          INNER JOIN users u ON n.user_id = u.id 
          INNER JOIN note_shares ns ON n.id = ns.note_id 
          WHERE n.id = ? AND ns.shared_with = ?";

$stmt = $conn->prepare($query);
$stmt->execute([$note_id, $user_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if note doesn't exist or user doesn't have access
if (!$note) {
    header("Location: shared_notes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Note - <?= htmlspecialchars($note['title']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .note-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .note-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .note-meta {
            color: #666;
            font-size: 0.9rem;
        }
        .note-content {
            line-height: 1.6;
        }
        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <div class="main-content">
        <div class="note-container">
            <div class="note-header">
                <h1 class="h3 mb-3"><?= htmlspecialchars($note['title']) ?></h1>
                <div class="note-meta">
                    <p>
                        <i class="fa fa-user me-2"></i>Created by <?= htmlspecialchars($note['author_name']) ?>
                        <br>
                        <i class="fa fa-calendar me-2"></i><?= date('F j, Y', strtotime($note['created_at'])) ?>
                    </p>
                </div>
            </div>
            
            <div class="note-content">
                <?php echo html_entity_decode($note['content']); ?>
            </div>
            
            <div class="action-buttons">
                <a href="shared_notes.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Back to Shared Notes
                </a>
                <?php if ($note['can_edit']): ?>
                    <a href="editnote.php?id=<?= $note['id'] ?>&shared=1" class="btn btn-primary">
                        <i class="fa fa-edit me-2"></i>Edit Note
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
