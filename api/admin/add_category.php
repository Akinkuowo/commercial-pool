<?php
// api/admin/add_category.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

$name = trim($_POST['name'] ?? '');
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : null;
$slug = trim($_POST['slug'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

// Generate slug if empty
if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
}

// Check for duplicate slug
$stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    // Append timestamp to make unique if duplicate
    $slug .= '-' . time();
}
$stmt->close();

$sql = "INSERT INTO categories (name, slug, parent_id, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $name, $slug, $parent_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Category added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add category: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
