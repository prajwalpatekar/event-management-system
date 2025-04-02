<?php
// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to create an event.';
    redirect('login');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $event_date = sanitize($_POST['event_date']);
    $event_time = sanitize($_POST['event_time']);
    $location = sanitize($_POST['location']);
    $capacity = (int)$_POST['capacity'];
    $userId = $_SESSION['user_id'];
    $error = '';
    
    if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || empty($capacity)) {
        $error = 'Please fill in all fields';
    } elseif ($capacity <= 0) {
        $error = 'Capacity must be greater than 0';
    } elseif (strtotime($event_date) < strtotime(date('Y-m-d'))) {
        $error = 'Event date cannot be in the past';
    } else {
        // Insert event
        $query = "INSERT INTO events (title, description, event_date, event_time, location, capacity, created_by, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssii", $title, $description, $event_date, $event_time, $location, $capacity, $userId);
        
        if ($stmt->execute()) {
            $eventId = $conn->insert_id;
            $_SESSION['success_message'] = 'Event created successfully!';
            redirect('event-details&id=' . $eventId);
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create New Event</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?page=create-event" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="invalid-feedback">Please enter an event title.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                        <div class="invalid-feedback">Please enter an event description.</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" min="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">Please select a valid event date.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time" required>
                            <div class="invalid-feedback">Please select a valid event time.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                        <div class="invalid-feedback">Please enter an event location.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                        <div class="invalid-feedback">Please enter a valid capacity (minimum 1).</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Create Event</button>
                        <a href="index.php?page=events" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

