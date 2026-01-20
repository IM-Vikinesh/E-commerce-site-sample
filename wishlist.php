<?php
// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Check if user is logged in
if (!$userAuth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user and database connection
$currentUser = $userAuth->getCurrentUser();
$db = getDB();

// Get wishlist items with product and category details
$wishlistItems = $db->select(
    'wishlist w JOIN products p ON w.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id',
    'w.*, p.name as product_name, p.price, p.image, p.status as product_status, c.name as category_name',
    'w.user_id = ? AND p.status = "active"',
    [$currentUser['id']],
    'w.created_at DESC'
);

$pageTitle = 'My Wishlist';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-heart me-2"></i>My Wishlist
            </h1>
        </div>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div class="text-center py-5">
            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
            <h3>Your wishlist is empty</h3>
            <p class="text-muted">Start adding products you love to your wishlist!</p>
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo getProductImage($item['image'], $item['product_name']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($item['category_name']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="h5 text-primary mb-0"><?php echo formatPrice($item['price']); ?></span>
                                <div>
                                    <button class="btn btn-primary btn-sm me-2" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-danger btn-sm" data-wishlist-id="<?php echo $item['id']; ?>" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('[data-product-id].btn-primary');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            if (productId) {
                addToCart(productId, this);
            }
        });
    });
    
    // Remove from wishlist buttons
    const removeButtons = document.querySelectorAll('[data-wishlist-id].btn-danger');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const wishlistId = this.getAttribute('data-wishlist-id');
            const productId = this.getAttribute('data-product-id');
            if (wishlistId && productId) {
                removeFromWishlist(wishlistId, productId, this);
            }
        });
    });
});

function addToCart(productId, button) {
    // Show loading state
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    // Send AJAX request
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
            updateCartCount(data.cart_count);
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
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function removeFromWishlist(wishlistId, productId, button) {
    if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
        return;
    }
    
    // Show loading state
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
    button.disabled = true;
    
    // Send AJAX request
    fetch('assets/ajax/remove-from-wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'wishlist_id=' + wishlistId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the card from DOM
            const card = button.closest('.col-md-6');
            card.style.transition = 'opacity 0.3s';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                // Check if wishlist is now empty
                const cards = document.querySelectorAll('.col-md-6');
                if (cards.length === 0) {
                    location.reload();
                }
            }, 300);
            showNotification('Product removed from wishlist!', 'info');
        } else {
            showNotification(data.message || 'Failed to remove product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

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
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php require_once 'includes/footer.php'; ?>