<?php
// api/get_user_orders.php

error_reporting(E_ALL);
ini_set('display_errors', 0); // JSON response, don't break with errors

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../config.php';

try {
    $conn = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    // Fetch orders
    $sql = "SELECT id, total_amount, status, created_at, payment_method, delivery_method, collection_code 
            FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Format date
        $row['formatted_date'] = date('d M Y, h:i A', strtotime($row['created_at']));
        $row['formatted_total'] = '£' . number_format($row['total_amount'], 2);
        
        // Fetch items for this order (optional, maybe for detail view, but let's include summary count)
        // For efficiency in list view we might skip items details or do a JOIN. 
        // Let's do a simple count query or leave it for detail API. 
        // For now, let's keep it simple.
        
        $orders[] = $row;
    }
    
    echo json_encode(['success' => true, 'orders' => $orders]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) closeDbConnection($conn);
}
?>
