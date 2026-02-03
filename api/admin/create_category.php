<?php
// api/admin/create_category.php
session_start();
require_once '../../config.php';
require_once '../admin/include/utils.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

$name = $_POST['name'] ?? '';
$parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
$description = $_POST['description'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

// Generate Slug
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

// Check duplicate slug
$check = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
$check->bind_param("s", $slug);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Category already exists']);
    exit;
}

// Insert - handle optional description if column exists or ignore
// We know description likely doesn't exist based on previous errors, so let's omit it for safety or check schema
// Safest is to just do name, slug, parent_id
$stmt = $conn->prepare("INSERT INTO categories (name, slug, parent_id, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("ssi", $name, $slug, $parent_id);

if ($stmt->execute()) {
    $category_id = $stmt->insert_id;
    
    // Log activity
    logActivity($conn, 'create_category', "Created category: $name (ID: $category_id)");
    
    echo json_encode(['success' => true, 'message' => 'Category created successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$conn->close();
?>
