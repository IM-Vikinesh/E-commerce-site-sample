<?php
/**
 * User Account Page
 */

// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';
require_once 'assets/ajax/helper-functions.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Require login
$userAuth->requireLogin();

// Get current user
$currentUser = $userAuth->getCurrentUser();
$pageTitle = 'My Account';

// Include header
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-user-circle me-2"></i>My Account
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-4x text-primary"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    </div>
                    <hr>
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="#orders" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i>Orders
                        </a>
                        <a href="#wishlist" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </a>
                        <a href="#settings" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Profile Section -->
            <div class="card" id="profile-section">
                <div class="card-body">
                    <h5 class="card-title">Profile Information</h5>
                    <p class="text-muted">Manage your account information and preferences.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>First Name:</strong> <?php echo htmlspecialchars($currentUser['first_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($currentUser['last_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($currentUser['phone'] ?? 'Not provided'); ?></p>
                        </div>
                        <div class="col-12">
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($currentUser['address'] ?? 'Not provided'); ?></p>
                        </div>
                    </div>

                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                </div>
            </div>

            <!-- Orders Section -->
            <div class="card d-none" id="orders-section">
                <div class="card-body">
                    <h5 class="card-title">My Orders</h5>
                    <p class="text-muted">View your order history and track shipments.</p>
                    
                    <?php
                    $db = getDB();
                    $orders = $db->select(
                        'orders o LEFT JOIN users u ON o.user_id = u.id', 
                        'o.*, u.first_name, u.last_name', 
                        'o.user_id = ?', 
                        [$currentUser['id']], 
                        'o.created_at DESC', 
                        10
                    );
                    ?>
                    
                    <?php if (count($orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo get_order_status_color($order['order_status']); ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">View Details</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>No orders yet</h5>
                            <p class="text-muted">When you place orders, they will appear here.</p>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wishlist Section -->
            <div class="card d-none" id="wishlist-section">
                <div class="card-body">
                    <h5 class="card-title">My Wishlist</h5>
                    <p class="text-muted">Products you've saved for later.</p>
                    
                    <?php
                    $wishlist = $db->select(
                        'wishlist w JOIN products p ON w.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id', 
                        'w.*, p.name, p.price, p.image, c.name as category_name', 
                        'w.user_id = ? AND p.status = "active"', 
                        [$currentUser['id']], 
                        'w.created_at DESC', 
                        10
                    );
                    ?>
                    
                    <?php if (count($wishlist) > 0): ?>
                        <div class="row">
                            <?php foreach ($wishlist as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <p class="mb-0 text-muted">$<?php echo number_format($item['price'], 2); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100">
                                            <button class="btn btn-sm btn-primary" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromWishlist(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <h5>Your wishlist is empty</h5>
                            <p class="text-muted">Save products you love for later!</p>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Browse Products
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Settings Section -->
            <div class="card d-none" id="settings-section">
                <div class="card-body">
                    <h5 class="card-title">Account Settings</h5>
                    <p class="text-muted">Manage your account preferences and security.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6><i class="fas fa-user-lock me-2"></i>Change Password</h6>
                                    <p class="text-muted small mb-3">Update your account password.</p>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                        Change Password
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6><i class="fas fa-envelope me-2"></i>Email Preferences</h6>
                                    <p class="text-muted small mb-3">Manage your email notifications.</p>
                                    <button class="btn btn-outline-primary btn-sm" disabled>
                                        Coming Soon
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6><i class="fas fa-bell me-2"></i>Notifications</h6>
                                    <p class="text-muted small mb-3">Control your notification settings.</p>
                                    <button class="btn btn-outline-primary btn-sm" disabled>
                                        Coming Soon
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security</h6>
                                    <p class="text-muted small mb-3">Manage your privacy and security options.</p>
                                    <button class="btn btn-outline-primary btn-sm" disabled>
                                        Coming Soon
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profileForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateProfile()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    // Hide all sections
    document.querySelectorAll('#orders-section, #wishlist-section, #settings-section').forEach(section => {
        section.classList.add('d-none');
    });
    
    // Show profile section by default
    document.getElementById('profile-section').classList.remove('d-none');
    
    // Handle tab clicks
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            document.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Hide all sections
            document.querySelectorAll('#profile-section, #orders-section, #wishlist-section, #settings-section').forEach(section => {
                section.classList.add('d-none');
            });
            
            // Show corresponding section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId + '-section');
            if (targetSection) {
                targetSection.classList.remove('d-none');
            }
        });
    });
});

function updateProfile() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    
    fetch('assets/ajax/update-profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function removeFromWishlist(wishlistId) {
    if (confirm('Remove this item from your wishlist?')) {
        fetch('assets/ajax/remove-wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'wishlist_id=' + wishlistId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Item removed from wishlist!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }
}

function changePassword() {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match!', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    
    fetch('assets/ajax/change-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password changed successfully!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
            modal.hide();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
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
        notification.remove();
    }, 3000);
}
</script>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="passwordForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="changePassword()">Change Password</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>