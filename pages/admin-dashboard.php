<?php
// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin dashboard.';
    redirect('home');
}

// Get statistics
$query = "SELECT COUNT(*) as count FROM events";
$result = $conn->query($query);
$eventCount = $result->fetch_assoc()['count'];

$query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($query);
$userCount = $result->fetch_assoc()['count'];

$query = "SELECT COUNT(*) as count FROM registrations";
$result = $conn->query($query);
$registrationCount = $result->fetch_assoc()['count'];

// Get recent events
$query = "
    SELECT e.*, u.name as creator_name 
    FROM events e
    JOIN users u ON e.created_by = u.id
    ORDER BY e.created_at DESC
    LIMIT 5
";
$result = $conn->query($query);
$recentEvents = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentEvents[] = $row;
    }
}

// Get recent users
$query = "
    SELECT * FROM users
    ORDER BY created_at DESC
    LIMIT 5
";
$result = $conn->query($query);
$recentUsers = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}
?>

<h2 class="mb-4">Admin Dashboard</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="dashboard-stats stats-events">
            <h3><?php echo $eventCount; ?></h3>
            <p>Total Events</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-stats stats-users">
            <h3><?php echo $userCount; ?></h3>
            <p>Registered Users</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-stats stats-registrations">
            <h3><?php echo $registrationCount; ?></h3>
            <p>Event Registrations</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Events</h5>
                <a href="index.php?page=events" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body">
                <?php if (count($recentEvents) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($recentEvents as $event): ?>
                            <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo $event['title']; ?></h5>
                                    <small><?php echo formatDate($event['created_at']); ?></small>
                                </div>
                                <p class="mb-1">Date: <?php echo formatDate($event['event_date']); ?> at <?php echo formatTime($event['event_time']); ?></p>
                                <small>Created by: <?php echo $event['creator_name']; ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No events found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Users</h5>
                <a href="index.php?page=manage-users" class="btn btn-sm btn-light">Manage Users</a>
            </div>
            <div class="card-body">
                <?php if (count($recentUsers) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($recentUsers as $user): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo $user['name']; ?></h5>
                                    <small><?php echo formatDate($user['created_at']); ?></small>
                                </div>
                                <p class="mb-1"><?php echo $user['email']; ?></p>
                                <small>Role: <span class="badge bg-<?php echo ($user['role'] == 'admin') ? 'danger' : 'success'; ?>"><?php echo ucfirst($user['role']); ?></span></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Upcoming Events</h5>
            </div>
            <div class="card-body">
                <?php
                $query = "
                    SELECT e.*, u.name as creator_name, COUNT(r.id) as registration_count
                    FROM events e
                    JOIN users u ON e.created_by = u.id
                    LEFT JOIN registrations r ON e.id = r.event_id
                    WHERE e.event_date >= CURDATE()
                    GROUP BY e.id
                    ORDER BY e.event_date ASC
                    LIMIT 10
                ";
                $result = $conn->query($query);
                $upcomingEvents = array();
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $upcomingEvents[] = $row;
                    }
                }
                ?>
                
                <?php if (count($upcomingEvents) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Creator</th>
                                    <th>Registrations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <tr>
                                        <td><?php echo $event['title']; ?></td>
                                        <td><?php echo formatDate($event['event_date']); ?></td>
                                        <td><?php echo $event['location']; ?></td>
                                        <td><?php echo $event['creator_name']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($event['registration_count'] >= $event['capacity']) ? 'danger' : 'success'; ?>">
                                                <?php echo $event['registration_count']; ?> / <?php echo $event['capacity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=edit-event&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=event-registrations&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-users"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No upcoming events found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

