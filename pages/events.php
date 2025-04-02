<h2 class="mb-4">All Events</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="events">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search events..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary" type="submit">Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="index.php?page=events" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <div class="d-flex justify-content-end">
            <div class="btn-group">
                <a href="index.php?page=events&filter=upcoming" class="btn btn-outline-primary <?php echo (!isset($_GET['filter']) || $_GET['filter'] == 'upcoming') ? 'active' : ''; ?>">Upcoming</a>
                <a href="index.php?page=events&filter=past" class="btn btn-outline-primary <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'past') ? 'active' : ''; ?>">Past</a>
                <a href="index.php?page=events" class="btn btn-outline-primary <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'all') ? 'active' : ''; ?>">All</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php
    // Build query based on search and filter
    $query = "SELECT * FROM events WHERE 1=1";
    $params = array();
    $types = "";
    
    // Search
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    // Filter
    if (!isset($_GET['filter']) || $_GET['filter'] == 'upcoming') {
        $query .= " AND event_date >= CURDATE()";
    } elseif (isset($_GET['filter']) && $_GET['filter'] == 'past') {
        $query .= " AND event_date < CURDATE()";
    }
    
    // Order by
    $query .= " ORDER BY event_date ASC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $events = array();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    
    if (count($events) > 0) {
        foreach ($events as $event) {
            ?>
            <div class="col-md-4 mb-4">
                <div class="card event-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $event['title']; ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-calendar-alt"></i> <?php echo formatDate($event['event_date']); ?> at <?php echo formatTime($event['event_time']); ?>
                        </h6>
                        <p class="card-text"><?php echo substr($event['description'], 0, 100); ?>...</p>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge bg-info"><i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?></span>
                            <span class="badge bg-secondary"><i class="fas fa-users"></i> Capacity: <?php echo $event['capacity']; ?></span>
                        </div>
                        <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                    <div class="card-footer text-muted">
                        <?php
                        // Get registration count
                        $countQuery = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
                        $countStmt = $conn->prepare($countQuery);
                        $countStmt->bind_param("i", $event['id']);
                        $countStmt->execute();
                        $countResult = $countStmt->get_result();
                        $registrationCount = $countResult->fetch_assoc()['count'];
                        $countStmt->close();
                        ?>
                        <small><i class="fas fa-user-check"></i> <?php echo $registrationCount; ?> registered</small>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-12"><div class="alert alert-info">No events found.</div></div>';
    }
    
    $stmt->close();
    ?>
</div>

