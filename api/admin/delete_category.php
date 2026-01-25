<?php
// api/admin/delete_category.php
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

$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
