<?php
/**
 * Logout Page
 */

// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Logout user
$userAuth->logout();

// Set flash message
$session->flash('success', 'You have been logged out successfully.');

// Redirect to home page
redirect('index.php');
?>