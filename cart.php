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
$cartCount = getCartCount();

$pageTitle = 'Shopping Cart';

function getCartItems() {
    global $userAuth;
    $items = [];
    
    if (isLoggedIn()) {
        // Logged in user - get from database
        $db = getDB();
        $cartItems = $db->select('cart ci JOIN products p ON ci.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id', 
                               'ci.*, p.name, p.price, p.image, c.name as category_name', 
                               'ci.user_id = ? AND p.status = "active"', 
                               [$userAuth->getCurrentUser()['id']]);
        
        foreach ($cartItems as $item) {
            $items[] = [
                'product' => [
                    'id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'category_name' => $item['category_name']
                ],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity']
            ];
        }
    } else {
        // Guest user - get from session
        if (!isset($_SESSION['cart'])) {
            return [];
        }
        
        $cart = $_SESSION['cart'];
        $db = getDB();
        
        foreach ($cart as $product_id => $quantity) {
            $product = $db->selectOne('products p LEFT JOIN categories c ON p.category_id = c.id', 
                                   'p.*, c.name as category_name', 
                                   'p.id = ? AND p.status = "active"', 
                                   [$product_id]);
            
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product['price'] * $quantity
                ];
            }
        }
    }
    
    return $items;
}

$cartItems = getCartItems();
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['subtotal'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isLoggedIn()) {
        // Update logged in user's cart in database
        $userId = $userAuth->getCurrentUser()['id'];
        $db = getDB();
        
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $quantity = (int)$quantity;
            $product_id = (int)$product_id;
            
            if ($quantity <= 0) {
                // Remove item
                $db->delete('cart', 'user_id = ? AND product_id = ?', [$userId, $product_id]);
            } else {
                // Update quantity
                $db->update('cart', ['quantity' => $quantity], 'user_id = ? AND product_id = ?', [$userId, $product_id]);
            }
        }
    } else {
        // Update guest user's cart in session
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
    }
    
    // Redirect without including header first
    header('Location: cart.php');
    exit;
}

if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    
    if (isLoggedIn()) {
        // Remove from database cart
        $userId = $userAuth->getCurrentUser()['id'];
        $db = getDB();
        $db->delete('cart', 'user_id = ? AND product_id = ?', [$userId, $product_id]);
    } else {
        // Remove from session cart
        unset($_SESSION['cart'][$product_id]);
    }
    
    // Redirect without including header first
    header('Location: cart.php');
    exit;
}

// Include header after handling redirects
require_once 'includes/header.php';
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
            </h1>
        </div>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Add some products to get started!</p>
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo getProductImage($item['product']['image'], $item['product']['name']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['product']['name']); ?>" 
                                                             style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['product']['name']); ?></h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($item['product']['category_name']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>$<?php echo number_format($item['product']['price'], 2); ?></td>
                                                <td>
                                                    <input type="number" name="quantities[<?php echo $item['product']['id']; ?>]" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           min="0" max="99" class="form-control form-control-sm" style="width: 80px;">
                                                </td>
                                                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                                <td>
                                                    <a href="cart.php?remove=<?php echo $item['product']['id']; ?>" 
                                                       class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                </a>
                                <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-2"></i>Update Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax:</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total:</strong>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function proceedToCheckout() {
    alert('Checkout functionality would be implemented here!');
}
</script>

<?php require_once 'includes/footer.php'; ?>