<?php
// api/admin/delete_category.php
session_start();
require_once '../../config.php';
require_once '../admin/include/utils.php';

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $vars);
    $id = intval($vars['id'] ?? 0);
} else {
    $id = intval($_GET['id'] ?? 0);
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Optional: Check if products depend on this category
/*
$check = $conn->prepare("SELECT id FROM products WHERE category_id = ?");
$check->bind_param("i", $id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete: Products are assigned to this category']);
    exit;
}
$check->close();
*/

// Get category name before deletion
$check = $conn->prepare("SELECT name FROM categories WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();
$category = $result->fetch_assoc();
$check->close();

if (!$category) {
    echo json_encode(['success' => false, 'message' => 'Category not found']);
    exit;
}

$category_name = $category['name'];

$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Log activity
    logActivity($conn, 'delete_category', "Deleted category: $category_name (ID: $id)");
    
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
