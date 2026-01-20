<?php
/**
 * Simple Working Checkout
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

$session = Session::getInstance();
$userAuth = new UserAuth();

// Check if user is logged in
if (!$userAuth->isLoggedIn()) {
    die("Error: You must be logged in to checkout. <a href='login.php'>Login here</a>");
}

// Get current user
$currentUser = $userAuth->getCurrentUser();
$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px;'>";
    echo "<h3>✅ Order Received!</h3>";
    echo "<p>Processing your order...</p>";
    echo "<pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre>";
    echo "</div>";
    
    try {
        // Create a simple test order
        $orderData = [
            'user_id' => $currentUser['id'],
            'order_number' => 'SIMPLE-' . date('YmdHis'),
            'first_name' => $_POST['first_name'] ?? $currentUser['first_name'],
            'last_name' => $_POST['last_name'] ?? $currentUser['last_name'], 
            'email' => $_POST['email'] ?? $currentUser['email'],
            'total_amount' => 100.00,
            'order_status' => 'pending',
            'payment_status' => 'pending'
        ];
        
        if ($db->insert('orders', $orderData)) {
            $orderId = $db->lastInsertId();
            echo "<div style='background: #cce5ff; padding: 20px; margin: 20px;'>";
            echo "<h3>✅ Order Created Successfully!</h3>";
            echo "<p>Order ID: <strong>$orderId</strong></p>";
            echo "<p>Order Number: <strong>{$orderData['order_number']}</strong></p>";
            echo "<p><a href='order-confirmation.php?order_id=$orderId'>Go to Confirmation</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 20px; margin: 20px;'>";
            echo "<h3>❌ Failed to create order</h3>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 20px; margin: 20px;'>";
        echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
        echo "</div>";
    }
}

// Include header
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Simple Checkout</h1>
            
            <div class="alert alert-info">
                <h4>Test Checkout Form</h4>
                <p>This is a simplified checkout form to test if form submission works.</p>
            </div>
            
            <form method="POST" action="" class="border p-4 rounded">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="place_order" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>Place Order
                        </button>
                        <a href="checkout.php" class="btn btn-secondary">Back to Full Checkout</a>
                    </div>
                </div>
            </form>
            
            <div class="mt-4">
                <h5>Debug Information:</h5>
                <ul>
                    <li>Request Method: <?php echo $_SERVER['REQUEST_METHOD']; ?></li>
                    <li>User ID: <?php echo $currentUser['id']; ?></li>
                    <li>User Name: <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></li>
                    <li>Form Method: POST</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>