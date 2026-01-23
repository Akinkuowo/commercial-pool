<?php
// api/products_debug.php - Debug version to check what's happening

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output, only in JSON

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if config file exists
$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'config.php file not found',
        'debug' => [
            'looking_for' => $configPath,
            'current_dir' => __DIR__,
            'suggestion' => 'Ensure config.php is in the parent directory'
        ]
    ]);
    exit;
}

require_once $configPath;

try {
    // Test database connection
    $conn = getDbConnection();
    
    if (!$conn) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to connect to database',
            'debug' => 'Check your database credentials in config.php'
        ]);
        exit;
    }
    
    // Test simple query
    $testQuery = "SELECT COUNT(*) as total FROM products";
    $testResult = $conn->query($testQuery);
    
    if (!$testResult) {
        echo json_encode([
            'success' => false,
            'error' => 'Products table not found or query failed',
            'debug' => $conn->error,
            'sql' => $testQuery
        ]);
        exit;
    }
    
    $totalRow = $testResult->fetch_assoc();
    $totalProducts = $totalRow['total'];
    
    // Get all products - simple version
    $sql = "SELECT 
                id,
                product_name as name,
                sku_number as sku,
                price,
                brand_name as brand,
                stock_status as stock,
                size_variant_model as category,
                colour_type,
                quantity,
                product_description,
                is_new_product,
                is_popular_product
            FROM products 
            ORDER BY product_name ASC
            LIMIT 100";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode([
            'success' => false,
            'error' => 'Query execution failed',
            'debug' => $conn->error,
            'sql' => $sql
        ]);
        exit;
    }
    
    // Fetch all products
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Generate placeholder image based on category
        $imageMap = [
            'awning' => 'https://images.unsplash.com/photo-1571687949921-1306bfb24c72?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'cooling' => 'https://images.unsplash.com/photo-1570727624867-35da6d63d3bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'tent' => 'https://images.unsplash.com/photo-1534188753412-9f0337d4d51d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'furniture' => 'https://images.unsplash.com/photo-1504851149312-7a075b496cc7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'electrical' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'kitchen' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'pool' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        ];
        
        $category = strtolower($row['category'] ?? 'furniture');
        $row['image'] = $imageMap[$category] ?? $imageMap['furniture'];
        
        $products[] = $row;
    }
    
    // Get unique brands for filter
    $brandsQuery = "SELECT DISTINCT brand_name as brand, COUNT(*) as count 
                    FROM products 
                    WHERE brand_name IS NOT NULL AND brand_name != ''
                    GROUP BY brand_name 
                    ORDER BY brand_name ASC";
    $brandsResult = $conn->query($brandsQuery);
    
    $brandsList = [];
    if ($brandsResult) {
        while ($brandRow = $brandsResult->fetch_assoc()) {
            $brandsList[] = $brandRow;
        }
    }
    
    // Success response
    ob_clean(); // Clear any unexpected output
    echo json_encode([
        'success' => true,
        'products' => $products,
        'brands' => $brandsList,
        'totalCount' => count($products),
        'debug' => [
            'total_in_db' => $totalProducts,
            'returned' => count($products),
            'brands_found' => count($brandsList)
        ]
    ]);
    
    closeDbConnection($conn);
    
} catch (Exception $e) {
    ob_clean(); // Clear any unexpected output
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>