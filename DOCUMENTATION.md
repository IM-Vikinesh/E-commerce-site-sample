# PHP E-Commerce Website - Complete Documentation

## Table of Contents

1. [Project Overview](#project-overview)
2. [Features](#features)
3. [Technical Architecture](#technical-architecture)
4. [Database Schema](#database-schema)
5. [File Structure](#file-structure)
6. [API Documentation](#api-documentation)
7. [Security Features](#security-features)
8. [Customization Guide](#customization-guide)
9. [Deployment Guide](#deployment-guide)
10. [Troubleshooting](#troubleshooting)

## Project Overview

This PHP E-Commerce Website is a complete, production-ready online shopping platform built with modern PHP, MySQL, and Bootstrap. It provides a professional shopping experience for customers and comprehensive management tools for administrators.

### Key Highlights
- **Modern UI**: Responsive design inspired by leading e-commerce platforms
- **Secure Architecture**: Robust authentication and data protection
- **Scalable Design**: Clean MVC-style architecture for easy maintenance
- **Feature Rich**: Complete shopping cart, checkout, and order management
- **Admin Panel**: Comprehensive dashboard with analytics and management tools
- **SEO Friendly**: Clean URLs and optimized meta tags

## Features

### Frontend Features

#### User Experience
- **Responsive Design**: Works perfectly on all devices (mobile, tablet, desktop)
- **Modern UI**: Clean, professional interface with smooth animations
- **Fast Loading**: Optimized for speed and performance
- **Intuitive Navigation**: Easy-to-use menu and search functionality

#### Shopping Features
- **Product Catalog**: Browse products by category, search, and filter
- **Product Details**: Detailed product pages with images, descriptions, reviews
- **Shopping Cart**: Real-time cart management with quantity updates
- **Wishlist**: Save favorite products for later
- **Checkout**: Multi-step checkout process with multiple payment options
- **Order Tracking**: View order history and track shipments
- **Product Reviews**: Rate and review purchased products
- **Account Management**: Update profile, view orders, manage addresses

#### Search & Discovery
- **Advanced Search**: Search by product name, category, or description
- **Product Filtering**: Filter by price range, category, rating
- **Sorting Options**: Sort by price, popularity, newest, rating
- **Product Recommendations**: Related products and best sellers

### Admin Panel Features

#### Dashboard
- **Sales Analytics**: Real-time sales data and charts
- **Order Statistics**: Overview of order status and trends
- **Customer Insights**: Customer registration and activity data
- **Product Performance**: Best-selling and low-stock alerts

#### Product Management
- **CRUD Operations**: Create, read, update, delete products
- **Image Upload**: Multiple product images with gallery view
- **Inventory Management**: Stock tracking and low-stock alerts
- **Pricing Control**: Regular pricing and discount management
- **Product Categories**: Hierarchical category organization
- **Bulk Operations**: Mass update and delete capabilities

#### Order Management
- **Order Processing**: View and update order status
- **Customer Communication**: Send order notifications
- **Shipping Management**: Track shipments and update delivery status
- **Payment Processing**: Monitor payment status and handle refunds
- **Order History**: Complete order audit trail

#### Customer Management
- **Customer Database**: View and manage customer accounts
- **Order History**: Customer purchase history and preferences
- **Customer Support**: Access customer contact information
- **Account Management**: Enable/disable customer accounts

#### System Management
- **User Management**: Create and manage admin accounts
- **Role-Based Access**: Different permission levels for admins
- **Settings Configuration**: Site-wide settings and preferences
- **Review Management**: Moderate and manage product reviews
- **System Logs**: Activity logging and error tracking

## Technical Architecture

### Backend Architecture

#### PHP Framework
- **Core PHP**: Pure PHP without framework dependencies
- **MVC Pattern**: Model-View-Controller architecture
- **Object-Oriented**: Class-based design for maintainability
- **Autoloading**: Automatic class loading for better performance

#### Database Layer
- **MySQL**: Relational database for data storage
- **PDO**: PHP Data Objects for secure database access
- **Prepared Statements**: SQL injection protection
- **Connection Pooling**: Efficient database connection management

#### Security Layer
- **Password Hashing**: bcrypt for secure password storage
- **Session Management**: Secure session handling with regeneration
- **CSRF Protection**: Cross-site request forgery prevention
- **Input Validation**: Comprehensive data sanitization
- **SQL Injection Prevention**: Parameterized queries

### Frontend Architecture

#### HTML/CSS/JavaScript
- **HTML5**: Semantic markup for accessibility
- **Bootstrap 5**: Responsive CSS framework
- **Custom CSS**: Optimized styles for unique design
- **Vanilla JavaScript**: Fast, dependency-free scripting
- **AJAX**: Asynchronous data fetching

#### Responsive Design
- **Mobile-First**: Progressive enhancement approach
- **Flexible Grid**: Bootstrap grid system
- **Touch-Friendly**: Optimized for touch devices
- **Performance**: Optimized assets and lazy loading

## Database Schema

### Core Tables

#### Users Table
```sql
users (
    id, first_name, last_name, email, password, 
    phone, address, city, state, postal_code, 
    country, email_verified, verification_token, 
    status, created_at, updated_at
)
```

#### Products Table
```sql
products (
    id, category_id, name, description, price, 
    discount_price, sku, stock_quantity, min_stock_level,
    weight, dimensions, image, images, status, 
    featured, created_at, updated_at
)
```

#### Orders Table
```sql
orders (
    id, user_id, order_number, subtotal, tax_amount,
    shipping_amount, discount_amount, total_amount,
    payment_method, payment_status, order_status,
    shipping_address, billing_address, notes,
    tracking_number, shipped_at, delivered_at,
    created_at, updated_at
)
```

#### Order Items Table
```sql
order_items (
    id, order_id, product_id, quantity, price,
    total, product_name, product_image, created_at
)
```

#### Categories Table
```sql
categories (
    id, name, description, image, status,
    created_at, updated_at
)
```

#### Admins Table
```sql
admins (
    id, username, email, password, first_name,
    last_name, role, permissions, last_login,
    status, created_at, updated_at
)
```

#### Supporting Tables
- `cart`: Shopping cart items
- `wishlist`: User wishlists
- `reviews`: Product reviews and ratings
- `settings`: System configuration

## File Structure

```
ecommerce/
├── admin/                          # Admin Panel
│   ├── css/                       # Admin Stylesheets
│   │   └── admin.css              # Admin-specific styles
│   ├── js/                        # Admin JavaScript
│   │   └── admin.js               # Admin functionality
│   ├── includes/                  # Admin Core Files
│   │   ├── AdminAuth.php          # Admin authentication
│   │   ├── ProductManager.php     # Product management
│   │   ├── OrderManager.php       # Order management
│   │   └── Dashboard.php          # Dashboard logic
│   ├── login.php                  # Admin login page
│   ├── dashboard.php               # Admin dashboard
│   ├── products.php               # Product management
│   ├── orders.php                 # Order management
│   ├── customers.php              # Customer management
│   ├── settings.php               # System settings
│   └── logout.php                 # Admin logout
├── assets/                        # Frontend Assets
│   ├── css/                       # Stylesheets
│   │   ├── style.css              # Main stylesheet
│   │   ├── responsive.css         # Responsive styles
│   │   └── animations.css         # Animation styles
│   ├── js/                        # JavaScript Files
│   │   ├── main.js                # Main JavaScript
│   │   ├── cart.js                # Cart functionality
│   │   ├── checkout.js            # Checkout logic
│   │   └── admin.js               # Admin interface
│   ├── images/                    # Static Images
│   │   ├── logo.png               # Site logo
│   │   ├── placeholder.jpg        # Product placeholder
│   │   └── icons/                 # Icon set
│   └── fonts/                     # Custom fonts
├── config/                        # Configuration
│   ├── database.php               # Database config
│   ├── settings.php               # Site settings
│   └── constants.php              # Application constants
├── includes/                      # Core PHP Files
│   ├── functions.php              # Utility functions
│   ├── Session.php                # Session management
│   ├── Database.php               # Database class
│   ├── UserAuth.php               # User authentication
│   ├── Product.php                # Product class
│   ├── Cart.php                   # Shopping cart
│   ├── Order.php                  # Order processing
│   ├── Payment.php                # Payment processing
│   ├── Email.php                  # Email functionality
│   ├── header.php                 # Header component
│   ├── footer.php                 # Footer component
│   └── sidebar.php                # Sidebar component
├── uploads/                       # User Uploads
│   ├── products/                  # Product images
│   ├── categories/                # Category images
│   └── temp/                      # Temporary uploads
├── ajax/                          # AJAX Endpoints
│   ├── add-to-cart.php           # Add to cart API
│   ├── update-cart.php           # Update cart API
│   ├── toggle-wishlist.php       # Wishlist API
│   ├── checkout.php               # Checkout API
│   └── search.php                # Search API
├── pages/                         # Page Templates
│   ├── index.php                  # Homepage
│   ├── products.php               # Product listing
│   ├── product.php                # Product detail
│   ├── cart.php                   # Shopping cart
│   ├── checkout.php               # Checkout process
│   ├── login.php                  # User login
│   ├── register.php               # User registration
│   ├── account.php                # User account
│   ├── wishlist.php               # User wishlist
│   ├── orders.php                 # User orders
│   ├── about.php                  # About page
│   ├── contact.php                # Contact page
│   └── search.php                 # Search results
├── database.sql                   # Database schema
├── sample_data.sql                # Sample data
├── README.md                      # Installation guide
├── DOCUMENTATION.md               # This file
└── .htaccess                      # Apache configuration
```

## API Documentation

### Authentication Endpoints

#### Login
```
POST /ajax/login.php
Content-Type: application/x-www-form-urlencoded

Parameters:
- email: User email
- password: User password

Response:
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

#### Logout
```
POST /ajax/logout.php

Response:
{
    "success": true,
    "message": "Logged out successfully"
}
```

### Product Endpoints

#### Get Products
```
GET /ajax/products.php
Query Parameters:
- page: Page number (default: 1)
- limit: Items per page (default: 12)
- category: Category ID
- search: Search term
- sort: Sort order (price, name, rating)
- order: Sort direction (asc, desc)

Response:
{
    "success": true,
    "products": [...],
    "pagination": {
        "current_page": 1,
        "total_pages": 10,
        "total_items": 120
    }
}
```

#### Get Product Details
```
GET /ajax/product.php?id={product_id}

Response:
{
    "success": true,
    "product": {
        "id": 1,
        "name": "Product Name",
        "price": 99.99,
        "description": "...",
        "images": [...],
        "category": {...},
        "stock": 50,
        "rating": 4.5
    }
}
```

### Cart Endpoints

#### Add to Cart
```
POST /ajax/add-to-cart.php
Content-Type: application/x-www-form-urlencoded

Parameters:
- product_id: Product ID
- quantity: Quantity (default: 1)

Response:
{
    "success": true,
    "message": "Product added to cart",
    "cart_count": 5
}
```

#### Update Cart
```
POST /ajax/update-cart.php
Content-Type: application/x-www-form-urlencoded

Parameters:
- product_id: Product ID
- quantity: New quantity

Response:
{
    "success": true,
    "message": "Cart updated",
    "cart_total": 499.95
}
```

#### Remove from Cart
```
POST /ajax/remove-from-cart.php
Content-Type: application/x-www-form-urlencoded

Parameters:
- product_id: Product ID

Response:
{
    "success": true,
    "message": "Product removed from cart"
}
```

### Order Endpoints

#### Create Order
```
POST /ajax/create-order.php
Content-Type: application/json

Body:
{
    "shipping_address": {
        "first_name": "John",
        "last_name": "Doe",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "postal_code": "10001",
        "country": "USA"
    },
    "payment_method": "credit_card",
    "notes": "Special instructions"
}

Response:
{
    "success": true,
    "order_id": 12345,
    "order_number": "ORD-2024-001",
    "total": 599.99
}
```

## Security Features

### Authentication Security
- **Password Hashing**: Uses bcrypt with salt for secure password storage
- **Session Security**: Secure session management with regeneration
- **CSRF Protection**: Tokens prevent cross-site request forgery
- **Rate Limiting**: Login attempt limiting to prevent brute force
- **Session Timeout**: Automatic session expiration

### Data Protection
- **Input Validation**: Comprehensive sanitization of all user inputs
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Prevention**: Output escaping and Content Security Policy
- **File Upload Security**: File type validation and secure storage
- **HTTPS Support**: SSL/TLS encryption for data transmission

### Access Control
- **Role-Based Access**: Different permission levels for users and admins
- **API Authentication**: Secure API endpoints with token validation
- **IP Validation**: Optional IP whitelisting for admin access
- **Activity Logging**: Comprehensive audit trail of user actions

### Server Security
- **File Permissions**: Restrictive file permissions for sensitive files
- **Error Handling**: Secure error messages without information disclosure
- **Backup Security**: Encrypted backup storage options
- **Monitoring**: Real-time security monitoring and alerts

## Customization Guide

### Visual Customization

#### Branding
1. **Logo**: Replace `assets/images/logo.png` with your logo
2. **Colors**: Update CSS variables in `assets/css/style.css`:
   ```css
   :root {
       --primary-color: #your-brand-color;
       --secondary-color: #your-secondary-color;
   }
   ```
3. **Typography**: Update font families in CSS
4. **Layout**: Modify Bootstrap grid system in HTML templates

#### Theme Options
```php
// In config/database.php
define('THEME_COLOR', '#667eea');
define('THEME_STYLE', 'modern'); // modern, classic, minimal
define('LOGO_TEXT', 'Your Store');
```

### Functional Customization

#### Adding New Fields
1. **Database**: Add columns to relevant tables
2. **Models**: Update PHP classes to handle new fields
3. **Views**: Add form fields to HTML templates
4. **Validation**: Update input validation rules

#### Custom Payment Gateways
```php
// Create new payment class
class CustomPaymentGateway {
    public function processPayment($orderData) {
        // Implementation
    }
    
    public function validateResponse($response) {
        // Implementation
    }
}
```

#### Custom Shipping Methods
```php
// Add to config/settings.php
$shipping_methods = [
    'standard' => ['cost' => 10, 'days' => '5-7'],
    'express' => ['cost' => 25, 'days' => '1-2'],
    'overnight' => ['cost' => 50, 'days' => '1']
];
```

### Advanced Customization

#### Plugin System
```php
// Create plugin interface
interface PluginInterface {
    public function init();
    public function getName();
    public function getVersion();
}

// Example plugin
class CustomPlugin implements PluginInterface {
    public function init() {
        // Plugin initialization
    }
    
    public function getName() {
        return 'Custom Plugin';
    }
    
    public function getVersion() {
        return '1.0.0';
    }
}
```

#### Custom Hooks
```php
// Add hook system
class HookManager {
    public static function addHook($hook, $callback) {
        // Implementation
    }
    
    public static function executeHook($hook, $data = []) {
        // Implementation
    }
}

// Usage example
HookManager::addHook('after_order_created', 'sendCustomNotification');
```

## Deployment Guide

### Production Server Setup

#### 1. Server Requirements
- **PHP**: 7.4+ (8.0+ recommended)
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 20GB SSD

#### 2. SSL Certificate
```bash
# Using Let's Encrypt
sudo certbot --apache -d yourdomain.com
```

#### 3. Performance Optimization
```nginx
# Nginx configuration for performance
server {
    gzip on;
    gzip_types text/css application/javascript image/svg+xml;
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### 4. Database Optimization
```sql
-- Add indexes for performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(order_status);
```

### Monitoring and Logging

#### Error Logging
```php
// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $logEntry = date('Y-m-d H:i:s') . " - Error: $errstr in $errfile on line $errline";
    error_log($logEntry, 3, '/var/log/ecommerce/error.log');
}
set_error_handler('customErrorHandler');
```

#### Performance Monitoring
```php
// Performance tracking
class PerformanceMonitor {
    public static function trackQuery($sql, $executionTime) {
        if ($executionTime > 1.0) {
            error_log("Slow query: $sql took $executionTime seconds");
        }
    }
}
```

### Backup Strategy

#### Automated Database Backup
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p'password' ecommerce > /backups/db_$DATE.sql
find /backups -name "db_*.sql" -mtime +30 -delete
```

#### File Backup
```bash
#!/bin/bash
# backup_files.sh
rsync -av --delete /var/www/ecommerce/uploads/ /backups/uploads/
```

## Troubleshooting

### Common Issues and Solutions

#### Database Connection Issues
```
Error: SQLSTATE[HY000] [2002] Connection refused
```
**Solutions:**
- Check database server is running
- Verify database credentials
- Check firewall settings
- Ensure database user has proper permissions

#### File Upload Issues
```
Error: Failed to move uploaded file
```
**Solutions:**
- Check directory permissions (755)
- Verify upload_max_filesize in php.ini
- Ensure sufficient disk space
- Check file type validation

#### Session Issues
```
Error: session_start(): Failed to initialize storage
```
**Solutions:**
- Check session.save_path permissions
- Clear session files
- Verify session configuration
- Check disk space

#### Performance Issues
```
Error: Slow page loading
```
**Solutions:**
- Enable PHP OPcache
- Optimize database queries
- Implement caching
- Use CDN for static assets
- Enable gzip compression

#### Email Issues
```
Error: Failed to send email
```
**Solutions:**
- Check SMTP configuration
- Verify email credentials
- Check firewall for SMTP ports
- Test with different SMTP server

### Debug Mode

#### Enable Debug Mode
```php
// In config/database.php
define('DEBUG_MODE', true);
```

#### Log Files
- **Apache Error Log**: `/var/log/apache2/error.log`
- **Nginx Error Log**: `/var/log/nginx/error.log`
- **PHP Error Log**: `/var/log/php_errors.log`
- **Application Log**: `/var/log/ecommerce/app.log`

### Performance Monitoring

#### Database Query Analysis
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
```

#### PHP Performance
```bash
# Check PHP OPcache status
php -m | grep opcache
php -i | grep opcache
```

#### Server Resources
```bash
# Check system resources
top
htop
df -h
free -h
```

---

For additional support or questions, please refer to the GitHub repository or contact your development team.

This documentation is continuously updated. Please check for the latest version.