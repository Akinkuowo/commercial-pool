<?php
// api/admin/update_admin_user.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$conn = getDbConnection();

$id = intval($_POST['id'] ?? 0);
$email = trim($_POST['email'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$role = $_POST['role'] ?? 'editor';
$status = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

if ($id <= 0 || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID or Email']);
    exit;
}

// Check duplicates (exclude self)
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already in use']);
    exit;
}
$stmt->close();

if (!empty($password)) {
    // Update with password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE admin_users SET email = ?, first_name = ?, last_name = ?, role = ?, status = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $email, $first_name, $last_name, $role, $status, $hashed, $id);
} else {
    // Update without password
    $sql = "UPDATE admin_users SET email = ?, first_name = ?, last_name = ?, role = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $email, $first_name, $last_name, $role, $status, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
