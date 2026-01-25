

// ============================================
// FILE: api/admin/export_products.php
// ============================================
<?php
session_start();
require_once '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../admin/login.php');
    exit;
}

$conn = getDbConnection();

// Get all products
$sql = "SELECT 
    id, product_name, sku_number, price, stock, 
    product_description, category, brand_name, 
    status, is_new_product, is_popular_product,
    created_at, updated_at
FROM products 
ORDER BY created_at DESC";

$result = $conn->query($sql);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products_export_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID', 'Product Name', 'SKU', 'Price', 'Stock',
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
        $row['stock'],
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
$conn->close();
exit;
?>