<?php
// api/admin/update_category.php
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

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : null;
$slug = trim($_POST['slug'] ?? '');

if ($id <= 0 || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Valid ID and Name are required']);
    exit;
}

// Prevent parent being self
if ($parent_id === $id) {
    echo json_encode(['success' => false, 'message' => 'Category cannot be its own parent']);
    exit;
}

// Generate slug if empty
if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
}

// Check for duplicate slug (excluding self)
$stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
$stmt->bind_param("si", $slug, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Slug already exists']);
    exit;
}
$stmt->close();

$sql = "UPDATE categories SET name = ?, slug = ?, parent_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $name, $slug, $parent_id, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update category: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
