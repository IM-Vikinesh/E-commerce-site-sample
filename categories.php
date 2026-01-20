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

$pageTitle = 'Categories';

// Include header after setting up variables
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">All Categories</h1>
        </div>
    </div>

    <div class="row">
        <?php
        $db = getDB();
        $categories = $db->select('categories', '*', 'status = "active"', [], 'name ASC');
        
        if (empty($categories)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No categories available at the moment.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 category-card">
                        <div class="card-body text-center">
                            <div class="category-icon mb-3">
                                <i class="fas fa-<?php echo get_category_icon($category['name']); ?> fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($category['description'] ?? 'Browse products in this category'); ?></p>
                            <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>