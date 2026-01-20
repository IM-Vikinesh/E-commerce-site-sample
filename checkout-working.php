<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

$session = Session::getInstance();
$userAuth = new UserAuth();

if (!$userAuth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $userAuth->getCurrentUser();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    
    try {
        $db = getDB();
        
        $orderData = [
            'user_id' => $currentUser['id'],
            'order_number' => 'TEST-' . time(),
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'total_amount' => 100.00,
            'order_status' => 'pending',
            'payment_status' => 'pending'
        ];
        
        if ($db->insert('orders', $orderData)) {
            $orderId = $db->lastInsertId();
            header('Location: order-confirmation.php?order_id=' . $orderId);
            exit;
        } else {
            $error = "Failed to create order";
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Quick Checkout Test</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="place_order" class="btn btn-primary">
                                Place Order
                            </button>
                            <a href="checkout.php" class="btn btn-outline-secondary text-center">
                                Back to Full Checkout
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>