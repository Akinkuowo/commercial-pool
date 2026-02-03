<?php
// api/admin/export_products.php
session_start();
require_once '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

// Built-in filters (mirroring products.php logic)
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';
$search = isset($_GET['s']) ? trim($_GET['s']) : '';

$where_conditions = [];
$params = [];
$param_types = '';

if ($category_filter) {
    $where_conditions[] = "category LIKE ?";
    $params[] = "%$category_filter%";
    $param_types .= 's';
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if ($search) {
    $where_conditions[] = "(product_name LIKE ? OR sku_number LIKE ? OR product_description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if ($stock_filter) {
    switch ($stock_filter) {
        case 'in_stock':
            $where_conditions[] = "quantity > 10";
            break;
        case 'low_stock':
            $where_conditions[] = "quantity <= 10 AND quantity > 0";
            break;
        case 'out_of_stock':
            $where_conditions[] = "quantity = 0";
            break;
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get filtered products - Using 'quantity' instead of 'stock'
$sql = "SELECT 
    id, product_name, sku_number, price, quantity, 
    product_description, category, brand_name, 
    status, is_new_product, is_popular_product,
    created_at, updated_at
FROM products 
$where_clause
ORDER BY created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products_export_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID', 'Product Name', 'SKU', 'Price', 'Stock (Quantity)',
    'Description', 'Category', 'Brand', 'Status',
    'New Product', 'Popular Product', 'Created At', 'Updated At'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['product_name'],
        $row['sku_number'],
        $row['price'],
        $row['quantity'],
        $row['product_description'],
        $row['category'],
        $row['brand_name'],
        $row['status'],
        $row['is_new_product'] ? 'Yes' : 'No',
        $row['is_popular_product'] ? 'Yes' : 'No',
        $row['created_at'],
        $row['updated_at']
    ]);
}

fclose($output);
if (isset($stmt)) $stmt->close();
$conn->close();
exit;
?>