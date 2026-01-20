<?php
require_once 'config/database.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

$db = getDB();

// Get products with their image paths
$products = $db->select('products', 'id, name, image', '1=1', [], 'id ASC', 10);

echo "<h3>Products and their image paths:</h3>\n";
echo "<table border='1' style='border-collapse: collapse; margin: 10px;'>\n";
echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists</th></tr>\n";

foreach ($products as $product) {
    $imagePath = $product['image'];
    $fileExists = file_exists('uploads/' . $imagePath) ? 'Yes' : 'No';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($product['id']) . "</td>";
    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
    echo "<td>" . htmlspecialchars($imagePath) . "</td>";
    echo "<td>" . $fileExists . "</td>";
    echo "</tr>\n";
}

echo "</table>\n";

// Check what files are actually in uploads directory
echo "<h3>Files in uploads directory:</h3>\n";
if (is_dir('uploads')) {
    $files = scandir('uploads');
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "- " . htmlspecialchars($file) . "\n";
        }
    }
} else {
    echo "Uploads directory does not exist\n";
}
?>