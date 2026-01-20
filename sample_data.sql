-- Sample Data for PHP E-Commerce Website
-- Insert sample categories, products, users, and admin

USE ecommerce;

-- Insert sample categories
INSERT INTO categories (name, description, image, status) VALUES
('Electronics', 'Latest electronic gadgets and devices', 'electronics.jpg', 'active'),
('Clothing', 'Fashionable clothing for men and women', 'clothing.jpg', 'active'),
('Home & Kitchen', 'Home appliances and kitchen essentials', 'home.jpg', 'active'),
('Books', 'Wide collection of books and educational materials', 'books.jpg', 'active'),
('Sports & Outdoors', 'Sports equipment and outdoor gear', 'sports.jpg', 'active'),
('Beauty & Health', 'Beauty products and health supplements', 'beauty.jpg', 'active');

-- Insert sample products
INSERT INTO products (category_id, name, description, price, discount_price, sku, stock_quantity, image, status, featured) VALUES
(1, 'iPhone 15 Pro', 'Latest iPhone with A17 Pro chip, titanium design, and advanced camera system', 999.99, 899.99, 'IP15PRO001', 50, 'iphone15pro.jpg', 'active', TRUE),
(1, 'Samsung Galaxy S24', 'Flagship Android phone with AI features and premium design', 899.99, 799.99, 'SGS24001', 30, 'galaxys24.jpg', 'active', TRUE),
(1, 'MacBook Air M2', 'Ultra-thin laptop with M2 chip, perfect for work and entertainment', 1299.99, 1199.99, 'MBAIR2001', 25, 'macbookair.jpg', 'active', TRUE),
(1, 'Sony WH-1000XM5', 'Industry-leading noise canceling headphones with exceptional sound quality', 399.99, 349.99, 'SONYXM5001', 40, 'sonyheadphones.jpg', 'active', FALSE),
(2, 'Men\'s Premium Leather Jacket', 'Genuine leather jacket with modern fit and premium quality', 299.99, 249.99, 'MLJACK001', 20, 'leatherjacket.jpg', 'active', TRUE),
(2, 'Women\'s Summer Dress', 'Light and comfortable summer dress with elegant design', 89.99, 69.99, 'WDRESS001', 35, 'summerdress.jpg', 'active', FALSE),
(2, 'Classic Denim Jeans', 'Comfortable fit denim jeans for everyday wear', 79.99, 59.99, 'JEANS001', 60, 'denimjeans.jpg', 'active', FALSE),
(3, 'Coffee Maker Deluxe', 'Programmable coffee maker with thermal carafe and multiple brew options', 149.99, 119.99, 'COFFEE001', 15, 'coffeemaker.jpg', 'active', TRUE),
(3, 'Air Fryer Pro', 'Healthy cooking with rapid air technology, 5.3qt capacity', 129.99, 99.99, 'AIRFRY001', 25, 'airfryer.jpg', 'active', FALSE),
(3, 'Robot Vacuum Cleaner', 'Smart robot vacuum with mapping technology and app control', 399.99, 299.99, 'ROBOTVAC001', 10, 'robotvacuum.jpg', 'active', TRUE),
(4, 'JavaScript: The Complete Guide', 'Comprehensive guide to modern JavaScript programming', 49.99, 39.99, 'BOOKJS001', 100, 'jsbook.jpg', 'active', FALSE),
(4, 'Python for Data Science', 'Learn data science with Python, includes practical examples', 59.99, 44.99, 'BOOKPY001', 80, 'pythonbook.jpg', 'active', FALSE),
(5, 'Yoga Mat Premium', 'Non-slip exercise yoga mat with carrying strap', 39.99, 29.99, 'YOGAMAT001', 50, 'yogamat.jpg', 'active', FALSE),
(5, 'Mountain Bike Pro', 'Professional grade mountain bike with 21 speeds', 599.99, 499.99, 'BIKE001', 8, 'mountainbike.jpg', 'active', TRUE),
(6, 'Face Serum Vitamin C', 'Brightening face serum with vitamin C and hyaluronic acid', 49.99, 39.99, 'SERUM001', 40, 'faceserum.jpg', 'active', FALSE);

-- Insert admin user (password: admin123)
INSERT INTO admins (username, email, password, first_name, last_name, role, status) VALUES
('admin', 'admin@ecommerce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'super_admin', 'active');

-- Insert sample users
INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, postal_code, country, status) VALUES
('John', 'Doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0101', '123 Main St', 'New York', 'NY', '10001', 'USA', 'active'),
('Jane', 'Smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0102', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 'USA', 'active'),
('Mike', 'Johnson', 'mike.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0103', '789 Pine Rd', 'Chicago', 'IL', '60001', 'USA', 'active');

-- Insert sample orders
INSERT INTO orders (user_id, order_number, subtotal, tax_amount, shipping_amount, total_amount, payment_method, payment_status, order_status, shipping_address) VALUES
(1, 'ORD-2024-001', 1099.98, 88.00, 15.00, 1202.98, 'card', 'paid', 'delivered', '{"first_name":"John","last_name":"Doe","address":"123 Main St","city":"New York","state":"NY","postal_code":"10001","country":"USA"}'),
(2, 'ORD-2024-002', 199.98, 16.00, 10.00, 225.98, 'paypal', 'paid', 'shipped', '{"first_name":"Jane","last_name":"Smith","address":"456 Oak Ave","city":"Los Angeles","state":"CA","postal_code":"90001","country":"USA"}'),
(3, 'ORD-2024-003', 699.99, 56.00, 12.00, 767.99, 'cod', 'pending', 'processing', '{"first_name":"Mike","last_name":"Johnson","address":"789 Pine Rd","city":"Chicago","state":"IL","postal_code":"60001","country":"USA"}');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, price, total, product_name, product_image) VALUES
(1, 3, 1, 1199.99, 1199.99, 'MacBook Air M2', 'macbookair.jpg'),
(1, 4, 1, 349.99, 349.99, 'Sony WH-1000XM5', 'sonyheadphones.jpg'),
(2, 7, 2, 59.99, 119.98, 'Classic Denim Jeans', 'denimjeans.jpg'),
(2, 8, 1, 119.99, 119.99, 'Coffee Maker Deluxe', 'coffeemaker.jpg'),
(3, 15, 1, 499.99, 499.99, 'Mountain Bike Pro', 'mountainbike.jpg'),
(3, 14, 1, 29.99, 29.99, 'Yoga Mat Premium', 'yogamat.jpg');

-- Insert sample reviews
INSERT INTO reviews (product_id, user_id, rating, review, status) VALUES
(1, 1, 5, 'Amazing phone! The camera quality is outstanding and the performance is lightning fast.', 'approved'),
(1, 2, 4, 'Great phone but a bit expensive. Love the titanium design.', 'approved'),
(3, 1, 5, 'Perfect laptop for my work. Lightweight and the battery life is incredible.', 'approved'),
(8, 2, 4, 'Good coffee maker with nice features. The thermal carafe keeps coffee hot for hours.', 'approved'),
(15, 3, 5, 'Excellent mountain bike! Great quality and smooth ride on trails.', 'approved');

-- Insert sample settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'E-Commerce Store', 'string', 'Website name'),
('site_description', 'Premium e-commerce store with quality products', 'string', 'Website description'),
('contact_email', 'info@ecommerce.com', 'string', 'Contact email'),
('contact_phone', '+1-555-0123', 'string', 'Contact phone'),
('currency', 'USD', 'string', 'Default currency'),
('tax_rate', '8', 'number', 'Tax rate in percentage'),
('shipping_cost', '10', 'number', 'Default shipping cost'),
('free_shipping_threshold', '100', 'number', 'Free shipping minimum order amount'),
('enable_reviews', 'true', 'boolean', 'Enable product reviews'),
('enable_wishlist', 'true', 'boolean', 'Enable wishlist feature'),
('meta_title', 'Best Online Shopping Store', 'string', 'Default meta title'),
('meta_description', 'Shop for electronics, clothing, home goods and more', 'string', 'Default meta description'),
('social_facebook', 'https://facebook.com/ecommerce', 'string', 'Facebook page URL'),
('social_twitter', 'https://twitter.com/ecommerce', 'string', 'Twitter page URL'),
('social_instagram', 'https://instagram.com/ecommerce', 'string', 'Instagram page URL');

-- Insert sample cart items for testing
INSERT INTO cart (user_id, product_id, quantity) VALUES
(1, 1, 1),
(1, 4, 2),
(2, 2, 1),
(2, 7, 1);

-- Insert sample wishlist items
INSERT INTO wishlist (user_id, product_id) VALUES
(1, 2),
(1, 15),
(2, 1),
(2, 3);