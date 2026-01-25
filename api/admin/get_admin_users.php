<?php
// api/admin/get_admin_users.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth and role (only admins can view other admins)
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

$sql = "SELECT id, username, email, first_name, last_name, role, status, last_login, created_at FROM admin_users ORDER BY created_at DESC";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['success' => true, 'users' => $users]);
$conn->close();
?>
