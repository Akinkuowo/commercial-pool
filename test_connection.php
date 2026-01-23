<?php
// test_connection.php - Simple test to verify database setup

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Check if config exists
if (!file_exists('config.php')) {
    echo "<p style='color: red;'>❌ config.php file NOT found!</p>";
    echo "<p>Please create config.php in the same directory as this file.</p>";
    exit;
}

echo "<p style='color: green;'>✓ config.php file found</p>";

require_once 'config.php';

echo "<h2>Testing Database Connection...</h2>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
        echo "<p>Please check your database credentials in config.php</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Successfully connected to database: " . DB_NAME . "</p>";
    
    // Check if products table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'products'");
    
    if ($tableCheck->num_rows == 0) {
        echo "<p style='color: red;'>❌ 'products' table NOT found!</p>";
        echo "<p>Please run the CREATE TABLE SQL statement to create the products table.</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ 'products' table found</p>";
    
    // Count products
    $countResult = $conn->query("SELECT COUNT(*) as total FROM products");
    $countRow = $countResult->fetch_assoc();
    $totalProducts = $countRow['total'];
    
    echo "<p style='color: green;'>✓ Total products in database: <strong>" . $totalProducts . "</strong></p>";
    
    if ($totalProducts == 0) {
        echo "<p style='color: orange;'>⚠ Warning: No products found in database. Please insert some products.</p>";
    } else {
        // Show sample products
        echo "<h2>Sample Products (first 5):</h2>";
        $sampleResult = $conn->query("SELECT product_name, sku_number, price, brand_name, stock_status FROM products LIMIT 5");
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Product Name</th><th>SKU</th><th>Price</th><th>Brand</th><th>Stock</th></tr>";
        
        while ($row = $sampleResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sku_number']) . "</td>";
            echo "<td>£" . number_format($row['price'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($row['brand_name']) . "</td>";
            echo "<td>" . $row['stock_status'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<h2 style='color: green;'>✓ All tests passed!</h2>";
    echo "<p>You can now use the products page. The API should work correctly.</p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>