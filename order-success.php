<?php
/**
 * Order Success Page with PDF Generation
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

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed']) && isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];
    $session->flash('success', 'Order confirmed successfully!');
    redirect('bill.php?order_id=' . $orderId);
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

// Handle text invoice download
if (isset($_GET['download_invoice']) && $_GET['download_invoice'] === '1') {
    if (!empty($order['invoice_path']) && file_exists($order['invoice_path'])) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($order['invoice_path']) . '"');
        header('Content-Length: ' . filesize($order['invoice_path']));
        readfile($order['invoice_path']);
        exit;
    } else {
        // Generate invoice on the fly if file doesn't exist
        $invoiceGenerator = new TextInvoiceGenerator($order, $orderItems);
        $invoiceGenerator->generate();
        exit;
    }
}

// Handle invoice view in browser
if (isset($_GET['view_invoice']) && $_GET['view_invoice'] === '1') {
    $invoiceGenerator = new TextInvoiceGenerator($order, $orderItems);
    $invoiceGenerator->display();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                <h4 class="alert-heading">
                    <i class="fas fa-check-circle me-2"></i>Order Placed Successfully!
                </h4>
                <p class="mb-0">Thank you for your order. Your order has been received and is being processed.</p>
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
                            <p><strong>Order Status:</strong> 
                                <span class="badge bg-<?php echo get_order_status_color($order['order_status']); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </p>
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
                                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
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
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
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
                        <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span>$<?php echo number_format($order['shipping_cost'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($order['tax'], 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary">$<?php echo number_format($order['total_amount'], 2); ?></strong>
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
                    <a href="bill.php?order_id=<?php echo $orderId; ?>" 
                       class="btn btn-success w-100">
                        <i class="fas fa-file-invoice me-2"></i>View Bill
                    </a>
                    <a href="order-success.php?order_id=<?php echo $orderId; ?>&download_invoice=1" 
                       class="btn btn-primary w-100 mt-2">
                        <i class="fas fa-file-alt me-2"></i>Download Invoice (Text)
                    </a>
                    <a href="order-success.php?order_id=<?php echo $orderId; ?>&view_invoice=1" 
                       class="btn btn-outline-primary w-100 mt-2" target="_blank">
                        <i class="fas fa-eye me-2"></i>View Invoice
                    </a>
                    <?php if (!empty($order['invoice_path']) && file_exists($order['invoice_path'])): ?>
                        <a href="<?php echo htmlspecialchars($order['invoice_path']); ?>" 
                           target="_blank" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-file-alt me-2"></i>Open Invoice File
                        </a>
                    <?php endif; ?>
                    <a href="account.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
/**
 * Generate PDF Invoice
 */
function generateOrderPDF($order, $orderItems) {
    // Include TCPDF library (you'll need to install this)
    // For now, we'll use a simple HTML to PDF approach
    
    $html = '
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company { font-size: 24px; font-weight: bold; color: #667eea; }
        .invoice-info { margin-bottom: 30px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .customer-info, .shipping-info { margin-bottom: 30px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .items-table th { background-color: #f8f9fa; font-weight: bold; }
        .totals { text-align: right; margin-top: 20px; }
        .totals div { margin-bottom: 5px; }
        .footer { margin-top: 30px; text-align: center; color: #666; }
    </style>
    
    <div class="header">
        <div class="company">' . SITE_NAME . '</div>
        <div>Invoice / Bill</div>
    </div>
    
    <div class="invoice-info">
        <div class="info-row">
            <div><strong>Invoice Number:</strong> ' . $order['order_number'] . '</div>
            <div><strong>Date:</strong> ' . date('F j, Y', strtotime($order['created_at'])) . '</div>
        </div>
        <div class="info-row">
            <div><strong>Order Status:</strong> ' . ucfirst($order['order_status']) . '</div>
            <div><strong>Payment:</strong> ' . ucfirst($order['payment_status']) . '</div>
        </div>
    </div>
    
    <div class="customer-info">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> ' . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($order['phone']) . '</p>
    </div>
    
    <div class="shipping-info">
        <h3>Shipping Address</h3>
        <p>' . htmlspecialchars($order['address']) . '</p>
        <p>' . htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']) . '</p>
        <p>' . htmlspecialchars($order['country']) . '</p>
    </div>
    
    <h3>Order Items</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($orderItems as $item) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . htmlspecialchars($item['category_name']) . '</td>
                <td>$' . number_format($item['price'], 2) . '</td>
                <td>' . $item['quantity'] . '</td>
                <td>$' . number_format($item['subtotal'], 2) . '</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
    </table>
    
    <div class="totals">
        <div><strong>Subtotal:</strong> $' . number_format($order['subtotal'], 2) . '</div>
        <div><strong>Shipping:</strong> $' . number_format($order['shipping_cost'], 2) . '</div>
        <div><strong>Tax:</strong> $' . number_format($order['tax'], 2) . '</div>
        <div><strong>Total:</strong> $' . number_format($order['total_amount'], 2) . '</div>
    </div>
    
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>' . SITE_NAME . ' | ' . SITE_URL . '</p>
    </div>';
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Invoice-' . $order['order_number'] . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    
    // Convert HTML to PDF (using simple approach for now)
    // In production, you should use a proper PDF library like TCPDF or FPDF
    echo '<html><head><title>Invoice ' . $order['order_number'] . '</title></head><body>' . $html . '</body></html>';
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