<?php
// api/categories.php - Fetch categories from database

error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Configuration file not found'
    ]);
    exit;
}

require_once $configPath;

try {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Fetch all categories with parent-child relationships
    $sql = "SELECT 
                id,
                name,
                slug,
                parent_id,
                created_at
            FROM categories 
            ORDER BY parent_id ASC, name ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $categories = [];
    $categoryMap = [];
    
    // First pass: create all categories
    while ($row = $result->fetch_assoc()) {
        $categoryMap[$row['id']] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'parent_id' => $row['parent_id'],
            'children' => []
        ];
    }
    
    // Second pass: build hierarchy
    foreach ($categoryMap as $id => $category) {
        if ($category['parent_id'] === null || $category['parent_id'] === 0) {
            // Top-level category
            $categories[] = &$categoryMap[$id];
        } else {
            // Child category - add to parent
            if (isset($categoryMap[$category['parent_id']])) {
                $categoryMap[$category['parent_id']]['children'][] = &$categoryMap[$id];
            }
        }
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => count($categoryMap)
    ]);
    
    closeDbConnection($conn);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>