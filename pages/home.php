<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-4">Welcome to Event Management System</h1>
    <p class="lead">Discover, create, and manage events with ease.</p>
    <hr class="my-4">
    <p>Join us today to start organizing and attending amazing events!</p>
    <?php if (!isLoggedIn()): ?>
        <a class="btn btn-primary btn-lg" href="index.php?page=register" role="button">Sign Up Now</a>
    <?php else: ?>
        <a class="btn btn-primary btn-lg" href="index.php?page=events" role="button">Browse Events</a>
    <?php endif; ?>
</div>

<h2 class="mt-5 mb-4">Upcoming Events</h2>

<div class="row">
    <?php
    // Get upcoming events (limit to 6)
    $query = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 6";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($event = $result->fetch_assoc()) {
            ?>
            <div class="col-md-4">
                <div class="card event-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $event['title']; ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-calendar-alt"></i> <?php echo formatDate($event['event_date']); ?> at <?php echo formatTime($event['event_time']); ?>
                        </h6>
                        <p class="card-text"><?php echo substr($event['description'], 0, 100); ?>...</p>
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-info"><i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?></span>
                            <span class="badge bg-secondary"><i class="fas fa-users"></i> Capacity: <?php echo $event['capacity']; ?></span>
                        </div>
                        <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" class="btn btn-primary mt-3">View Details</a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-12"><div class="alert alert-info">No upcoming events found.</div></div>';
    }
    ?>
</div>

<div class="text-center mt-4">
    <a href="index.php?page=events" class="btn btn-outline-primary">View All Events</a>
</div>

