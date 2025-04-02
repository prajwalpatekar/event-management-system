<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Default page is home
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include 'includes/header.php';

// Navigation
include 'includes/navigation.php';

// Main content
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'logout':
        include 'pages/logout.php';
        break;
    case 'events':
        include 'pages/events.php';
        break;
    case 'event-details':
        include 'pages/event-details.php';
        break;
    case 'create-event':
        include 'pages/create-event.php';
        break;
    case 'edit-event':
        include 'pages/edit-event.php';
        break;
    case 'my-events':
        include 'pages/my-events.php';
        break;
    case 'admin-dashboard':
        include 'pages/admin-dashboard.php';
        break;
    case 'manage-users':
        include 'pages/manage-users.php';
        break;
    case 'event-registrations':
        include 'pages/event-registrations.php';
        break;
    default:
        include 'pages/404.php';
        break;
}

// Footer
include 'includes/footer.php';
?>

