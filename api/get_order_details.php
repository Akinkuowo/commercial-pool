<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once('../config.php');

try {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get Order ID from query parameter
    $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($order_id <= 0) {
        throw new Exception('Invalid order ID');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Fetch Order Info (verify ownership)
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("is", $order_id, $user_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Fetch Order Items
    $items_sql = "SELECT oi.*, p.product_name, p.image, p.sku_number 
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = ?";
    
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $items = [];
    while ($row = $items_result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'price' => number_format($row['price'], 2),
            'total' => number_format($row['price'] * $row['quantity'], 2),
            'image' => $row['image'],
            'sku' => $row['sku_number'] ?? ''
        ];
    }
    
    // Format response
    $response = [
        'success' => true,
        'order' => [
            'id' => $order['id'],
            'date' => date('d M Y, H:i', strtotime($order['created_at'])),
            'status' => $order['status'],
            'total' => number_format($order['total_amount'], 2),
            'payment_method' => str_replace('_', ' ', $order['payment_method']),
            'delivery_method' => ucfirst($order['delivery_method']),
            'shipping_address' => $order['shipping_address']
        ],
        'items' => $items
    ];
    
    // Check for Click & Collect info
    if ($order['delivery_method'] === 'collection') {
        $cc_sql = "SELECT collection_code, qr_code, code_expires_at FROM click_and_collect WHERE order_id = ?";
        $cc_stmt = $conn->prepare($cc_sql);
        $cc_stmt->bind_param("i", $order_id);
        $cc_stmt->execute();
        $cc_result = $cc_stmt->get_result();
        
        if ($cc_row = $cc_result->fetch_assoc()) {
            $response['collection'] = [
                'code' => $cc_row['collection_code'],
                'qr_url' => $cc_row['qr_code'],
                'expires_at' => date('d M Y, H:i', strtotime($cc_row['code_expires_at']))
            ];
        }
    }
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) closeDbConnection($conn);
}
?>
