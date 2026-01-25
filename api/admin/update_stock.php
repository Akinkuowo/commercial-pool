

// ============================================
// FILE: api/admin/update_stock.php
// ============================================
<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check if user is logged in
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

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id'] ?? 0);
$stock = intval($data['stock'] ?? 0);

if ($product_id <= 0 || $stock < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$sql = "UPDATE products SET stock = ?, updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $stock, $product_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Stock updated successfully',
        'new_stock' => $stock
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update stock: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>