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

$pageTitle = 'About Us';

// Include header after setting up variables
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">About <?php echo getSetting('site_name', SITE_NAME); ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Our Story</h2>
                    <p>Welcome to <?php echo SITE_NAME; ?>, your premier online shopping destination for quality products and exceptional service. Founded with a passion for bringing the best products to our customers, we've grown from a small startup to a trusted e-commerce platform.</p>
                    
                    <h2 class="h4 mb-3 mt-4">Our Mission</h2>
                    <p>Our mission is to provide customers with a seamless shopping experience, offering a curated selection of high-quality products at competitive prices. We believe in building lasting relationships with our customers through trust, transparency, and outstanding service.</p>
                    
                    <h2 class="h4 mb-3 mt-4">What We Offer</h2>
                    <ul>
                        <li>Wide range of quality products across multiple categories</li>
                        <li>Competitive pricing and regular special deals</li>
                        <li>Secure and easy payment options</li>
                        <li>Fast and reliable shipping</li>
                        <li>Excellent customer support</li>
                        <li>Easy returns and refunds policy</li>
                    </ul>
                    
                    <h2 class="h4 mb-3 mt-4">Our Values</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Quality:</strong> We source only the best products for our customers.
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Integrity:</strong> We conduct business with honesty and transparency.
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Customer Focus:</strong> Our customers are at the heart of everything we do.
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Innovation:</strong> We continuously improve our platform and services.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-store fa-3x text-primary mb-3"></i>
                    <h4>Why Shop With Us?</h4>
                    <ul class="list-unstyled text-start">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>100% Secure Shopping</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Quality Guarantee</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Fast Delivery</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>24/7 Customer Support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Easy Returns</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Contact Information</h4>
                    <p><i class="fas fa-phone me-2"></i><?php echo getSetting('contact_phone', '+1-555-0123'); ?></p>
                    <p><i class="fas fa-envelope me-2"></i><?php echo getSetting('contact_email', 'info@ecommerce.com'); ?></p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>123 Shopping Street, Retail City, RC 12345</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>