<?php
// api/products.php - Enhanced version with category filtering and multiple IDs support

error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Configuration file not found'
    ]);
    exit;
}

require_once $configPath;

// Function to get product image with fallback
function getProductImage($image, $category = '') {
    // If image exists in database and is not empty, use it
    if (!empty($image) && $image !== 'NULL') {
        return $image;
    }
    
    // Otherwise use category-based fallback with local assets
    $categoryLower = strtolower($category);
    $imageMap = [
        'awning' => '../assets/img/Products/product2.webp',
        'camping' => '../assets/img/Products/product1.webp',
        'caravan' => '../assets/img/Products/product8.jpg',
        'electrical' => '../assets/img/Products/product7.jpeg',
        'heating' => '../assets/img/Products/product4.jpg',
        'kitchen' => '../assets/img/Products/product3.jpg',
        'fridge' => '../assets/img/Products/product5.jpg',
        'water' => '../assets/img/Products/product6.jpeg'
    ];
    
    foreach ($imageMap as $key => $url) {
        if (strpos($categoryLower, $key) !== false) {
            return $url;
        }
    }
    
    return '../assets/img/Products/product1.webp'; // default
}

try {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if fetching specific products by IDs
    $ids = isset($_GET['ids']) ? $_GET['ids'] : (isset($_GET['id']) ? $_GET['id'] : '');
    
    if (!empty($ids)) {
        // Fetch specific products by ID(s)
        $idArray = array_map('intval', explode(',', $ids));
        
        if (empty($idArray)) {
            throw new Exception('Invalid product IDs');
        }
        
        $placeholders = implode(',', array_fill(0, count($idArray), '?'));
        
        $sql = "SELECT 
                    id,
                    product_name as name,
                    sku_number as sku,
                    price,
                    image,
                    brand_name as brand,
                    stock_status as stock,
                    size_variant_model as size,
                    colour_type as color,
                    quantity,
                    product_description as description,
                    is_new_product as is_new,
                    is_popular_product as is_popular,
                    category,
                    full_category_path
                FROM products 
                WHERE id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        // Bind parameters
        $types = str_repeat('i', count($idArray));
        $stmt->bind_param($types, ...$idArray);
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception('Query failed: ' . $stmt->error);
        }
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Clean up the data
            $row['image'] = $row['image'] ?? '';
            $row['category'] = $row['category'] ?? '';
            $row['description'] = $row['description'] ?? '';
            $row['brand'] = $row['brand'] ?? 'Generic';
            $row['stock'] = $row['stock'] ?? 'Out of Stock';
            $row['price'] = floatval($row['price'] ?? 0);
            $row['quantity'] = intval($row['quantity'] ?? 0);
            $row['is_new'] = intval($row['is_new'] ?? 0);
            $row['is_popular'] = intval($row['is_popular'] ?? 0);
            
            // Use the image from database with fallback
            $row['image'] = getProductImage($row['image'], $row['category']);
            
            // Convert relative path to absolute URL if needed
            if (strpos($row['image'], '../') === 0) {
                $row['image'] = substr($row['image'], 3);
            }
            
            $products[] = $row;
        }
        
        $stmt->close();
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
        
        closeDbConnection($conn);
        exit;
    }
    
    // Get filter parameters from URL (existing functionality)
    $category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
    $subcategory = isset($_GET['subcategory']) ? $conn->real_escape_string($_GET['subcategory']) : '';
    $type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';
    $brand = isset($_GET['brand']) ? $conn->real_escape_string($_GET['brand']) : '';
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    // Build WHERE clause based on filters using prepared statements for security
    $whereConditions = [];
    $params = [];
    $types = '';
    
    if (!empty($category)) {
        $whereConditions[] = "category LIKE ?";
        $params[] = "%$category%";
        $types .= 's';
    }
    
    if (!empty($subcategory)) {
        $whereConditions[] = "category LIKE ?";
        $params[] = "%$subcategory%";
        $types .= 's';
    }
    
    if (!empty($type)) {
        $whereConditions[] = "category LIKE ?";
        $params[] = "%$type%";
        $types .= 's';
    }
    
    if (!empty($brand)) {
        $whereConditions[] = "brand_name = ?";
        $params[] = $brand;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $whereConditions[] = "(product_name LIKE ? OR product_description LIKE ? OR sku_number LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count using prepared statement
    $countSql = "SELECT COUNT(*) as total FROM products $whereClause";
    
    // Prepare and execute count query
    $countStmt = $conn->prepare($countSql);
    if ($countStmt && !empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRow = $countResult->fetch_assoc();
    $totalProducts = $totalRow ? $totalRow['total'] : 0;
    
    // Close count statement
    if ($countStmt) {
        $countStmt->close();
    }
    
    // Get products using prepared statement
    $sql = "SELECT 
                id,
                product_name as name,
                sku_number as sku,
                price,
                image,
                brand_name as brand,
                stock_status as stock,
                size_variant_model as size,
                colour_type as color,
                quantity,
                product_description as description,
                is_new_product as is_new,
                is_popular_product as is_popular,
                category,
                full_category_path
            FROM products 
            $whereClause
            ORDER BY is_popular_product DESC, is_new_product DESC, product_name ASC
            LIMIT ? OFFSET ?";
    
    // Add limit and offset to params
    $limitParams = array_merge($params, [$limit, $offset]);
    $limitTypes = $types . 'ii';
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Bind parameters
    if (!empty($limitParams)) {
        $stmt->bind_param($limitTypes, ...$limitParams);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception('Query failed: ' . $stmt->error);
    }
    
    // Fetch products
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Clean up the data
        $row['image'] = $row['image'] ?? '';
        $row['category'] = $row['category'] ?? '';
        $row['description'] = $row['description'] ?? '';
        $row['brand'] = $row['brand'] ?? 'Generic';
        $row['stock'] = $row['stock'] ?? 'Out of Stock';
        $row['price'] = floatval($row['price'] ?? 0);
        $row['quantity'] = intval($row['quantity'] ?? 0);
        $row['is_new'] = intval($row['is_new'] ?? 0);
        $row['is_popular'] = intval($row['is_popular'] ?? 0);
        
        // Use the image from database with fallback
        $row['image'] = getProductImage($row['image'], $row['category']);
        
        // Convert relative path to absolute URL if needed
        if (strpos($row['image'], '../') === 0) {
            // Remove the ../ prefix to make it relative to the root
            $row['image'] = substr($row['image'], 3);
        }
        
        $products[] = $row;
    }
    
    // Close main statement
    $stmt->close();
    
    // Get available brands for filters using prepared statement
    $brandsSql = "SELECT DISTINCT brand_name as brand, COUNT(*) as count 
                  FROM products 
                  $whereClause
                  GROUP BY brand_name 
                  ORDER BY brand_name ASC";
    
    $brandsStmt = $conn->prepare($brandsSql);
    $brands = [];

    if ($brandsStmt) {
        if (!empty($params)) {
            $brandsStmt->bind_param($types, ...$params);
        }
        
        if ($brandsStmt->execute()) {
            $brandsResult = $brandsStmt->get_result();
            if ($brandsResult) {
                while ($brandRow = $brandsResult->fetch_assoc()) {
                    if (!empty($brandRow['brand'])) {
                        $brands[] = $brandRow;
                    }
                }
            }
        }
        $brandsStmt->close();
    } else {
        // Fallback if prepare fails
        $brandsResult = $conn->query($brandsSql);
        if ($brandsResult) {
            while ($brandRow = $brandsResult->fetch_assoc()) {
                if (!empty($brandRow['brand'])) {
                    $brands[] = $brandRow;
                }
            }
        }
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'products' => $products,
        'brands' => $brands,
        'total' => $totalProducts,
        'count' => count($products),
        'filters' => [
            'category' => $category,
            'subcategory' => $subcategory,
            'type' => $type,
            'brand' => $brand,
            'search' => $search
        ]
    ], JSON_UNESCAPED_UNICODE);
    
    closeDbConnection($conn);
    
} catch (Exception $e) {
    ob_clean();
    error_log("Products API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching products',
        'debug' => $e->getMessage()
    ]);
}
?>