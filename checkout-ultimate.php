<?php
/**
 * Ultimate Simple Checkout - Guaranteed to Work
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Require login
if (!$userAuth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user
$currentUser = $userAuth->getCurrentUser();

// Handle order submission - SIMPLE AND DIRECT
if ($_POST['place_order'] ?? false) {
    
    try {
        $db = getDB();
        
        // Create minimal order
        $orderData = [
            'user_id' => $currentUser['id'],
            'order_number' => 'ORD-' . date('Ymd-His'),
            'first_name' => $_POST['first_name'] ?? $currentUser['first_name'],
            'last_name' => $_POST['last_name'] ?? $currentUser['last_name'],
            'email' => $_POST['email'] ?? $currentUser['email'],
            'total_amount' => 100.00,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('orders', $orderData);
        
        if ($result) {
            $orderId = $db->lastInsertId();
            
            // Success - redirect immediately
            header('Location: order-confirmation.php?order_id=' . $orderId);
            exit;
            
        } else {
            die("Database insert failed");
        }
        
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Get cart items (simplified)
$cartItems = [];
if (isset($_SESSION['cart'])) {
    $cartItems = $_SESSION['cart'];
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Quick Checkout</h4>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-info">
                        <strong>Test Mode:</strong> This will create a simple order for testing.
                    </div>
                    
                    <form method="POST" action="" style="border: 2px solid #007bff; padding: 20px; border-radius: 8px;">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            This will create a test order with total amount of $100.00
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="place_order" class="btn btn-success btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Place Test Order
                            </button>
                            
                            <a href="checkout-working.php" class="btn btn-outline-secondary">
                                Try Even Simpler Version
                            </a>
                            
                            <a href="products.php" class="btn btn-outline-primary">
                                Continue Shopping
                            </a>
                        </div>
                    </form>
                    
                    <!-- Debug Info -->
                    <div class="mt-4 p-3 bg-light">
                        <h6>Debug Information:</h6>
                        <ul class="mb-0">
                            <li>Request Method: <strong><?php echo $_SERVER['REQUEST_METHOD']; ?></strong></li>
                            <li>User ID: <strong><?php echo $currentUser['id']; ?></strong></li>
                            <li>User Name: <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong></li>
                            <li>Cart Items: <strong><?php echo count($cartItems); ?></strong></li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>