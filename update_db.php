<?php
require_once('config.php');
$conn = getDbConnection();

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    delivery_method ENUM('delivery', 'collection') DEFAULT 'delivery',
    collection_code VARCHAR(6),
    collection_qr_url VARCHAR(255),
    collection_expires_at DATETIME,
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    shipping_address TEXT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'orders' created successfully\n";
} else {
    echo "Error creating table 'orders': " . $conn->error . "\n";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    delivery_method ENUM('delivery', 'collection') DEFAULT 'delivery',
    FOREIGN KEY (order_id) REFERENCES orders(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'order_items' created successfully\n";
} else {
    echo "Error creating table 'order_items': " . $conn->error . "\n";
}

// Check if delivery_method exists in cart table
$result = $conn->query("SHOW COLUMNS FROM cart LIKE 'delivery_method'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE cart ADD COLUMN delivery_method ENUM('delivery', 'collection') DEFAULT 'delivery'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'delivery_method' added to 'cart' table successfully\n";
    } else {
        echo "Error adding column to 'cart': " . $conn->error . "\n";
    }
} else {
    echo "Column 'delivery_method' already exists in 'cart' table\n";
}

// Check if payment_method exists in orders table
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'worldpay'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'payment_method' added to 'orders' table successfully\n";
    } else {
        echo "Error adding column to 'orders': " . $conn->error . "\n";
    }
} else {
    echo "Column 'payment_method' already exists in 'orders' table\n";
}

// Check if delivery_method exists in orders table
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_method'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN delivery_method ENUM('delivery', 'collection') DEFAULT 'delivery'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'delivery_method' added to 'orders' table successfully\n";
    } else {
        echo "Error adding column to 'orders': " . $conn->error . "\n";
    }
} else {
    echo "Column 'delivery_method' already exists in 'orders' table\n";
}

// Create wishlist table
$sql = "CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, product_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'wishlist' created successfully\n";
} else {
    echo "Error creating table 'wishlist': " . $conn->error . "\n";
}

closeDbConnection($conn);
?>
