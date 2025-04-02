<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

// Create connection to MySQL server (without database)
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS event_management";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("event_management");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create events table
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Events table created successfully<br>";
} else {
    echo "Error creating events table: " . $conn->error . "<br>";
}

// Create registrations table
$sql = "CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_registration (event_id, user_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Registrations table created successfully<br>";
} else {
    echo "Error creating registrations table: " . $conn->error . "<br>";
}

// Check if admin user exists
$sql = "SELECT id FROM users WHERE email = 'admin@example.com'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Insert admin user (password: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES 
            ('Admin User', 'admin@example.com', '$admin_password', 'admin')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created successfully<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
}

// Check if regular user exists
$sql = "SELECT id FROM users WHERE email = 'user@example.com'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Insert regular user (password: user123)
    $user_password = password_hash('user123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES 
            ('Regular User', 'user@example.com', '$user_password', 'user')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Regular user created successfully<br>";
    } else {
        echo "Error creating regular user: " . $conn->error . "<br>";
    }
}

// Get admin and user IDs
$admin_id = 1;
$user_id = 2;

// Check if sample events exist
$sql = "SELECT id FROM events LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Insert sample events
    $sql = "INSERT INTO events (title, description, event_date, event_time, location, capacity, created_by) VALUES
            ('Web Development Workshop', 'Learn the basics of web development including HTML, CSS, and JavaScript.', 
            DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00:00', 'Tech Hub, Room 101', 30, $admin_id),
            
            ('Digital Marketing Conference', 'Join industry experts to learn about the latest digital marketing strategies and tools.', 
            DATE_ADD(CURDATE(), INTERVAL 14 DAY), '09:00:00', 'Business Center, Main Hall', 100, $admin_id),
            
            ('Data Science Bootcamp', 'Intensive one-day bootcamp covering the fundamentals of data science and machine learning.', 
            DATE_ADD(CURDATE(), INTERVAL 21 DAY), '09:30:00', 'Innovation Center', 50, $user_id),
            
            ('Networking Mixer', 'Connect with professionals in your industry and build valuable relationships.', 
            DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00', 'Downtown Lounge', 75, $user_id)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Sample events created successfully<br>";
    } else {
        echo "Error creating sample events: " . $conn->error . "<br>";
    }
}

// Check if sample registrations exist
$sql = "SELECT id FROM registrations LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Insert sample registrations
    $sql = "INSERT INTO registrations (event_id, user_id) VALUES
            (1, $user_id),
            (2, $user_id),
            (3, $admin_id),
            (4, $admin_id)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Sample registrations created successfully<br>";
    } else {
        echo "Error creating sample registrations: " . $conn->error . "<br>";
    }
}

$conn->close();

echo "<br><strong>Installation completed successfully!</strong><br>";
echo "<a href='index.php'>Go to the Event Management System</a>";
?>

