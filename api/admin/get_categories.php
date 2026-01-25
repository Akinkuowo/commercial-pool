<?php
// api/admin/get_categories.php
require_once '../../config.php';
header('Content-Type: application/json');

$conn = getDbConnection();

// Fetch all categories
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM products p WHERE p.category = c.slug) as product_count 
        FROM categories c
        ORDER BY c.parent_id ASC, c.name ASC"; // Get parents first if possible, or just sort

$result = $conn->query($sql);

$categories = [];
$lookup = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['parent_id'] = $row['parent_id'] ? (int)$row['parent_id'] : null;
        $row['children'] = []; // Init children array
        $lookup[$row['id']] = $row;
        $categories[] = &$lookup[$row['id']];
    }
}

// Build Tree
$tree = [];
foreach ($lookup as $id => &$category) {
    if ($category['parent_id'] && isset($lookup[$category['parent_id']])) {
        // Add to parent's children
        $lookup[$category['parent_id']]['children'][] = &$category;
    } else {
        // Is root
        $tree[] = &$category;
    }
}

echo json_encode(['success' => true, 'categories' => $tree]);
$conn->close();
?>
