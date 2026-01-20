<?php
/**
 * Checkout Page - Clean Version
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';
require_once 'includes/TextInvoiceGenerator.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Require login
if (!$userAuth->isLoggedIn()) {
    $session->flash('error', 'Please login to checkout');
    redirect('login.php?redirect=checkout.php');
}

// Get current user
$currentUser = $userAuth->getCurrentUser();

// Include header
require_once 'includes/header.php';

$db = getDB();
$cartItems = [];
$subtotal = 0;
$shipping = 10.00;
$tax = 0;
$grandTotal = 0;

// Get cart items
$cartItems = [];
if (isLoggedIn()) {
    // Logged in user - get from database
    $cartRows = $db->select('cart ci JOIN products p ON ci.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id', 
                            'ci.*, p.name, p.price, p.image, c.name as category_name', 
                            'ci.user_id = ? AND p.status = "active"', 
                            [$currentUser['id']]);
    
    foreach ($cartRows as $item) {
        $itemSubtotal = $item['price'] * $item['quantity'];
        $subtotal += $itemSubtotal;
        $cartItems[] = [
            'product' => [
                'id' => $item['product_id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'image' => $item['image'],
                'category_name' => $item['category_name']
            ],
            'quantity' => $item['quantity'],
            'subtotal' => $itemSubtotal
        ];
    }
} else {
    // Guest user - get from session
    if (isset($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
        foreach ($cart as $product_id => $quantity) {
            $product = $db->selectOne('products p LEFT JOIN categories c ON p.category_id = c.id', 
                                   'p.*, c.name as category_name', 
                                   'p.id = ? AND p.status = "active"', 
                                   [$product_id]);
            
            if ($product) {
                $itemSubtotal = $product['price'] * $quantity;
                $subtotal += $itemSubtotal;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal
                ];
            }
        }
    }
}

// Calculate totals
$tax = $subtotal * 0.08; // 8% tax
$grandTotal = $subtotal + $shipping + $tax;

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    error_log('Checkout: POST request received');
    
    try {
        // Check if cart is empty
        if (empty($cartItems)) {
            $session->flash('error', 'Your cart is empty. Cannot place order.');
            redirect('cart.php');
        }
        
        // Get shipping info
        $shippingData = [
            'first_name' => $_POST['first_name'] ?? $currentUser['first_name'],
            'last_name' => $_POST['last_name'] ?? $currentUser['last_name'],
            'email' => $_POST['email'] ?? $currentUser['email'],
            'phone' => $_POST['phone'] ?? $currentUser['phone'],
            'address' => $_POST['address'] ?? $currentUser['address'],
            'city' => $_POST['city'] ?? $currentUser['city'],
            'state' => $_POST['state'] ?? $currentUser['state'],
            'postal_code' => $_POST['postal_code'] ?? $currentUser['postal_code'],
            'country' => $_POST['country'] ?? $currentUser['country']
        ];
        
        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Get payment method
        $paymentMethod = $_POST['payment_method'] ?? 'cash_on_delivery';
        
        // Create order
        $orderData = [
            'user_id' => $currentUser['id'],
            'order_number' => $orderNumber,
            'first_name' => $shippingData['first_name'],
            'last_name' => $shippingData['last_name'],
            'email' => $shippingData['email'],
            'phone' => $shippingData['phone'],
            'address' => $shippingData['address'],
            'city' => $shippingData['city'],
            'state' => $shippingData['state'],
            'postal_code' => $shippingData['postal_code'],
            'country' => $shippingData['country'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shipping,
            'total_amount' => $grandTotal,
            'payment_method' => $paymentMethod,
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($db->insert('orders', $orderData)) {
            $orderId = $db->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product']['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['product']['price'],
                    'subtotal' => $item['subtotal']
                ];
                $db->insert('order_items', $orderItemData);
                
                // Update product stock
                $db->update('products', 
                           ['stock_quantity' => 'stock_quantity - ' . $item['quantity']], 
                           'id = ?', 
                           [$item['product']['id']]);
            }
            
            // Get complete order data with items for PDF generation
            $orderData = $db->selectOne('orders', '*', 'id = ?', [$orderId]);
            $orderItemsData = $db->select('order_items oi LEFT JOIN products p ON oi.product_id = p.id', 
                                         'oi.*, p.name as product_name', 
                                         'oi.order_id = ?', [$orderId]);
            
            // Generate text invoice
            $invoiceGenerator = new TextInvoiceGenerator($orderData, $orderItemsData);
            $invoicePath = $invoiceGenerator->getInvoicePath();
            $invoiceGenerator->generate($invoicePath);
            
            // Update order with invoice path
            $db->update('orders', ['invoice_path' => $invoicePath], 'id = ?', [$orderId]);
            
            // Clear cart
            if (isLoggedIn()) {
                $db->delete('cart', 'user_id = ?', [$currentUser['id']]);
            } else {
                unset($_SESSION['cart']);
            }
            
            // Store order data in session for confirmation page
            $session->set('pending_order', [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $grandTotal
            ]);
            
            // Redirect to order confirmation page
            $session->flash('success', 'Order placed successfully!');
            redirect('order-confirmation.php?order_id=' . $orderId);
        }
    } catch (Exception $e) {
        $session->flash('error', 'Error placing order: ' . $e->getMessage());
        error_log('Checkout error: ' . $e->getMessage());
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-credit-card me-2"></i>Checkout
            </h1>
        </div>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Add some products to proceed with checkout!</p>
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Order Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo getProductImage($item['product']['image'], $item['product']['name']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product']['name']); ?>" 
                                                         style="width: 40px; height: 40px; object-fit: cover;" class="me-2">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['product']['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($item['product']['category_name']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo CURRENCY_SYMBOL . ' ' . number_format($item['product']['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo CURRENCY_SYMBOL . ' ' . number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-truck me-2"></i>Shipping Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo htmlspecialchars($currentUser['state'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                               value="<?php echo htmlspecialchars($currentUser['postal_code'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" 
                                               value="<?php echo htmlspecialchars($currentUser['country'] ?? 'United States'); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Order Total
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($shipping, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (8%):</span>
                                <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($tax, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary"><?php echo CURRENCY_SYMBOL . ' ' . number_format($grandTotal, 2); ?></strong>
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <div class="form-control bg-light">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <strong>Cash on Delivery</strong>
                                    <small class="text-muted d-block">Pay when you receive your order</small>
                                </div>
                                <input type="hidden" id="payment_method" name="payment_method" value="cash_on_delivery" required>
                            </div>
                            <button type="submit" name="place_order" class="btn btn-primary w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Place Order
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="window.history.back()">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>