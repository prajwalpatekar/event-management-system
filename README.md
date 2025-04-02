# Event Management System

A complete PHP-based event management system with MySQL integration using XAMPP.

## Features

- User registration and authentication
- Event creation, editing, and deletion
- Event registration and cancellation
- Admin dashboard with user management
- Responsive design using Bootstrap 5

## Requirements

- XAMPP (Apache, MySQL, PHP)
- PHP 7.4 or higher
- Web browser

## Installation

1. Install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services from the XAMPP Control Panel
3. Clone or download this repository to your XAMPP's htdocs folder (e.g., `C:\xampp\htdocs\event-management`)
4. Open phpMyAdmin by navigating to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
5. Create a new database named `event_management`
6. Import the `database.sql` file to set up the database schema and sample data
7. Open the application in your browser: [http://localhost/event-management](http://localhost/event-management)

## Default Login Credentials

### Admin User
- Email: admin@example.com
- Password: admin123

### Regular User
- Email: user@example.com
- Password: user123

## Directory Structure

- `index.php` - Main entry point
- `config/` - Database configuration
- `includes/` - Common functions and layout files
- `pages/` - Individual page files
- `assets/` - CSS, JavaScript, and images

## Usage

1. Register a new account or log in with the default credentials
2. Browse existing events or create your own
3. Register for events you're interested in
4. Manage your events and registrations from your dashboard
5. Admins can manage all users and events from the admin dashboard

## License

This project is open-source and available under the MIT License.

