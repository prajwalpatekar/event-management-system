<?php
// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to edit an event.';
    redirect('login');
}

// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('events');
}

$eventId = $_GET['id'];
$event = getEventById($conn, $eventId);

// Check if event exists
if (!$event) {
    $_SESSION['error_message'] = 'Event not found.';
    redirect('events');
}

// Check if user is the creator or admin
if ($_SESSION['user_id'] != $event['created_by'] && !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to edit this event.';
    redirect('event-details&id=' . $eventId);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $event_date = sanitize($_POST['event_date']);
    $event_time = sanitize($_POST['event_time']);
    $location = sanitize($_POST['location']);
    $capacity = (int)$_POST['capacity'];
    $error = '';
    
    if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || empty($capacity)) {
        $error = 'Please fill in all fields';
    } elseif ($capacity <= 0) {
        $error = 'Capacity must be greater than 0';
    } else {
        // Check if reducing capacity below current registrations
        $query = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $registrationCount = $result->fetch_assoc()['count'];
        
        if ($capacity < $registrationCount) {
            $error = 'Cannot reduce capacity below current registration count (' . $registrationCount . ').';
        } else {
            // Update event
            $query = "UPDATE events SET title = ?, description = ?, event_date = ?, 
                      event_time = ?, location = ?, capacity = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssii", $title, $description, $event_date, $event_time, $location, $capacity, $eventId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Event updated successfully!';
                redirect('event-details&id=' . $eventId);
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            
            $stmt->close();
        }
    }
}

// Process delete request
if (isset($_POST['delete'])) {
    // Check if there are registrations
    $query = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $registrationCount = $result->fetch_assoc()['count'];
    
    if ($registrationCount > 0) {
        // Delete all registrations first
        $query = "DELETE FROM registrations WHERE event_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
    }
    
    // Delete event
    $query = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventId);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Event deleted successfully!';
        redirect('events');
    } else {
        $_SESSION['error_message'] = 'Failed to delete event. Please try again.';
        redirect('event-details&id=' . $eventId);
    }
    
    $stmt->close();
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Edit Event</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?page=edit-event&id=<?php echo $eventId; ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $event['title']; ?>" required>
                        <div class="invalid-feedback">Please enter an event title.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $event['description']; ?></textarea>
                        <div class="invalid-feedback">Please enter an event description.</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                            <div class="invalid-feedback">Please select a valid event date.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time" value="<?php echo $event['event_time']; ?>" required>
                            <div class="invalid-feedback">Please select a valid event time.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo $event['location']; ?>" required>
                        <div class="invalid-feedback">Please enter an event location.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="<?php echo $event['capacity']; ?>" required>
                        <div class="invalid-feedback">Please enter a valid capacity (minimum 1).</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary">Update Event</button>
                            <a href="index.php?page=event-details&id=<?php echo $eventId; ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                        <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                            Delete Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

