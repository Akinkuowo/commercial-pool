<?php
// api/admin/get_activity_log.php
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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Create table if not exists (safeguard)
$conn->query("
    CREATE TABLE IF NOT EXISTS admin_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT,
        action VARCHAR(50),
        description TEXT,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (admin_id),
        INDEX (created_at)
    )
");

// Get total count
$count_res = $conn->query("SELECT COUNT(*) as total FROM admin_activity_log");
$total = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Fetch logs
$sql = "SELECT 
            l.id,
            l.action,
            l.description,
            l.ip_address,
            l.created_at,
            u.username,
            u.first_name,
            u.last_name
        FROM admin_activity_log l
        LEFT JOIN admin_users u ON l.admin_id = u.id
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $row['admin_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
    if (empty($row['admin_name'])) $row['admin_name'] = $row['username'] ?? 'System / Deleted User';
    $logs[] = $row;
}

echo json_encode([
    'success' => true, 
    'logs' => $logs,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total
    ]
]);

$stmt->close();
$conn->close();
?>
