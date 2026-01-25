<?php
// api/admin/get_order.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$conn = getDbConnection();

// Get Order Details
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Get Order Items
// Check if order_items table exists first to prevent error
$check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
$items = [];

if ($check_table->num_rows > 0) {
    // Join with products to get product info if available
    // Assuming schema: order_items(id, order_id, product_id, quantity, price)
    // And products(id, product_name, image)
    
    // We try to select product_name from items table first (if snapshotted), else join
    // Let's first check columns of order_items to see what we have
    $cols = $conn->query("DESCRIBE order_items");
    $fields = [];
    while ($row = $cols->fetch_assoc()) {
        $fields[] = $row['Field'];
    }

    $has_product_name = in_array('product_name', $fields);

    if ($has_product_name) {
        $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
    } else {
        $items_sql = "SELECT oi.*, p.product_name, p.image 
                      FROM order_items oi 
                      LEFT JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?";
    }

    $stmt = $conn->prepare($items_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
} else {
    // If table doesn't exist, maybe return a message or empty items
    // For now, empty items
}

$conn->close();

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
?>
