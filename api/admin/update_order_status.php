<?php
// api/admin/update_order_status.php
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

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
$status = isset($data['status']) ? trim($data['status']) : '';

if ($order_id <= 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or status']);
    exit;
}

// Valid statuses
$valid_statuses = ['pending', 'processing', 'on_hold', 'completed', 'cancelled', 'refunded'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    // Log activity if activity logging is implemented
    if (function_exists('logActivity')) {
        // logActivity($_SESSION['admin_id'], 'update_order', "Updated order #$order_id status to $status");
    } else {
        // Manual log insert 
        $admin_id = $_SESSION['admin_id'];
        $desc = "Updated order #$order_id status to $status";
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address) VALUES (?, 'update_order', ?, ?)");
        if ($log_stmt) {
            $log_stmt->bind_param("iss", $admin_id, $desc, $ip);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }

    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
