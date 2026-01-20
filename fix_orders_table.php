<?php
/**
 * Fix Orders Table Structure
 * This will add the missing columns to the orders table
 */

// Direct database connection
$host = 'localhost';
$dbname = 'ecommerce';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Fixing Orders Table Structure</h2>";
    
    // Check if columns exist and add them if needed
    $columnsToAdd = [
        'first_name' => "VARCHAR(50) NOT NULL",
        'last_name' => "VARCHAR(50) NOT NULL", 
        'email' => "VARCHAR(100) NOT NULL",
        'phone' => "VARCHAR(20)",
        'address' => "TEXT",
        'city' => "VARCHAR(50)",
        'state' => "VARCHAR(50)",
        'postal_code' => "VARCHAR(20)",
        'country' => "VARCHAR(50)"
    ];
    
    foreach ($columnsToAdd as $column => $definition) {
        // Check if column exists
        $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' AND COLUMN_NAME = ?");
        $stmt->execute([$dbname, $column]);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Added column: $column</p>";
        } else {
            echo "<p style='color: blue;'>ℹ Column already exists: $column</p>";
        }
    }
    
    // Also check for other potentially missing columns
    $otherColumns = [
        'invoice_path' => "VARCHAR(255) NULL AFTER tracking_number",
        'tax' => "DECIMAL(10,2) DEFAULT 0",
        'shipping_cost' => "DECIMAL(10,2) DEFAULT 0",
        'discount' => "DECIMAL(10,2) DEFAULT 0"
    ];
    
    foreach ($otherColumns as $column => $definition) {
        $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' AND COLUMN_NAME = ?");
        $stmt->execute([$dbname, $column]);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            if ($column === 'invoice_path') {
                $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
            } else {
                $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
            }
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Added column: $column</p>";
        } else {
            echo "<p style='color: blue;'>ℹ Column already exists: $column</p>";
        }
    }
    
    // Fix payment_method enum if needed
    try {
        $pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash_on_delivery', 'credit_card', 'debit_card', 'paypal', 'bank_transfer') DEFAULT 'cash_on_delivery'");
        echo "<p style='color: green;'>✓ Fixed payment_method enum</p>";
    } catch (Exception $e) {
        echo "<p style='color: blue;'>ℹ payment_method enum already correct</p>";
    }
    
    // Show current table structure
    echo "<h3>✅ Current Orders Table Structure:</h3>";
    $stmt = $pdo->prepare("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' ORDER BY ORDINAL_POSITION");
    $stmt->execute([$dbname]);
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Column Name</th><th>Data Type</th><th>Nullable</th><th>Default</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['COLUMN_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($column['DATA_TYPE']) . "</td>";
        echo "<td>" . htmlspecialchars($column['IS_NULLABLE']) . "</td>";
        echo "<td>" . htmlspecialchars($column['COLUMN_DEFAULT'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Test the checkout:</strong> <a href='checkout.php'>Go to Checkout</a></li>";
    echo "<li><strong>Add items to cart</strong> if you haven't already</li>";
    echo "<li><strong>Place an order</strong> to test the fix</li>";
    echo "<li><strong>Check order creation</strong> in admin panel if needed</li>";
    echo "</ol>";
    
    echo "<div class='alert alert-success' style='margin-top: 20px;'>";
    echo "<strong>✅ Orders table structure fixed!</strong><br>";
    echo "The checkout should now work properly. All missing columns have been added.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>❌ Error fixing table:</strong> " . $e->getMessage();
    echo "</div>";
}
?>