-- Add invoice_path column to orders table
ALTER TABLE orders ADD COLUMN invoice_path VARCHAR(255) NULL AFTER tracking_number;

-- Also fix column naming if needed
ALTER TABLE orders CHANGE COLUMN subtotal subtotal DECIMAL(10,2) NOT NULL DEFAULT 0;
ALTER TABLE orders CHANGE COLUMN tax_amount tax DECIMAL(10,2) DEFAULT 0;
ALTER TABLE orders CHANGE COLUMN shipping_amount shipping_cost DECIMAL(10,2) DEFAULT 0;
ALTER TABLE orders CHANGE COLUMN discount_amount discount DECIMAL(10,2) DEFAULT 0;

-- Fix payment_method enum to include cash_on_delivery
ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash_on_delivery', 'credit_card', 'debit_card', 'paypal', 'bank_transfer') DEFAULT 'cash_on_delivery';

-- Fix order_items table column naming
ALTER TABLE order_items CHANGE COLUMN total subtotal DECIMAL(10,2) NOT NULL;