

// ============================================
// FILE: api/admin/update_product.php
// ============================================
<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

// Get form data
$product_id = intval($_POST['product_id'] ?? 0);
$product_name = trim($_POST['product_name'] ?? '');
$sku_number = trim($_POST['sku_number'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$product_description = trim($_POST['product_description'] ?? '');
$category = trim($_POST['category'] ?? '');
$brand_name = trim($_POST['brand_name'] ?? '');
$status = trim($_POST['status'] ?? 'draft');
$is_new_product = intval($_POST['is_new_product'] ?? 0);
$is_popular_product = intval($_POST['is_popular_product'] ?? 0);

// Validation
if ($product_id <= 0 || empty($product_name) || empty($sku_number) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit;
}

// Get current product data
$check_sql = "SELECT image FROM products WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('i', $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$current_product = $result->fetch_assoc();
$check_stmt->close();

if (!$current_product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$image_path = $current_product['image'];

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../assets/img/Products/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($file_extension, $allowed_extensions)) {
        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Delete old image
            if (!empty($current_product['image']) && file_exists('../../' . $current_product['image'])) {
                unlink('../../' . $current_product['image']);
            }
            $image_path = 'assets/img/Products/' . $new_filename;
        }
    }
}

// Update product
$sql = "UPDATE products SET 
    product_name = ?, 
    sku_number = ?, 
    price = ?, 
    stock = ?, 
    product_description = ?, 
    category = ?, 
    brand_name = ?, 
    image = ?, 
    status = ?, 
    is_new_product = ?, 
    is_popular_product = ?,
    updated_at = NOW()
WHERE id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    'ssdisssssiii',
    $product_name,
    $sku_number,
    $price,
    $stock,
    $product_description,
    $category,
    $brand_name,
    $image_path,
    $status,
    $is_new_product,
    $is_popular_product,
    $product_id
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update product: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>