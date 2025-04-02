<?php
// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to view your events.';
    redirect('login');
}

$userId = $_SESSION['user_id'];
$events = getUserEvents($conn, $userId);
?>

<h2 class="mb-4">My Events</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="mb-4">
    <a href="index.php?page=create-event" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Create New Event
    </a>
</div>

<?php if (count($events) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Registrations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <?php
                    // Get registration count
                    $query = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $event['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $registrationCount = $result->fetch_assoc()['count'];
                    $stmt->close();
                    ?>
                    <tr>
                        <td><?php echo $event['title']; ?></td>
                        <td><?php echo formatDate($event['event_date']); ?></td>
                        <td><?php echo formatTime($event['event_time']); ?></td>
                        <td><?php echo $event['location']; ?></td>
                        <td><?php echo $event['capacity']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo ($registrationCount >= $event['capacity']) ? 'danger' : 'success'; ?>">
                                <?php echo $registrationCount; ?> / <?php echo $event['capacity']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="index.php?page=edit-event&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit Event">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="index.php?page=event-registrations&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="View Registrations">
                                <i class="fas fa-users"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">You haven't created any events yet.</div>
<?php endif; ?>

<h3 class="mt-5 mb-4">My Event Registrations</h3>

<?php
// Get events the user is registered for
$query = "
    SELECT e.*, r.registration_date 
    FROM events e
    JOIN registrations r ON e.id = r.event_id
    WHERE r.user_id = ?
    ORDER BY e.event_date ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$registeredEvents = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $registeredEvents[] = $row;
    }
}
$stmt->close();
?>

<?php if (count($registeredEvents) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registeredEvents as $event): ?>
                    <tr>
                        <td><?php echo $event['title']; ?></td>
                        <td><?php echo formatDate($event['event_date']); ?></td>
                        <td><?php echo formatTime($event['event_time']); ?></td>
                        <td><?php echo $event['location']; ?></td>
                        <td><?php echo formatDate($event['registration_date']); ?></td>
                        <td>
                            <a href="index.php?page=event-details&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">You haven't registered for any events yet.</div>
<?php endif; ?>

