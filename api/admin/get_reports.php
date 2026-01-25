<?php
// api/admin/get_reports.php
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

$date_from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

// Add time to dates
$start_date = $date_from . ' 00:00:00';
$end_date = $date_to . ' 23:59:59';

$response = [
    'success' => true,
    'summary' => [],
    'sales_chart' => [],
    'status_chart' => [],
    'top_products' => []
];

// 1. Summary Cards
$summary_sql = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue,
    COALESCE(AVG(total_amount), 0) as avg_order_value
    FROM orders 
    WHERE created_at BETWEEN ? AND ? AND status = 'completed'";
$stmt = $conn->prepare($summary_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$response['summary'] = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Sales Over Time (Group by Day)
$sales_sql = "SELECT 
    DATE(created_at) as date, 
    SUM(total_amount) as revenue,
    COUNT(*) as orders
    FROM orders 
    WHERE created_at BETWEEN ? AND ? AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)";
$stmt = $conn->prepare($sales_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$sales_data = [];
while ($row = $result->fetch_assoc()) {
    $sales_data[] = $row;
}
$response['sales_chart'] = $sales_data;
$stmt->close();

// 3. Orders by Status
$status_sql = "SELECT status, COUNT(*) as count FROM orders WHERE created_at BETWEEN ? AND ? GROUP BY status";
$stmt = $conn->prepare($status_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$status_data = [];
while ($row = $result->fetch_assoc()) {
    $status_data[] = $row;
}
$response['status_chart'] = $status_data;
$stmt->close();

// 4. Top Selling Products
// Assuming order_items table exists and links to products
// You might need to adjust this query based on actual schema
$products_sql = "SELECT 
    p.product_name, 
    SUM(oi.quantity) as total_qty,
    SUM(oi.price * oi.quantity) as total_sales
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_sales DESC
    LIMIT 5";

// Only run if tables exist to avoid crashing if schema differs
$check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($check_table->num_rows > 0) {
    $stmt = $conn->prepare($products_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $top_products = [];
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }
    $response['top_products'] = $top_products;
    $stmt->close();
} else {
    $response['top_products'] = []; // Schema mismatch fallback
}

echo json_encode($response);
$conn->close();
?>
