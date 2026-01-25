<?php
// update_categories_db.php
require_once 'config.php';
$conn = getDbConnection();

// 1. Add parent_id column if not exists
$check_col = $conn->query("SHOW COLUMNS FROM categories LIKE 'parent_id'");
if ($check_col->num_rows === 0) {
    echo "Adding parent_id column...<br>";
    $conn->query("ALTER TABLE categories ADD COLUMN parent_id INT NULL DEFAULT NULL AFTER id");
    $conn->query("ALTER TABLE categories ADD INDEX (parent_id)");
} else {
    echo "parent_id column exists.<br>";
}

// 2. Populate Categories
$categories = [
    'Shop All' => [],
    'Pump & Filters' => ['Pumps', 'Filters'],
    'Cleaners' => [],
    'Lights' => [],
    'Covers' => ['Enclosures'],
    'Heaters' => [],
    'Competition' => ['Line Ropes', 'Life Guide Equipments', 'Starting Blocks', 'Turn Indicators', 'Water Polo', 'Pool Separation Walls'],
    'Pool Side' => ['Fountains', 'Ladders', 'Hoists'],
    'Sauna, Spa & Therapy' => []
];

foreach ($categories as $parent => $children) {
    // Insert/Get Parent
    $slug = strtolower(str_replace([' ', '&'], ['-', 'and'], $parent));
    // Check if exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->bind_param("s", $parent);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $parent_id = $res->fetch_assoc()['id'];
        echo "Parent '$parent' exists (ID: $parent_id).<br>";
    } else {
        // REMOVED 'description' column
        $stmt_ins = $conn->prepare("INSERT INTO categories (name, slug, created_at) VALUES (?, ?, NOW())");
        $stmt_ins->bind_param("ss", $parent, $slug);
        $stmt_ins->execute();
        $parent_id = $stmt_ins->insert_id;
        echo "Created Parent '$parent' (ID: $parent_id).<br>";
    }
    
    // Process Children
    foreach ($children as $child) {
        $child_slug = strtolower(str_replace([' ', '&'], ['-', 'and'], $child));
        
        // Check if child exists
        $stmt_c = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt_c->bind_param("s", $child);
        $stmt_c->execute();
        $res_c = $stmt_c->get_result();
        
        if ($res_c->num_rows > 0) {
            $child_row = $res_c->fetch_assoc();
            // Update parent if null
            $conn->query("UPDATE categories SET parent_id = $parent_id WHERE id = " . $child_row['id']);
            echo "updated Child '$child' linked to '$parent'.<br>";
        } else {
            // REMOVED 'description' column
            $stmt_ins_c = $conn->prepare("INSERT INTO categories (name, slug, parent_id, created_at) VALUES (?, ?, ?, NOW())");
            $stmt_ins_c->bind_param("ssi", $child, $child_slug, $parent_id);
            $stmt_ins_c->execute();
            echo "Created Child '$child' linked to '$parent'.<br>";
        }
    }
}

echo "Done.";
?>
