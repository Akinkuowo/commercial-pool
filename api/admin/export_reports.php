<?php
// api/admin/export_reports.php
session_start();
require_once '../../config.php';

// Check auth
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../admin/admin_login.php');
    exit;
}

$conn = getDbConnection();

$date_from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

$start_date = $date_from . ' 00:00:00';
$end_date = $date_to . ' 23:59:59';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="business_report_' . $date_from . '_to_' . $date_to . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// 1. Summary Section
fputcsv($output, ['--- SUMMARY ---']);
$summary_sql = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue,
    COALESCE(AVG(total_amount), 0) as avg_order_value
    FROM orders 
    WHERE created_at BETWEEN ? AND ? AND status = 'completed'";
$stmt = $conn->prepare($summary_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

fputcsv($output, ['Total Orders', $summary['total_orders']]);
fputcsv($output, ['Total Revenue', '£' . number_format($summary['total_revenue'], 2)]);
fputcsv($output, ['Avg Order Value', '£' . number_format($summary['avg_order_value'], 2)]);
fputcsv($output, []);

// 2. Daily Sales Section
fputcsv($output, ['--- DAILY SALES ---']);
fputcsv($output, ['Date', 'Orders', 'Revenue']);
$sales_sql = "SELECT 
    DATE(created_at) as date, 
    COUNT(*) as orders,
    SUM(total_amount) as revenue
    FROM orders 
    WHERE created_at BETWEEN ? AND ? AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)";
$stmt = $conn->prepare($sales_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['date'], $row['orders'], '£' . number_format($row['revenue'], 2)]);
}
$stmt->close();
fputcsv($output, []);

// 3. Status Distribution
fputcsv($output, ['--- ORDERS BY STATUS ---']);
fputcsv($output, ['Status', 'Count']);
$status_sql = "SELECT status, COUNT(*) as count FROM orders WHERE created_at BETWEEN ? AND ? GROUP BY status";
$stmt = $conn->prepare($status_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [ucfirst($row['status']), $row['count']]);
}
$stmt->close();
fputcsv($output, []);

// 4. Top Products
fputcsv($output, ['--- TOP SELLING PRODUCTS ---']);
fputcsv($output, ['Product Name', 'Quantity Sold', 'Total Revenue']);
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
    LIMIT 10";

$check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($check_table->num_rows > 0) {
    $stmt = $conn->prepare($products_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['product_name'], $row['total_qty'], '£' . number_format($row['total_sales'], 2)]);
    }
    $stmt->close();
}

fclose($output);
$conn->close();
exit;
?>
