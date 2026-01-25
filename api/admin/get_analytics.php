<?php
// api/admin/get_analytics.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

$response = [
    'success' => true,
    'category_sales' => [],
    'inventory_value' => [],
    'stock_health' => [],
    'low_stock_products' => []
];

// 1. Sales by Category
// Requires order_items joining products
$cat_sales_sql = "SELECT 
    p.category, 
    SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY p.category
    ORDER BY revenue DESC";

// Check table existence first
$check = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($check->num_rows > 0) {
    // If we have order_items, run the query
    $result = $conn->query($cat_sales_sql);
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Normalize category if empty
            if (empty($row['category'])) $row['category'] = 'Uncategorized';
            $data[] = $row;
        }
        $response['category_sales'] = $data;
    }
} else {
    // Fallback? Or just empty array
    $response['category_sales'] = [];
}

// 2. Inventory Value by Category
$inv_val_sql = "SELECT 
    category,
    SUM(stock * price) as total_value,
    COUNT(*) as product_count
    FROM products
    GROUP BY category
    ORDER BY total_value DESC";
$result = $conn->query($inv_val_sql);
$inv_data = [];
while ($row = $result->fetch_assoc()) {
    if (empty($row['category'])) $row['category'] = 'Uncategorized';
    $inv_data[] = $row;
}
$response['inventory_value'] = $inv_data;

// 3. Stock Health
$stock_health_sql = "SELECT 
    CASE 
        WHEN stock = 0 THEN 'Out of Stock'
        WHEN stock <= 10 THEN 'Low Stock'
        ELSE 'In Stock'
    END as status,
    COUNT(*) as count
    FROM products
    GROUP BY 
    CASE 
        WHEN stock = 0 THEN 'Out of Stock'
        WHEN stock <= 10 THEN 'Low Stock'
        ELSE 'In Stock'
    END";
$result = $conn->query($stock_health_sql);
$health_data = [];
while ($row = $result->fetch_assoc()) {
    $health_data[] = $row;
}
$response['stock_health'] = $health_data;

// 4. Low Stock Products
$low_stock_sql = "SELECT 
    product_name, 
    sku_number, 
    stock, 
    category 
    FROM products 
    WHERE stock <= 10 
    ORDER BY stock ASC 
    LIMIT 10";
$result = $conn->query($low_stock_sql);
$low_stock = [];
while ($row = $result->fetch_assoc()) {
    $low_stock[] = $row;
}
$response['low_stock_products'] = $low_stock;

echo json_encode($response);
$conn->close();
?>
