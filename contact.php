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

$pageTitle = 'Contact Us';

// Include header after setting up variables
require_once 'includes/header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Here you would typically save to database or send email
        $success_message = 'Thank you for contacting us! We will get back to you soon.';
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Contact Us</h1>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">Send us a Message</h4>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Contact Information</h4>
                    <div class="mb-3">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <strong>Phone:</strong><br>
                        <?php echo getSetting('contact_phone', '+1-555-0123'); ?>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <strong>Email:</strong><br>
                        <?php echo getSetting('contact_email', 'info@ecommerce.com'); ?>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        <strong>Address:</strong><br>
                        <?php 
                        $address = getSetting('site_address', '123 Shopping Street<br>Retail City, RC 12345<br>United States');
                        echo nl2br(htmlspecialchars($address));
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Business Hours</h4>
                    <div class="mb-2">
                        <strong>Monday - Friday:</strong><br>
                        9:00 AM - 6:00 PM
                    </div>
                    <div class="mb-2">
                        <strong>Saturday:</strong><br>
                        10:00 AM - 4:00 PM
                    </div>
                    <div>
                        <strong>Sunday:</strong><br>
                        Closed
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>