<?php
// Get all notes for a specific user (including notes shared with them)
function get_user_notes($conn, $user_id) {
    $sql = "SELECT n.* FROM notes n 
            WHERE n.owner_id = ? 
            UNION 
            SELECT n.* FROM notes n 
            JOIN note_sharing ns ON n.id = ns.note_id 
            WHERE ns.shared_with_user_id = ? AND n.is_private = 0
            ORDER BY updated_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $user_id]);
    
    if ($stmt->rowCount() >= 1) {
        $notes = $stmt->fetchAll();
        return $notes;
    } else {
        return [];
    }
}

// Get note by ID
function get_note_by_id($conn, $note_id) {
    $sql = "SELECT * FROM notes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id]);
    
    if ($stmt->rowCount() == 1) {
        return $stmt->fetch();
    } else {
        return false;
    }
}

// Create a new note
function create_note($conn, $owner_id, $title, $content, $is_private, $related_task_id = null) {
    $sql = "INSERT INTO notes (owner_id, title, content, is_private, related_task_id, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$owner_id, $title, $content, $is_private, $related_task_id]);
    
    if ($result) {
        return $conn->lastInsertId();
    } else {
        return false;
    }
}

// Update existing note
function update_note($conn, $note_id, $title, $content, $is_private, $related_task_id = null) {
    $sql = "UPDATE notes 
            SET title = ?, content = ?, is_private = ?, related_task_id = ?, updated_at = NOW() 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$title, $content, $is_private, $related_task_id, $note_id]);
}

// Delete note
function delete_note($conn, $note_id) {
    // First delete any sharing records
    $sql1 = "DELETE FROM note_sharing WHERE note_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([$note_id]);
    
    // Then delete the note
    $sql2 = "DELETE FROM notes WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    return $stmt2->execute([$note_id]);
}

// Count user's notes
function count_user_notes($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM notes WHERE owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

// Share note with users
function share_note_with_users($conn, $note_id, $shared_with_user_ids) {
    // First delete existing sharing records
    $sql1 = "DELETE FROM note_sharing WHERE note_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([$note_id]);
    
    // Then insert new sharing records
    $success = true;
    foreach ($shared_with_user_ids as $user_id) {
        $sql2 = "INSERT INTO note_sharing (note_id, shared_with_user_id, shared_at) 
                VALUES (?, ?, NOW())";
        $stmt2 = $conn->prepare($sql2);
        $result = $stmt2->execute([$note_id, $user_id]);
        if (!$result) {
            $success = false;
        }
    }
    
    return $success;
}

// Check if note is shared with user
function is_note_shared_with_user($conn, $note_id, $user_id) {
    $sql = "SELECT * FROM note_sharing WHERE note_id = ? AND shared_with_user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id, $user_id]);
    return $stmt->rowCount() > 0;
}

// Get users with whom a note is shared
function get_note_shared_users($conn, $note_id) {
    $sql = "SELECT * FROM note_sharing WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id]);
    
    if ($stmt->rowCount() >= 1) {
        return $stmt->fetchAll();
    } else {
        return [];
    }
}

// Search notes (for premium users)
function search_notes($conn, $user_id, $search_term) {
    $search_term = "%$search_term%";
    
    $sql = "SELECT n.* FROM notes n 
            WHERE (n.owner_id = ? AND (n.title LIKE ? OR n.content LIKE ?))
            UNION 
            SELECT n.* FROM notes n 
            JOIN note_sharing ns ON n.id = ns.note_id 
            WHERE ns.shared_with_user_id = ? AND n.is_private = 0 AND (n.title LIKE ? OR n.content LIKE ?)
            ORDER BY updated_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $search_term, $search_term, $user_id, $search_term, $search_term]);
    
    if ($stmt->rowCount() >= 1) {
        return $stmt->fetchAll();
    } else {
        return [];
    }
}

// Insert a new note
function insert_note($conn, $data) {
    $sql = "INSERT INTO notes (title, content, owner_id, is_private, related_task_id) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($data);
    
    if ($result) {
        return $conn->lastInsertId();
    } else {
        return false;
    }
}

// Update an existing note
function update_note($conn, $data) {
    $sql = "UPDATE notes 
            SET title = ?, content = ?, is_private = ?, related_task_id = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute($data);
}

// Delete a note
function delete_note($conn, $note_id) {
    // First delete all sharing records
    $sql = "DELETE FROM note_sharing WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id]);
    
    // Then delete the note
    $sql = "DELETE FROM notes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$note_id]);
}

// Share a note with users
function share_note($conn, $note_id, $user_ids) {
    // First remove all existing shares
    $sql = "DELETE FROM note_sharing WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id]);
    
    // No users to share with
    if (empty($user_ids)) {
        return true;
    }
    
    // Add new shares
    $values = [];
    $placeholders = [];
    
    foreach ($user_ids as $user_id) {
        $values[] = $note_id;
        $values[] = $user_id;
        $placeholders[] = "(?, ?)";
    }
    
    $sql = "INSERT INTO note_sharing (note_id, shared_with_user_id) VALUES " . implode(', ', $placeholders);
    $stmt = $conn->prepare($sql);
    return $stmt->execute($values);
}

// Get users a note is shared with
function get_note_shared_users($conn, $note_id) {
    $sql = "SELECT shared_with_user_id FROM note_sharing WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id]);
    
    $users = [];
    while ($row = $stmt->fetch()) {
        $users[] = $row['shared_with_user_id'];
    }
    
    return $users;
}

// Get detailed information about users a note is shared with
function get_note_shared_users_details($conn, $note_id) {
    $sql = "SELECT u.* FROM users u 
            JOIN note_sharing ns ON u.id = ns.shared_with_user_id 
            WHERE ns.note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id]);
    
    if ($stmt->rowCount() >= 1) {
        return $stmt->fetchAll();
    } else {
        return [];
    }
}

// Check if a note is shared with a specific user
function check_note_shared_with_user($conn, $note_id, $user_id) {
    $sql = "SELECT * FROM note_sharing WHERE note_id = ? AND shared_with_user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$note_id, $user_id]);
    
    return $stmt->rowCount() > 0;
}

// Get recent notes for dashboard
function get_recent_user_notes($conn, $user_id, $limit = 3) {
    $sql = "SELECT n.* FROM notes n 
            WHERE n.owner_id = ? 
            UNION 
            SELECT n.* FROM notes n 
            JOIN note_sharing ns ON n.id = ns.note_id 
            WHERE ns.shared_with_user_id = ? AND n.is_private = 0
            ORDER BY updated_at DESC
            LIMIT ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $user_id, $limit]);
    
    if ($stmt->rowCount() >= 1) {
        $notes = $stmt->fetchAll();
        return $notes;
    } else {
        return [];
    }
}