<?php
// api/admin/export_orders.php
session_start();
require_once '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../admin/admin_login.php');
    exit;
}

$conn = getDbConnection();

// Pagination - we want all for export, so ignoring pagination but keeping filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if ($payment_filter) {
    $where_conditions[] = "payment_status = ?";
    $params[] = $payment_filter;
    $param_types .= 's';
}

if ($search) {
    $where_conditions[] = "(id LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if ($date_from) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if ($date_to) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get orders
$sql = "SELECT * FROM orders $where_clause ORDER BY created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Order ID', 'Date', 'Customer Name', 'Customer Email', 'Customer Phone',
    'Total Amount', 'Payment Status', 'Order Status', 'Created At'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        date('Y-m-d H:i', strtotime($row['created_at'])),
        $row['customer_name'],
        $row['customer_email'],
        $row['customer_phone'] ?? 'N/A',
        $row['total_amount'],
        $row['payment_status'],
        $row['status'],
        $row['created_at']
    ]);
}

fclose($output);
if (isset($stmt)) $stmt->close();
$conn->close();
exit;
?>
