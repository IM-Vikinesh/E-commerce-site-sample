# PHP E-Commerce Website - Installation Guide

## Overview

This is a complete, production-ready PHP e-commerce website with a fully functional admin panel and modern frontend UI. The system includes user authentication, product management, order processing, shopping cart, wishlist, and much more.

## Features

### Frontend (User Side)
- Modern, responsive design inspired by Amazon/Flipkart
- Home page with hero banner, featured products, categories
- Product listing with search, filters, and pagination
- Product detail page with image gallery and reviews
- Shopping cart with real-time updates
- Checkout process with multiple payment options
- User registration, login, and account management
- Wishlist functionality
- Product reviews and ratings
- Order tracking
- Search and filtering

### Admin Panel
- Secure admin authentication
- Dashboard with sales analytics and statistics
- Product management (CRUD with image upload)
- Category management
- Order management and status updates
- User management
- Review management
- Settings management
- Responsive admin UI

## System Requirements

### Server Requirements
- **PHP Version**: 7.4 or higher (8.0+ recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Memory**: Minimum 512MB RAM (1GB+ recommended)
- **Disk Space**: Minimum 500MB (2GB+ recommended for images)

### PHP Extensions Required
- `pdo_mysql` (for database connectivity)
- `gd` or `imagick` (for image processing)
- `curl` (for API calls and email)
- `json` (for JSON handling)
- `mbstring` (for string handling)
- `openssl` (for secure hashing)
- `session` (for session management)
- `fileinfo` (for file upload validation)

### Optional Extensions
- `imap` (for email functionality)
- `zip` (for backup functionality)

## Installation Instructions

### Step 1: Download and Extract Files

1. Download the project files to your local machine
2. Extract the ZIP file to your web server's document root
3. Rename the extracted folder to your desired project name (e.g., `ecommerce`)

### Step 2: Database Setup

1. **Create Database**
   ```sql
   CREATE DATABASE ecommerce;
   ```

2. **Import Database Schema**
   - Using phpMyAdmin:
     - Select the `ecommerce` database
     - Click on "Import" tab
     - Choose the `database.sql` file
     - Click "Go"

   - Using MySQL CLI:
     ```bash
     mysql -u username -p ecommerce < database.sql
     ```

3. **Import Sample Data (Optional)**
   - Using phpMyAdmin:
     - Select the `ecommerce` database
     - Click on "Import" tab
     - Choose the `sample_data.sql` file
     - Click "Go"

   - Using MySQL CLI:
     ```bash
     mysql -u username -p ecommerce < sample_data.sql
     ```

### Step 3: Configuration

1. **Database Configuration**
   - Open `config/database.php`
   - Update the database connection settings:
     ```php
     define('DB_HOST', 'localhost');        // Database host
     define('DB_NAME', 'ecommerce');        // Database name
     define('DB_USER', 'your_username');    // Database username
     define('DB_PASS', 'your_password');    // Database password
     ```

2. **Site Configuration**
   - In `config/database.php`, update site settings:
     ```php
     define('SITE_NAME', 'Your Store Name');
     define('SITE_URL', 'http://localhost/your-project-folder/');
     define('ADMIN_URL', SITE_URL . 'admin/');
     ```

3. **File Permissions**
   - Set write permissions for the uploads directory:
     ```bash
     chmod 755 uploads/
     chmod 755 admin/uploads/
     ```

### Step 4: Web Server Configuration

#### For Apache

1. **Enable .htaccess**
   - Ensure `AllowOverride` is set to `All` in your Apache configuration

2. **Required Modules**
   - Enable `mod_rewrite` for clean URLs:
     ```bash
     sudo a2enmod rewrite
     ```

3. **Virtual Host Example**
   ```apache
   <VirtualHost *:80>
       DocumentRoot /var/www/html/ecommerce
       ServerName your-domain.com
       
       <Directory /var/www/html/ecommerce>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

#### For Nginx

1. **Configuration Example**
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /var/www/html/ecommerce;
       index index.php index.html;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

### Step 5: Testing the Installation

1. **Frontend Access**
   - Open your browser and navigate to `http://localhost/your-project-folder/`
   - You should see the homepage with sample products

2. **Admin Panel Access**
   - Navigate to `http://localhost/your-project-folder/admin/`
   - Login with default credentials:
     - Username: `admin`
     - Password: `admin123`

### Step 6: Post-Installation Setup

1. **Change Default Admin Password**
   - Log in to admin panel
   - Go to Profile → Change Password
   - Set a strong password

2. **Configure Site Settings**
   - Go to Settings in admin panel
   - Update store information, contact details, etc.

3. **Upload Product Images**
   - Add your own product images to the `uploads/` directory
   - Update product records in admin panel

4. **Configure Email Settings**
   - Update SMTP settings in `config/database.php` if needed
   - Test email functionality

## Directory Structure

```
ecommerce/
├── admin/                      # Admin panel files
│   ├── css/                    # Admin stylesheets
│   ├── js/                     # Admin JavaScript files
│   ├── includes/               # Admin includes and classes
│   ├── login.php              # Admin login page
│   ├── dashboard.php           # Admin dashboard
│   └── ...
├── assets/                     # Frontend assets
│   ├── css/                   # Frontend stylesheets
│   ├── js/                    # Frontend JavaScript files
│   └── images/                # Static images
├── config/                     # Configuration files
│   └── database.php           # Database and site configuration
├── includes/                   # Core PHP files
│   ├── functions.php          # Utility functions
│   ├── Session.php            # Session management
│   ├── UserAuth.php           # User authentication
│   └── Database.php           # Database class
├── uploads/                    # Uploaded files
├── index.php                   # Homepage
├── login.php                   # User login page
├── register.php                # User registration page
├── products.php                # Product listing page
├── cart.php                    # Shopping cart page
├── checkout.php                # Checkout page
├── database.sql                # Database schema
├── sample_data.sql             # Sample data
└── README.md                   # This file
```

## Security Recommendations

### 1. Database Security
- Use strong database passwords
- Restrict database user privileges (only SELECT, INSERT, UPDATE, DELETE)
- Enable SSL for database connections if possible

### 2. File Security
- Move configuration files outside web root if possible
- Set appropriate file permissions (755 for directories, 644 for files)
- Prevent directory listing

### 3. PHP Security
- Keep PHP updated to the latest version
- Disable PHP error display in production (`display_errors = Off`)
- Enable and configure `open_basedir` if possible

### 4. Web Server Security
- Use HTTPS with valid SSL certificate
- Implement security headers (CSP, X-Frame-Options, etc.)
- Regularly update web server software

### 5. Application Security
- Change default admin credentials immediately
- Use strong passwords for all accounts
- Regularly backup database and files
- Monitor for suspicious activities

## Performance Optimization

### 1. Database Optimization
- Add indexes to frequently queried columns
- Use query caching
- Optimize database queries

### 2. Caching
- Implement PHP opcode caching (OPcache)
- Use browser caching for static assets
- Consider Redis/Memcached for session and data caching

### 3. Image Optimization
- Compress product images
- Use appropriate image formats (WebP for modern browsers)
- Implement lazy loading for images

### 4. PHP Optimization
- Enable PHP OPcache
- Use the latest stable PHP version
- Profile and optimize slow code

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
```
Error: Database Connection Failed
```
**Solution:**
- Verify database credentials in `config/database.php`
- Ensure database server is running
- Check database user permissions

#### 2. 404 Errors
```
Not Found: The requested URL was not found
```
**Solution:**
- Ensure `.htaccess` file exists and is readable
- Check Apache `AllowOverride` directive
- For Nginx, verify URL rewriting configuration

#### 3. Permission Denied Errors
```
Warning: move_uploaded_file(): Permission denied
```
**Solution:**
- Set correct permissions for uploads directory
- Ensure web server user has write permissions

#### 4. Session Issues
```
Warning: session_start(): Failed to initialize storage module
```
**Solution:**
- Check session save path permissions
- Ensure sufficient disk space
- Verify session configuration in php.ini

#### 5. Image Upload Issues
```
File type not supported
```
**Solution:**
- Verify GD or ImageMagick extension is installed
- Check file type validation in upload script
- Ensure uploaded file size is within limits

### Debug Mode

To enable debug mode:
1. Open `config/database.php`
2. Set `define('DEBUG_MODE', true);`

**Warning:** Always disable debug mode in production!

## Support and Maintenance

### Regular Maintenance Tasks
1. **Database Backups**: Perform regular database backups
2. **File Backups**: Backup uploaded files and configuration
3. **Security Updates**: Keep PHP and server software updated
4. **Log Monitoring**: Monitor error logs for issues
5. **Performance Monitoring**: Monitor site performance and optimize

### Getting Help
- Check error logs in `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- Enable debug mode to see detailed error messages
- Review PHP documentation for specific function issues
- Consider professional support for complex issues

## Deployment to Production

### Pre-Deployment Checklist
- [ ] Change all default passwords
- [ ] Disable debug mode
- [ ] Configure HTTPS/SSL
- [ ] Set appropriate file permissions
- [ ] Test all functionality
- [ ] Perform security audit
- [ ] Set up monitoring and logging
- [ ] Create backup strategy

### Production Server Configuration
1. Use HTTPS with valid SSL certificate
2. Configure security headers
3. Set up regular backups
4. Monitor server resources
5. Implement CDN for static assets
6. Configure error logging and monitoring

## License

This project is provided as-is for educational and commercial use. Please review the license terms if applicable.

---

**Happy Selling!** 🛍️

For additional support or custom development needs, please contact your development team.