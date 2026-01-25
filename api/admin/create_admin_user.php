<?php
// api/admin/create_admin_user.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth and super-admin privileges if necessary
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Can only be created by admins
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: Requires admin role']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$role = $_POST['role'] ?? 'editor';

// Validation
if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check if username/email exists
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or Email already exists']);
    exit;
}
$stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$status = 'active';

$sql = "INSERT INTO admin_users (username, email, password, first_name, last_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $username, $email, $hashed_password, $first_name, $last_name, $role, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User created successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create user: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
