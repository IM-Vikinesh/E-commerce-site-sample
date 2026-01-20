<?php
/**
 * Order Confirmation Page
 * Shows order details before final confirmation
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
    redirect('login.php');
}

// Get order details
$orderId = (int)($_GET['order_id'] ?? 0);
$currentUser = $userAuth->getCurrentUser();

if ($orderId <= 0) {
    redirect('account.php');
}

$db = getDB();
$order = $db->selectOne('orders o LEFT JOIN users u ON o.user_id = u.id', 
                       'o.*, u.first_name, u.last_name, u.email, u.phone, u.address, u.city, u.state, u.postal_code, u.country', 
                       'o.id = ? AND o.user_id = ?', 
                       [$orderId, $currentUser['id']]);

if (!$order) {
    $session->flash('error', 'Order not found');
    redirect('account.php');
}

// Get order items
$orderItems = $db->select('order_items oi JOIN products p ON oi.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id', 
                          'oi.*, p.name, p.image, c.name as category_name', 
                          'oi.order_id = ?', 
                          [$orderId]);

// Include header
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h4 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Confirm Your Order
                </h4>
                <p class="mb-0">Please review your order details below before proceeding to generate your bill.</p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Order Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Payment Status:</strong> 
                                <span class="badge bg-<?php echo get_payment_status_color($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <h6>Items Ordered:</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                                <img src="<?php echo getProductImage($item['image'], $item['name']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     style="width: 40px; height: 40px; object-fit: cover;" class="me-2">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . ' ' . number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo CURRENCY_SYMBOL . ' ' . number_format($item['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['shipping_cost'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['tax'], 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary"><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['total_amount'], 2); ?></strong>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-truck me-2"></i>Shipping Address
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                    <p class="mb-1"><?php echo htmlspecialchars($order['address']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']); ?></p>
                    <p class="mb-0"><?php echo htmlspecialchars($order['country']); ?></p>
                    <hr>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <form method="POST" action="order-success.php">
                        <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                        <input type="hidden" name="confirmed" value="1">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-2"></i>Confirm Order & Generate Bill
                        </button>
                        <a href="account.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Account
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
/**
 * Helper function for payment status colors
 */
if (!function_exists('get_payment_status_color')) {
    function get_payment_status_color($status) {
        $colors = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info'
        ];
        
        return $colors[$status] ?? 'secondary';
    }
}
?>