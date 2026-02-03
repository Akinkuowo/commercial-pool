<?php
// api/admin/delete_product.php
session_start();
require_once '../../config.php';
require_once '../admin/include/utils.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Admin access required.']);
    exit;
}

$conn = getDbConnection();

// Get product ID
$product_id = 0;
// Check if ID is in the query string (works for DELETE method too)
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
} 
// Fallback to checking input stream if not in URL
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $delete_vars);
    $product_id = intval($delete_vars['id'] ?? 0);
}

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Get product details before deletion
$check_sql = "SELECT product_name, sku_number, image FROM products WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('i', $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$product = $result->fetch_assoc();
$check_stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product_name = $product['product_name'];
$sku_number = $product['sku_number'];

// Delete product
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);

if ($stmt->execute()) {
    // Delete image file if exists
    if (!empty($product['image']) && file_exists('../../' . $product['image'])) {
        unlink('../../' . $product['image']);
    }
    
    // Log activity
    logActivity($conn, 'delete_product', "Deleted product: $product_name (ID: $product_id, SKU: $sku_number)");
    
    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete product: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>