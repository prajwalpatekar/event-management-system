<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Redirect to a specific page
function redirect($page) {
    header("Location: index.php?page=$page");
    exit;
}

// Sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Get all events
function getAllEvents($conn) {
    $query = "SELECT * FROM events ORDER BY event_date DESC";
    $result = $conn->query($query);
    
    $events = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    
    return $events;
}

// Get event by ID
function getEventById($conn, $id) {
    $query = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

// Get events created by a specific user
function getUserEvents($conn, $userId) {
    $query = "SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    
    return $events;
}

// Get registrations for an event
function getEventRegistrations($conn, $eventId) {
    $query = "
        SELECT r.id, r.registration_date, u.name, u.email 
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.event_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $registrations = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $registrations[] = $row;
        }
    }
    
    return $registrations;
}

// Check if user is registered for an event
function isUserRegistered($conn, $userId, $eventId) {
    $query = "SELECT id FROM registrations WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result && $result->num_rows > 0;
}

// Get all users
function getAllUsers($conn) {
    $query = "SELECT id, name, email, role, created_at FROM users";
    $result = $conn->query($query);
    
    $users = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

// Format date for display
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}

// Format time for display
function formatTime($time) {
    return date("g:i A", strtotime($time));
}
?>

