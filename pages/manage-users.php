<?php
// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error_message'] = 'You do not have permission to manage users.';
    redirect('home');
}

// Process role change
if (isset($_GET['user']) && isset($_GET['role'])) {
    $userId = $_GET['user'];
    $newRole = $_GET['role'];
    
    if ($newRole != 'admin' && $newRole != 'user') {
        $_SESSION['error_message'] = 'Invalid role specified.';
    } else {
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $newRole, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'User role updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to update user role.';
        }
        
        $stmt->close();
    }
    
    redirect('manage-users');
}

// Process user deletion
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    
    // Check if trying to delete self
    if ($userId == $_SESSION['user_id']) {
        $_SESSION['error_message'] = 'You cannot delete your own account.';
        redirect('manage-users');
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete user's registrations
        $query = "DELETE FROM registrations WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        // Get events created by user
        $query = "SELECT id FROM events WHERE created_by = ?";
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
        $stmt->close();
        
        // Delete registrations for user's events
        foreach ($events as $event) {
            $query = "DELETE FROM registrations WHERE event_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $event['id']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete user's events
        $query = "DELETE FROM events WHERE created_by = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        // Delete user
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_message'] = 'User deleted successfully.';
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = 'Failed to delete user: ' . $e->getMessage();
    }
    
    redirect('manage-users');
}

// Get all users
$users = getAllUsers($conn);
?>

<h2 class="mb-4">Manage Users</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">User List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($user['role'] == 'admin') ? 'danger' : 'success'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <?php if ($user['role'] == 'user'): ?>
                                        <a href="index.php?page=manage-users&user=<?php echo $user['id']; ?>&role=admin" 
                                           class="btn btn-sm btn-primary" 
                                           onclick="return confirm('Are you sure you want to make this user an admin?')">
                                            Make Admin
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?page=manage-users&user=<?php echo $user['id']; ?>&role=user" 
                                           class="btn btn-sm btn-warning" 
                                           onclick="return confirm('Are you sure you want to remove admin privileges from this user?')">
                                            Remove Admin
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="index.php?page=manage-users&delete=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this user? This will also delete all events created by this user and their registrations.')">
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

