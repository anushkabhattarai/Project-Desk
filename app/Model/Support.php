<?php

/**
 * Get all support tickets
 */
function get_all_support_tickets($conn, $status = 'all', $search = '') {
    $sql = "SELECT * FROM support_tickets";
    $params = [];
    
    // Add status filter if not 'all'
    if ($status != 'all') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
        
        // Add search filter if provided
        if (!empty($search)) {
            $sql .= " AND (subject LIKE ? OR user_id IN (SELECT id FROM users WHERE username LIKE ? OR full_name LIKE ?))";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
    } else if (!empty($search)) {
        // Only search filter
        $sql .= " WHERE subject LIKE ? OR user_id IN (SELECT id FROM users WHERE username LIKE ? OR full_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    // Order by status (open first) and then by creation date (newest first)
    $sql .= " ORDER BY CASE WHEN status = 'open' THEN 0 ELSE 1 END, created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $tickets ? $tickets : [];
}

/**
 * Get support tickets for a specific user
 */
function get_user_support_tickets($conn, $user_id) {
    $sql = "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $tickets ? $tickets : [];
}

/**
 * Get a specific ticket by ID
 */
function get_ticket_by_id($conn, $ticket_id) {
    $sql = "SELECT * FROM support_tickets WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ticket_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a new support ticket
 */
function create_support_ticket($conn, $data) {
    $sql = "INSERT INTO support_tickets (user_id, subject) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$data['user_id'], $data['subject']]);
    
    return $conn->lastInsertId();
}

/**
 * Add a reply to a ticket
 */
function add_ticket_reply($conn, $data) {
    $sql = "INSERT INTO support_replies (ticket_id, user_id, message, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$data['ticket_id'], $data['user_id'], $data['message'], $data['role']]);
    
    // Update the ticket's updated_at timestamp
    $sql = "UPDATE support_tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$data['ticket_id']]);
    
    return $conn->lastInsertId();
}

/**
 * Get all replies for a ticket
 */
function get_ticket_replies($conn, $ticket_id) {
    $sql = "SELECT * FROM support_replies WHERE ticket_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ticket_id]);
    
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $replies ? $replies : [];
}

/**
 * Update ticket status (open/resolved)
 */
function update_ticket_status($conn, $ticket_id, $status) {
    $sql = "UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$status, $ticket_id]);
}

/**
 * Count tickets by status
 */
function count_tickets_by_status($conn, $status) {
    $sql = "SELECT COUNT(*) as count FROM support_tickets WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$status]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
}

/**
 * Count total tickets
 */
function count_total_tickets($conn) {
    $sql = "SELECT COUNT(*) as count FROM support_tickets";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
}

/**
 * Count user's tickets
 */
function count_user_tickets($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM support_tickets WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
}

/**
 * Count user's open tickets
 */
function count_user_open_tickets($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM support_tickets WHERE user_id = ? AND status = 'open'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
} 