<?php
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

// Process registration
if (isset($_POST['register']) && isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    
    // Check if already registered
    if (isUserRegistered($conn, $userId, $eventId)) {
        $_SESSION['error_message'] = 'You are already registered for this event.';
    } else {
        // Check if event is full
        $query = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $registrationCount = $result->fetch_assoc()['count'];
        
        if ($registrationCount >= $event['capacity']) {
            $_SESSION['error_message'] = 'This event is already at full capacity.';
        } else {
            // Register user
            $query = "INSERT INTO registrations (event_id, user_id, registration_date) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $eventId, $userId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'You have successfully registered for this event.';
            } else {
                $_SESSION['error_message'] = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
    }
    
    // Redirect to refresh page and avoid form resubmission
    redirect('event-details&id=' . $eventId);
}

// Process cancellation
if (isset($_POST['cancel']) && isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    
    // Check if registered
    if (!isUserRegistered($conn, $userId, $eventId)) {
        $_SESSION['error_message'] = 'You are not registered for this event.';
    } else {
        // Cancel registration
        $query = "DELETE FROM registrations WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $eventId, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Your registration has been cancelled.';
        } else {
            $_SESSION['error_message'] = 'Cancellation failed. Please try again.';
        }
        
        $stmt->close();
    }
    
    // Redirect to refresh page and avoid form resubmission
    redirect('event-details&id=' . $eventId);
}

// Get creator info
$query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event['created_by']);
$stmt->execute();
$result = $stmt->get_result();
$creator = $result->fetch_assoc();
$stmt->close();

// Get registration count
$query = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();
$registrationCount = $result->fetch_assoc()['count'];
$stmt->close();

// Check if current user is registered
$isRegistered = false;
if (isLoggedIn()) {
    $isRegistered = isUserRegistered($conn, $_SESSION['user_id'], $eventId);
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="event-details">
            <h2><?php echo $event['title']; ?></h2>
            <div class="mb-4">
                <span class="badge bg-primary me-2"><i class="fas fa-calendar-alt"></i> <?php echo formatDate($event['event_date']); ?></span>
                <span class="badge bg-secondary me-2"><i class="fas fa-clock"></i> <?php echo formatTime($event['event_time']); ?></span>
                <span class="badge bg-info"><i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?></span>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p><?php echo nl2br($event['description']); ?></p>
            </div>
            
            <div class="mb-4">
                <h5>Event Details</h5>
                <ul class="list-group">
                    <li class="list-group-item"><strong>Date:</strong> <?php echo formatDate($event['event_date']); ?></li>
                    <li class="list-group-item"><strong>Time:</strong> <?php echo formatTime($event['event_time']); ?></li>
                    <li class="list-group-item"><strong>Location:</strong> <?php echo $event['location']; ?></li>
                    <li class="list-group-item"><strong>Capacity:</strong> <?php echo $event['capacity']; ?></li>
                    <li class="list-group-item"><strong>Registrations:</strong> <?php echo $registrationCount; ?> / <?php echo $event['capacity']; ?></li>
                    <li class="list-group-item"><strong>Created by:</strong> <?php echo $creator['name']; ?></li>
                </ul>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <?php if ($event['event_date'] >= date('Y-m-d')): ?>
                    <?php if ($isRegistered): ?>
                        <form method="POST" action="index.php?page=event-details&id=<?php echo $eventId; ?>">
                            <button type="submit" name="cancel" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel your registration?')">
                                <i class="fas fa-times-circle"></i> Cancel Registration
                            </button>
                        </form>
                    <?php elseif ($registrationCount < $event['capacity']): ?>
                        <form method="POST" action="index.php?page=event-details&id=<?php echo $eventId; ?>">
                            <button type="submit" name="register" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Register for this Event
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">This event is at full capacity.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">This event has already passed.</div>
                <?php endif; ?>
                
                <?php if ($_SESSION['user_id'] == $event['created_by'] || isAdmin()): ?>
                    <div class="mt-3">
                        <a href="index.php?page=edit-event&id=<?php echo $eventId; ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Event
                        </a>
                        <a href="index.php?page=event-registrations&id=<?php echo $eventId; ?>" class="btn btn-info">
                            <i class="fas fa-users"></i> View Registrations
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="index.php?page=login">login</a> to register for this event.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Registration Status</h5>
            </div>
            <div class="card-body">
                <div class="progress mb-3">
                    <?php $percentage = ($registrationCount / $event['capacity']) * 100; ?>
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%" 
                        aria-valuenow="<?php echo $registrationCount; ?>" aria-valuemin="0" aria-valuemax="<?php echo $event['capacity']; ?>">
                        <?php echo $registrationCount; ?> / <?php echo $event['capacity']; ?>
                    </div>
                </div>
                <p class="card-text">
                    <?php if ($registrationCount >= $event['capacity']): ?>
                        <span class="text-danger"><i class="fas fa-exclamation-circle"></i> This event is full</span>
                    <?php else: ?>
                        <span class="text-success"><i class="fas fa-check-circle"></i> <?php echo $event['capacity'] - $registrationCount; ?> spots remaining</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Share This Event</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="shareEvent('facebook')">
                        <i class="fab fa-facebook"></i> Share on Facebook
                    </button>
                    <button class="btn btn-outline-info" onclick="shareEvent('twitter')">
                        <i class="fab fa-twitter"></i> Share on Twitter
                    </button>
                    <button class="btn btn-outline-secondary" onclick="copyEventLink()">
                        <i class="fas fa-link"></i> Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

