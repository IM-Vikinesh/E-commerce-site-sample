<?php
// Simple test to check wishlist functionality
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/includes/UserAuth.php';

session_start();

echo "<h2>Wishlist Debug Test</h2>";

// Test login
$userAuth = new UserAuth();
if ($userAuth->isLoggedIn()) {
    echo "<p>✅ User is logged in</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    
    // Test database connection
    $db = getDB();
    $wishlistCount = $db->count('wishlist', 'user_id = ?', [$_SESSION['user_id']]);
    echo "<p>Current wishlist count: $wishlistCount</p>";
    
    // Show all wishlist items
    $items = $db->select('wishlist', '*', 'user_id = ?', [$_SESSION['user_id']]);
    echo "<h3>Wishlist Items:</h3>";
    echo "<pre>" . print_r($items, true) . "</pre>";
    
} else {
    echo "<p>❌ User is not logged in</p>";
    echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
}

// Test adding to wishlist
echo "<h3>Test Add to Wishlist:</h3>";
$productId = 1; // Test with product ID 1

echo "<form method='post' action='assets/ajax/toggle-wishlist.php'>";
echo "<input type='hidden' name='product_id' value='$productId'>";
echo "<button type='submit'>Add Product 1 to Wishlist</button>";
echo "</form>";

// Show current session info
echo "<h3>Session Info:</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session Name: " . SESSION_NAME . "</p>";
?>