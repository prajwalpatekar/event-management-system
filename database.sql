-- Create database
CREATE DATABASE IF NOT EXISTS event_management;
USE event_management;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create events table
CREATE TABLE IF NOT EXISTS events (
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
);

-- Create registrations table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_registration (event_id, user_id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@example.com', '$2y$10$8WxYR0AIeUZvr5RXWgH7UuQGqNuGfK.O2yfvL.YWBT.jYJLfVVOYi', 'admin');

-- Insert regular user (password: user123)
INSERT INTO users (name, email, password, role) VALUES 
('Regular User', 'user@example.com', '$2y$10$8WxYR0AIeUZvr5RXWgH7UuQGqNuGfK.O2yfvL.YWBT.jYJLfVVOYi', 'user');

-- Insert sample events
INSERT INTO events (title, description, event_date, event_time, location, capacity, created_by) VALUES
('Web Development Workshop', 'Learn the basics of web development including HTML, CSS, and JavaScript.', 
 DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00:00', 'Tech Hub, Room 101', 30, 1),
 
('Digital Marketing Conference', 'Join industry experts to learn about the latest digital marketing strategies and tools.', 
 DATE_ADD(CURDATE(), INTERVAL 14 DAY), '09:00:00', 'Business Center, Main Hall', 100, 1),
 
('Data Science Bootcamp', 'Intensive one-day bootcamp covering the fundamentals of data science and machine learning.', 
 DATE_ADD(CURDATE(), INTERVAL 21 DAY), '09:30:00', 'Innovation Center', 50, 2),
 
('Networking Mixer', 'Connect with professionals in your industry and build valuable relationships.', 
 DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00', 'Downtown Lounge', 75, 2);

-- Insert sample registrations
INSERT INTO registrations (event_id, user_id) VALUES
(1, 2),
(2, 2),
(3, 1),
(4, 1);

