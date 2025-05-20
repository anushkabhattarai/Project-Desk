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

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $content = $_POST['content'];
                $user_id = $_SESSION['id'];
                
                $stmt = $conn->prepare("INSERT INTO private_notes (user_id, title, content) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $title, $content]);
                header("Location: private_notes.php?success=Note created successfully");
                exit();
                break;
                
            case 'update':
                $note_id = (int)$_POST['note_id'];
                $title = $_POST['title'];
                $content = $_POST['content'];
                $user_id = $_SESSION['id'];
                
                $stmt = $conn->prepare("UPDATE private_notes SET title=?, content=? WHERE note_id=? AND user_id=?");
                $stmt->execute([$title, $content, $note_id, $user_id]);
                header("Location: private_notes.php?success=Note updated successfully");
                exit();
                break;
                
            case 'delete':
                $note_id = (int)$_POST['note_id'];
                $user_id = $_SESSION['id'];
                
                $stmt = $conn->prepare("DELETE FROM private_notes WHERE note_id=? AND user_id=?");
                $stmt->execute([$note_id, $user_id]);
                header("Location: private_notes.php?success=Note deleted successfully");
                exit();
                break;
        }
    }
}

// Handle search and sorting
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$user_id = $_SESSION['id'];

$where_clause = "WHERE user_id = ?";
$params = [$user_id];

if ($search) {
    $where_clause .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$order_clause = match($sort) {
    'az' => 'ORDER BY title ASC',
    'za' => 'ORDER BY title DESC',
    'oldest' => 'ORDER BY created_at ASC',
    default => 'ORDER BY created_at DESC'
};

$stmt = $conn->prepare("SELECT * FROM private_notes $where_clause $order_clause");
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Notes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        main {
            margin-left: 250px;
            margin-top: 60px; /* Changed from 20px */
            background: #f8fafc;
            min-height: calc(100vh - 80px);
            padding: 0 20px;
            transition: margin-left 0.3s ease;
        }

        .card {
            margin-top: 15px;  /* Add margin to card instead */
        }

        .d-flex.justify-content-between.align-items-center {
            padding-top: 15px;  /* Reduced from default padding */
            margin-bottom: 15px !important;  /* Reduced from mb-4 */
        }

        .page-header {
            margin-bottom: 15px;  /* Reduced spacing */
        }

        .top-nav {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            background: white;
            z-index: 1000;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 30px;
        }

        .search-form {
            position: relative;
            max-width: 400px;
        }

        .search-form input {
            padding: 0.6rem 1rem;
            padding-right: 3rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            width: 100%;
            transition: all 0.2s;
        }

        .search-form button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            padding: 0 1rem;
            background: none;
            border: none;
            color: #64748b;
        }

        .sort-options .btn {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sort-options .btn:hover,
        .sort-options .btn.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem 0;
        }

        .note-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .note-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #0d6efd, #0dcaf0);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .note-card:hover::before {
            opacity: 1;
        }

        .note-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.08);
        }

        .note-header {
            margin-bottom: 1rem;
        }

        .note-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .note-date {
            font-size: 0.875rem;
            color: #64748b;
        }

        .note-content {
            color: #475569;
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .note-footer {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .note-card:hover .note-footer {
            opacity: 1;
            transform: translateY(0);
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .modal {
            background: rgba(0,0,0,0.5);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1050;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        }

        .modal input,
        .modal textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .modal input:focus,
        .modal textarea:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
            outline: none;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .empty-state img {
            width: 200px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Private Notes</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item active">Private Notes</li>
                        </ol>
                    </nav>
                </div>
                <a href="create_private_note.php" class="btn btn-primary">
                    <i class="fa fa-plus me-2"></i> Add New Note
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <div class="search-form flex-grow-1">
                            <form method="GET">
                                <input type="text" name="search" placeholder="Search notes..." 
                                       value="<?= htmlspecialchars($search) ?>" class="form-control">
                                <button type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="sort-options">
                            <a href="?sort=newest<?= $search ? '&search='.urlencode($search) : '' ?>" 
                               class="btn <?= $sort == 'newest' ? 'active' : '' ?>">Newest</a>
                            <a href="?sort=oldest<?= $search ? '&search='.urlencode($search) : '' ?>" 
                               class="btn <?= $sort == 'oldest' ? 'active' : '' ?>">Oldest</a>
                            <a href="?sort=az<?= $search ? '&search='.urlencode($search) : '' ?>" 
                               class="btn <?= $sort == 'az' ? 'active' : '' ?>">A-Z</a>
                            <a href="?sort=za<?= $search ? '&search='.urlencode($search) : '' ?>" 
                               class="btn <?= $sort == 'za' ? 'active' : '' ?>">Z-A</a>
                        </div>
                    </div>

                    <?php if (count($result) > 0): ?>
                        <div class="notes-grid">
                            <?php foreach ($result as $note): ?>
                                <div class="note-card">
                                    <div class="note-header">
                                        <h3 class="note-title"><?= htmlspecialchars($note['title']) ?></h3>
                                        <div class="note-date">
                                            <i class="fa fa-calendar-o me-1"></i>
                                            <?= date('M d, Y', strtotime($note['created_at'])) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="note-content">
                                        <?= nl2br(htmlspecialchars($note['content'])) ?>
                                    </div>

                                    <div class="note-footer">
                                        <a href="create_private_note.php?id=<?= $note['note_id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit me-1"></i> Edit
                                        </a>
                                        <button onclick="deleteNote(<?= $note['note_id'] ?>)" 
                                                class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <img src="img/empty-notes.svg" alt="No Notes" class="mb-4">
                            <h4 class="text-muted mb-2">No Notes Found</h4>
                            <p class="text-muted mb-4">Create your first private note to get started</p>
                            <button onclick="showAddNoteForm()" class="btn btn-primary">
                                <i class="fa fa-plus me-2"></i> Create Note
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div id="noteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <form id="noteForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="note_id" id="noteId">
                <div>
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="noteTitle" required>
                </div>
                <div>
                    <label for="content">Content:</label>
                    <textarea name="content" id="noteContent" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(3)");
        active.classList.add("active");

        function showAddNoteForm() {
            document.getElementById('formAction').value = 'add';
            document.getElementById('noteId').value = '';
            document.getElementById('noteTitle').value = '';
            document.getElementById('noteContent').value = '';
            document.getElementById('noteModal').style.display = 'block';
        }

        function editNote(id, title, content) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('noteId').value = id;
            document.getElementById('noteTitle').value = title;
            document.getElementById('noteContent').value = content;
            document.getElementById('noteModal').style.display = 'block';
        }

        function deleteNote(id) {
            if (confirm('Are you sure you want to delete this note?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="note_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        <?php if (isset($_GET['success'])): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= htmlspecialchars($_GET['success']) ?>',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        <?php endif; ?>

        function closeModal() {
            document.getElementById('noteModal').style.display = 'none';
        }
    </script>
</body>
</html>