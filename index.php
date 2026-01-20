<?php
/**
 * Home Page - Main storefront
 */

// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

// Product rating display function (only if not already defined)
if (!function_exists('display_product_rating')) {
    function display_product_rating($productId) {
        $db = getDB();
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ? AND status = 'approved'";
        $db->prepare($sql);
        $db->execute([$productId]);
        $result = $db->fetch();
        
        if ($result && $result['count'] > 0) {
            $rating = round($result['avg_rating'], 1);
            $stars = '';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $rating) {
                    $stars .= '<i class="fas fa-star"></i>';
                } elseif ($i - 0.5 <= $rating) {
                    $stars .= '<i class="fas fa-star-half-alt"></i>';
                } else {
                    $stars .= '<i class="far fa-star"></i>';
                }
            }
            return $stars . ' <small>(' . $result['count'] . ')</small>';
        }
        
        return '<i class="far fa-star"></i> <small>(No reviews)</small>';
    }
}

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Get database connection
$db = getDB();

// Get featured products
$featuredProducts = $db->select('products', '*', 'status = "active" AND featured = 1', [], 'created_at DESC', 8);

// Get new products (latest)
$newProducts = $db->select('products', '*', 'status = "active"', [], 'created_at DESC', 8);

// Get categories
$categories = $db->select('categories', '*', 'status = "active"', [], 'name ASC', 6);

// Get best selling products
$sql = "SELECT p.*, SUM(oi.quantity) as total_sold 
        FROM products p 
        JOIN order_items oi ON p.id = oi.product_id 
        JOIN orders o ON oi.order_id = o.id 
        WHERE p.status = 'active' AND o.payment_status = 'paid' 
        GROUP BY p.id 
        ORDER BY total_sold DESC 
        LIMIT 8";
$db->prepare($sql);
$db->execute();
$bestSellingProducts = $db->fetchAll();

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

// Page title
$pageTitle = "Home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            animation: fadeInUp 1s ease;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s;
            animation-fill-mode: both;
        }
        
        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s;
            animation-fill-mode: both;
        }
        
        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-hero-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: #667eea;
        }
        
        .btn-hero-outline {
            border: 2px solid white;
            color: white;
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        /* Category Cards */
        .category-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            color: inherit;
        }
        
        .category-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }
        
        .category-content {
            padding: 1.5rem;
        }
        
        .category-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .category-count {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Product Cards */
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            height: 250px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .product-content {
            padding: 1.5rem;
        }
        
        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            text-decoration: none;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .product-title:hover {
            color: #667eea;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .product-old-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 1rem;
            margin-left: 0.5rem;
        }
        
        .product-rating {
            color: #ffc107;
            margin-bottom: 1rem;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-add-cart {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-wishlist {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            color: #6c757d;
            padding: 0.75rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-wishlist:hover {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .btn-wishlist.active {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">Welcome to <?php echo SITE_NAME; ?></h1>
                <p class="hero-subtitle">Discover amazing products at unbeatable prices</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn-hero btn-hero-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="#categories" class="btn-hero btn-hero-outline">
                        <i class="fas fa-th-large me-2"></i>Browse Categories
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="py-5" id="categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Find what you're looking for in our popular categories</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                        <div class="category-image">
                            <i class="fas fa-<?php echo get_category_icon($category['name']); ?>"></i>
                        </div>
                        <div class="category-content">
                            <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-count">
                                <?php 
                                $count = $db->count('products', 'category_id = ? AND status = "active"', [$category['id']]);
                                echo $count . ' products';
                                ?>
                            </p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle">Hand-picked products just for you</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if ($product['discount_price']): ?>
                            <span class="product-badge">Sale</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="product-title">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                            <div class="product-price">
                                <?php echo formatPrice($product['discount_price'] ?: $product['price']); ?>
                                <?php if ($product['discount_price']): ?>
                                <span class="product-old-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <?php echo display_product_rating($product['id']); ?>
                            </div>
<div class="product-actions">
                                <button class="btn btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                </button>
                                <button class="btn btn-wishlist <?php echo isInWishlist($product['id'], $userWishlist) ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="<?php echo isInWishlist($product['id'], $userWishlist) ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Best Selling Products Section -->
    <section class="py-5">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Best Selling Products</h2>
                <p class="section-subtitle">Our most popular products this month</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($bestSellingProducts as $product): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if ($product['discount_price']): ?>
                            <span class="product-badge">Sale</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="product-title">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                            <div class="product-price">
                                <?php echo formatPrice($product['discount_price'] ?: $product['price']); ?>
                                <?php if ($product['discount_price']): ?>
                                <span class="product-old-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <?php echo display_product_rating($product['id']); ?>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                </button>
                                <button class="btn btn-wishlist <?php echo isInWishlist($product['id'], $userWishlist) ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="<?php echo isInWishlist($product['id'], $userWishlist) ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- New Products Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">New Arrivals</h2>
                <p class="section-subtitle">Check out our latest products</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($newProducts as $product): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <span class="product-badge" style="background: #28a745;">New</span>
                        </div>
                        <div class="product-content">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="product-title">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                            <div class="product-price">
                                <?php echo formatPrice($product['discount_price'] ?: $product['price']); ?>
                                <?php if ($product['discount_price']): ?>
                                <span class="product-old-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <?php echo display_product_rating($product['id']); ?>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                </button>
                                <button class="btn btn-wishlist <?php echo isInWishlist($product['id'], $userWishlist) ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="<?php echo isInWishlist($product['id'], $userWishlist) ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
   
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
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
        
        // Toggle wishlist function
        function toggleWishlist(productId) {
            const btn = event.target;
            const icon = btn.querySelector('i');
            
            // AJAX call to toggle wishlist
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
                    // Toggle button state
                    btn.classList.toggle('active');
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    
                    // Show message
                    if (data.action === 'added') {
                        showNotification('Product added to wishlist!', 'success');
                    } else {
                        showNotification('Product removed from wishlist!', 'info');
                    }
                } else {
                    showNotification(data.message || 'Failed to update wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
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
</body>
</html>