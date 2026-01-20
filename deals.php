<?php
// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();
$currentUser = $userAuth->getCurrentUser();
$cartCount = $session->getCartCount();

$pageTitle = 'Deals & Offers';

// Include header after setting up variables
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-tag me-2"></i>Hot Deals & Special Offers
            </h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-fire me-2"></i>
                <strong>Limited Time Offers!</strong> Don't miss out on these amazing deals.
            </div>
        </div>
    </div>

    <div class="row">
        <?php
        $db = getDB();
        // Get products with discount price
        $deals = $db->select(
            'products p JOIN categories c ON p.category_id = c.id',
            'p.*, c.name as category_name',
            'p.status = "active" AND p.discount_price > 0',
            [],
            'p.created_at DESC',
            12
        );
        
        if (empty($deals)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-tag fa-3x text-muted mb-3"></i>
                    <h3>No active deals at the moment</h3>
                    <p class="text-muted">Check back soon for special offers and discounts!</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($deals as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 product-card">
                        <div class="position-relative">
                            <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-danger">
                                <i class="fas fa-percentage me-1"></i>SALE
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <div class="mt-auto">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="h5 text-primary mb-0 me-2">
                                        $<?php echo number_format($product['discount_price'] ?? $product['price'], 2); ?>
                                    </span>
                                    <?php if ($product['discount_price'] > 0): ?>
                                        <span class="text-muted text-decoration-line-through">
                                            $<?php echo number_format($product['price'], 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary btn-sm w-100" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Add to cart function
function addToCart(productId) {
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
    btn.disabled = true;
    
    // AJAX call to add to cart
    fetch('assets/ajax/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount(data.cart_count);
            // Show success message
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Update cart count in header
function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

// Show notification
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php require_once 'includes/footer.php'; ?>