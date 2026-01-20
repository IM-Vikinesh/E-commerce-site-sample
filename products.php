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

// Include header after setting up variables
require_once 'includes/header.php';

$db = getDB();
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get user's wishlist if logged in
$userWishlist = [];
if ($userAuth->isLoggedIn()) {
    $userId = $session->getUserId();
    $wishlistItems = $db->select('wishlist', 'product_id', 'user_id = ?', [$userId]);
    $userWishlist = array_column($wishlistItems, 'product_id');
}

// Helper function to check if product is in wishlist
function isInWishlist($productId, $userWishlist) {
    return in_array($productId, $userWishlist);
}

$where = 'p.status = "active"';
$params = [];

if ($category_id) {
    $where .= ' AND p.category_id = ?';
    $params[] = $category_id;
}

if ($search) {
    $where .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$total_products = $db->count('products p', $where, $params);
$total_pages = ceil($total_products / $per_page);

$products = $db->select(
    'products p JOIN categories c ON p.category_id = c.id',
    'p.*, c.name as category_name',
    $where,
    $params,
    'p.created_at DESC',
    "$offset, $per_page"
);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">All Products</h1>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h3>No products found</h3>
            <p class="text-muted">Try adjusting your search or browse our categories.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 product-card">
                        <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <p class="card-text flex-grow-1"><?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="h5 text-primary mb-0"><?php echo formatPrice($product['price']); ?></span>
                                <div>
                                    <button class="btn btn-wishlist btn-sm me-1 <?php echo isInWishlist($product['id'], $userWishlist) ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="<?php echo isInWishlist($product['id'], $userWishlist) ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                    <button class="btn btn-add-cart btn-sm" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/main.js"></script>

<script>
// Manual initialization for products page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing product page buttons...');
    
    // Manually initialize all product buttons
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const addToCartBtn = card.querySelector('.btn-add-cart');
        const wishlistBtn = card.querySelector('.btn-wishlist');
        
        // Add to cart button
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                console.log('Add to cart clicked:', productId);
                
                // Basic add to cart
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                this.disabled = true;
                
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
                        showNotification(data.message || 'Failed to add product', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                })
                .finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        }
        
        // Wishlist button
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                console.log('Wishlist clicked:', productId);
                
                const icon = this.querySelector('i');
                const isActive = this.classList.contains('active');
                
                // Basic wishlist toggle
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                
                fetch('assets/ajax/toggle-wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('active');
                        if (icon) {
                            icon.classList.toggle('fas');
                            icon.classList.toggle('far');
                        }
                        
                        const action = isActive ? 'removed from' : 'added to';
                        showNotification(`Product ${action} wishlist!`, isActive ? 'info' : 'success');
                    } else {
                        showNotification(data.message || 'Failed to update wishlist', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                })
                .finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        }
    });
});

function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php require_once 'includes/footer.php'; ?>