<?php
/**
 * Bill Page - Customer Bill/Invoice View
 * Shows detailed bill for a specific order
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

// Handle invoice download
if (isset($_GET['download']) && $_GET['download'] === 'invoice') {
    $invoiceGenerator = new TextInvoiceGenerator($order, $orderItems);
    $invoicePath = 'invoices/' . $invoiceGenerator->getFilename();
    
    // Create invoices directory if it doesn't exist
    if (!is_dir('invoices')) {
        mkdir('invoices', 0755, true);
    }
    
    $invoiceGenerator->generate($invoicePath);
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $invoiceGenerator->getFilename() . '"');
    header('Content-Length: ' . filesize($invoicePath));
    readfile($invoicePath);
    exit;
}

// Include header
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-file-invoice me-2"></i>Order Bill
                </h1>
                <div>
                    <a href="?order_id=<?php echo $orderId; ?>&download=invoice" 
                       class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Download Invoice
                    </a>
                    <a href="account.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Account
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="text-primary"><?php echo SITE_NAME; ?></h3>
                    <p class="text-muted mb-1">Online Shopping Store</p>
                    <p class="mb-1"><?php echo SITE_URL; ?></p>
                    <p class="mb-0">Email: <?php echo FROM_EMAIL; ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <h4>INVOICE</h4>
                    <p class="mb-1"><strong>Invoice Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                    <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    <p class="mb-0"><strong>Status:</strong> 
                        <span class="badge bg-<?php echo get_order_status_color($order['order_status']); ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer & Shipping Info -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-shopping-cart me-2"></i>Order Items
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
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

    <!-- Order Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                    <p class="mb-0"><strong>Payment Status:</strong> 
                        <span class="badge bg-<?php echo get_payment_status_color($order['payment_status']); ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Order Summary
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
                        <span>Tax (8%):</span>
                        <span><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['tax'], 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5><strong>Total Amount:</strong></h5>
                        <h5 class="text-primary"><strong><?php echo CURRENCY_SYMBOL . ' ' . number_format($order['total_amount'], 2); ?></strong></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="card mt-4">
        <div class="card-body text-center">
            <p class="mb-1"><strong>Thank you for your order!</strong></p>
            <p class="text-muted mb-0">For any inquiries, please contact us at <?php echo FROM_EMAIL; ?></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
/**
 * Helper function for order status colors
 */
if (!function_exists('get_order_status_color')) {
    function get_order_status_color($status) {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];
        
        return $colors[$status] ?? 'secondary';
    }
}

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