<?php
/**
 * User Registration Page
 */

// Include required files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/Session.php';
require_once 'includes/UserAuth.php';

// Initialize session and auth
$session = Session::getInstance();
$userAuth = new UserAuth();

// Redirect if already logged in
if ($userAuth->isLoggedIn()) {
    redirect('index.php');
}

// Handle registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'country' => $_POST['country'] ?? ''
    ];
    
    // Validate password confirmation
    if ($data['password'] !== $data['confirm_password']) {
        $session->flash('error', 'Passwords do not match');
    } else {
        // Attempt registration
        $result = $userAuth->register($data);
        
        if ($result['success']) {
            $session->flash('success', $result['message']);
            redirect('login.php');
        } else {
            $session->flash('error', $result['message']);
        }
    }
    
    // Redirect back to registration page
    redirect('register.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1200px;
            width: 90%;
            backdrop-filter: blur(10px);
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .register-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        
        .register-body {
            padding: 3rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .login-link {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-floating {
            flex: 1;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #495057;
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        
        .password-strength.weak {
            color: #dc3545;
        }
        
        .password-strength.medium {
            color: #ffc107;
        }
        
        .password-strength.strong {
            color: #28a745;
        }
        
        @media (max-width: 768px) {
            .register-body {
                padding: 2rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-container {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="fas fa-user-plus me-2"></i>Create Account</h1>
            <p>Join <?php echo SITE_NAME; ?> and start shopping today</p>
        </div>
        
        <div class="register-body">
            <?php echo displayFlashMessages(); ?>
            
            <form method="POST" action="">
                <!-- Personal Information -->
                <h3 class="section-title">Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                        <label for="first_name">First Name</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                        <label for="last_name">Last Name</label>
                    </div>
                </div>
                
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
                    <label for="email">Email address</label>
                </div>
                
                <div class="form-floating">
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone number">
                    <label for="phone">Phone number (optional)</label>
                </div>
                
                <!-- Account Security -->
                <h3 class="section-title">Account Security</h3>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                
                <div class="password-strength" id="passwordStrength"></div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <label for="confirm_password">Confirm Password</label>
                </div>
                
                <!-- Address Information -->
                <h3 class="section-title">Address Information</h3>
                
                <div class="form-floating">
                    <textarea class="form-control" id="address" name="address" placeholder="Street address" style="height: 100px;"></textarea>
                    <label for="address">Street address</label>
                </div>
                
                <div class="form-row">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="city" name="city" placeholder="City">
                        <label for="city">City</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="state" name="state" placeholder="State/Province">
                        <label for="state">State/Province</label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Postal code">
                        <label for="postal_code">Postal code</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country">
                        <label for="country">Country</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthElement = document.getElementById('passwordStrength');
            
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (password.length === 0) {
                feedback = '';
                strengthElement.className = 'password-strength';
            } else if (strength <= 2) {
                feedback = 'Weak password';
                strengthElement.className = 'password-strength weak';
            } else if (strength <= 3) {
                feedback = 'Medium password';
                strengthElement.className = 'password-strength medium';
            } else {
                feedback = 'Strong password';
                strengthElement.className = 'password-strength strong';
            }
            
            strengthElement.textContent = feedback;
        });
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Auto-focus on first name field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('first_name').focus();
        });
        
        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn-register');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>