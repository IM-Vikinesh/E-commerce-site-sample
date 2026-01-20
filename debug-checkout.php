<?php
/**
 * Debug Checkout
 */
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

$session = Session::getInstance();
$userAuth = new UserAuth();

echo "<h1>Checkout Debug Page</h1>";

echo "<h2>Request Method:</h2>";
echo "<p>" . $_SERVER['REQUEST_METHOD'] . "</p>";

echo "<h2>POST Data:</h2>";
echo "<pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre>";

echo "<h2>Session Data:</h2>";
echo "<pre>" . htmlspecialchars(print_r($_SESSION, true)) . "</pre>";

if ($userAuth->isLoggedIn()) {
    echo "<h2>Current User:</h2>";
    $currentUser = $userAuth->getCurrentUser();
    echo "<pre>" . htmlspecialchars(print_r($currentUser, true)) . "</pre>";
    
    // Test database connection
    try {
        $db = getDB();
        echo "<h2>Database Connection:</h2>";
        echo "<p>✅ Database connected successfully</p>";
        
        // Test query
        $result = $db->select('orders', 'COUNT(*) as count', '1=1', [], '', '1');
        echo "<h2>Orders Count:</h2>";
        echo "<p>" . $result[0]['count'] . " orders found</p>";
        
    } catch (Exception $e) {
        echo "<h2>Database Error:</h2>";
        echo "<p>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<h2>Authentication:</h2>";
    echo "<p>❌ User not logged in</p>";
}

echo "<h2>Test Form:</h2>";
?>
<form method="POST" action="">
    <input type="hidden" name="debug_test" value="1">
    <button type="submit" class="btn btn-primary">Submit Test Form</button>
</form>

<?php
if (isset($_POST['debug_test'])) {
    echo "<div style='background: green; color: white; padding: 10px; margin: 10px;'>";
    echo "✅ Form submitted successfully!";
    echo "</div>";
}
?>

<a href="checkout.php">Back to Checkout</a>