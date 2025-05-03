<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

include "DB_connection.php";

// Check if we're editing an existing note
$isEditing = false;
$note = null;

if (isset($_GET['id'])) {
    $isEditing = true;
    $stmt = $conn->prepare("SELECT * FROM private_notes WHERE note_id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['id']]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        header("Location: private_notes.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['id'];
    
    if ($isEditing) {
        $stmt = $conn->prepare("UPDATE private_notes SET title=?, content=? WHERE note_id=? AND user_id=?");
        $stmt->execute([$title, $content, $_GET['id'], $user_id]);
        header("Location: private_notes.php?success=Note updated successfully");
    } else {
        $stmt = $conn->prepare("INSERT INTO private_notes (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $content]);
        header("Location: private_notes.php?success=Note created successfully");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditing ? 'Edit' : 'Create' ?> Private Note</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: white;
        }

        main {
            margin-left: 250px;
            margin-top: 80px;
            background: white;
            min-height: calc(100vh - 80px);
            padding: 0;
            transition: margin-left 0.3s ease;
        }
        
        .note-form-container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .editor-section {
            background: white;
            border-radius: 0;
            box-shadow: none;
            overflow: hidden;
            border: none;
        }

        .editor-toolbar {
            padding: 15px 40px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8f9fa;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .toolbar-group {
            display: flex;
            gap: 0.25rem;
            padding: 0.25rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
        }

        .toolbar-button {
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            border-radius: 0.25rem;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1rem;
        }

        .toolbar-button:hover {
            background: #f8f9fa;
            color: #0d6efd;
        }

        .toolbar-button.active {
            background: #e9ecef;
            color: #0d6efd;
        }

        .editor-content {
            padding: 40px;
            min-height: calc(100vh - 250px);
            outline: none;
            font-size: 16px;
            line-height: 1.6;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
        }

        .form-control-lg {
            font-size: 18px;
            padding: 15px;
        }

        .btn {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-primary {
            background: #0d6efd;
            border: none;
            box-shadow: 0 2px 4px rgba(13,110,253,0.2);
        }

        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
        }

        .btn-light {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
        }

        .form-control-lg[name="title"] {
            border: none;
            border-bottom: 2px solid #e2e8f0;
            padding: 40px 40px 20px 40px;
            font-size: 32px;
            font-weight: 500;
            background: transparent;
            box-shadow: none;
            margin-bottom: 0;
        }

        .form-control-lg[name="title"]::placeholder {
            color: #94a3b8;
            opacity: 0.7;
            font-weight: 400;
        }

        .form-control-lg[name="title"]:focus {
            border-bottom-color: #0d6efd;
            outline: none;
            box-shadow: none;
        }

        .mb-4:first-child {
            margin-bottom: 0 !important;
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

        .container-fluid {
            padding: 0;
        }

        .action-buttons {
            padding: 20px 40px;
            background: white;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="bg-light">
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <?php include "inc/nav.php" ?>
    
    <main>
        <div class="container-fluid">
            <div class="note-form-container">
                <form action="" method="POST" class="note-editor">
                    <div class="mb-4">
                        <input type="text" class="form-control form-control-lg" 
                               id="title" name="title" required 
                               value="<?= $isEditing ? htmlspecialchars($note['title']) : '' ?>"
                               placeholder="Untitled note...">
                    </div>

                    <div class="section-divider"></div>

                    <div class="editor-section">
                        <input type="hidden" name="content" id="hiddenContent">
                        
                        <div class="editor-toolbar">
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
                        </div>

                        <div class="editor-content" id="content" contenteditable="true" 
                             data-placeholder="Write your note content here..."><?= $isEditing ? htmlspecialchars_decode($note['content']) : '' ?></div>
                    </div>

                    <div class="action-buttons d-flex justify-content-between gap-2">
                        <a href="private_notes.php" class="btn btn-light">
                            <i class="fa fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary" onclick="setContent()">
                            <i class="fa fa-save me-2"></i><?= $isEditing ? 'Update' : 'Save' ?> Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('content');
            const toolbarButtons = document.querySelectorAll('.toolbar-button');

            toolbarButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const command = this.dataset.command;
                    
                    editor.focus();
                    document.execCommand(command, false, null);
                    
                    if (!['undo', 'redo'].includes(command)) {
                        this.classList.toggle('active');
                    }
                    updateToolbarState();
                });
            });

            function updateToolbarState() {
                toolbarButtons.forEach(button => {
                    const command = button.dataset.command;
                    if (['undo', 'redo'].includes(command)) return;
                    
                    if (document.queryCommandState(command)) {
                        button.classList.add('active');
                    } else {
                        button.classList.remove('active');
                    }
                });
            }

            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            editor.addEventListener('input', updateToolbarState);

            editor.addEventListener('paste', function(e) {
                e.preventDefault();
                const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            });

            editor.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key.toLowerCase()) {
                        case 'b':
                            e.preventDefault();
                            document.execCommand('bold', false, null);
                            break;
                        case 'i':
                            e.preventDefault();
                            document.execCommand('italic', false, null);
                            break;
                        case 'u':
                            e.preventDefault();
                            document.execCommand('underline', false, null);
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
                    updateToolbarState();
                }
            });

            updateToolbarState();
        });

        function setContent() {
            const editorContent = document.getElementById('content').innerHTML;
            document.getElementById('hiddenContent').value = editorContent;
        }
    </script>
</body>
</html>
