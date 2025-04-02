<?php
// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = 'You must be logged in to view registrations.';
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
    $_SESSION['error_message'] = 'You do not have permission to view registrations for this event.';
    redirect('event-details&id=' . $eventId);
}

// Process registration removal
if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    $registrationId = $_GET['remove'];
    
    $query = "DELETE FROM registrations WHERE id = ? AND event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $registrationId, $eventId);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Registration removed successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to remove registration.';
    }
    
    $stmt->close();
    redirect('event-registrations&id=' . $eventId);
}

// Get registrations
$registrations = getEventRegistrations($conn, $eventId);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Registrations for "<?php echo $event['title']; ?>"</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?page=event-details&id=<?php echo $eventId; ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Event
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Event Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Date:</strong> <?php echo formatDate($event['event_date']); ?></p>
                <p><strong>Time:</strong> <?php echo formatTime($event['event_time']); ?></p>
                <p><strong>Location:</strong> <?php echo $event['location']; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Capacity:</strong> <?php echo $event['capacity']; ?></p>
                <p><strong>Registrations:</strong> <?php echo count($registrations); ?></p>
                <p><strong>Available Spots:</strong> <?php echo $event['capacity'] - count($registrations); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if (count($registrations) > 0): ?>
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registered Attendees</h5>
            <button class="btn btn-sm btn-light" onclick="printRegistrations()">
                <i class="fas fa-print"></i> Print List
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $registration): ?>
                            <tr>
                                <td><?php echo $registration['name']; ?></td>
                                <td><?php echo $registration['email']; ?></td>
                                <td><?php echo formatDate($registration['registration_date']); ?></td>
                                <td>
                                    <a href="index.php?page=event-registrations&id=<?php echo $eventId; ?>&remove=<?php echo $registration['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to remove this registration?')">
                                        <i class="fas fa-user-minus"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function printRegistrations() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Registrations for ${<?php echo json_encode($event['title']); ?>}</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    h2, h3 { margin-bottom: 10px; }
                </style>
            </head>
            <body>
                <h2>Registrations for "${<?php echo json_encode($event['title']); ?>}"</h2>
                <h3>Date: ${<?php echo json_encode(formatDate($event['event_date'])); ?>} at ${<?php echo json_encode(formatTime($event['event_time'])); ?>}</h3>
                <h3>Location: ${<?php echo json_encode($event['location']); ?>}</h3>
                <p>Total Registrations: ${<?php echo json_encode(count($registrations)); ?>}</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${<?php 
                            $rows = '';
                            foreach ($registrations as $reg) {
                                $rows .= "<tr><td>{$reg['name']}</td><td>{$reg['email']}</td><td>" . formatDate($reg['registration_date']) . "</td></tr>";
                            }
                            echo json_encode($rows);
                        ?>}
                    </tbody>
                </table>
                <p style="margin-top: 30px;">Generated on ${new Date().toLocaleString()}</p>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }
    </script>
<?php else: ?>
    <div class="alert alert-info">No registrations found for this event.</div>
<?php endif; ?>

