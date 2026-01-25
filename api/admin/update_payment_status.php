<?php
// api/admin/update_payment_status.php
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
$valid_statuses = ['pending', 'paid', 'failed', 'refunded'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    // Manual log
    $admin_id = $_SESSION['admin_id'];
    $desc = "Updated order #$order_id payment status to $status";
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address) VALUES (?, 'update_payment', ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("iss", $admin_id, $desc, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update payment status: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
