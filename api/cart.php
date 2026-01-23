<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to get user identification
session_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
try {
    // Check if config file exists
    if (!file_exists('../config.php')) {
        throw new Exception('Configuration file not found');
    }
    require_once('../config.php');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit();
}

// Get or create user session ID (for guest users)
if (!isset($_SESSION['user_id'])) {
    // For guest users, use session ID as user identifier
    $_SESSION['user_id'] = session_id();
}

$user_id = $_SESSION['user_id'];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    if ($method === 'POST') {
        // Handle POST requests (add, update, remove)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add':
                addToCart($conn, $input, $user_id);
                break;
            case 'update':
                updateCart($conn, $input, $user_id);
                break;
            case 'remove':
                removeFromCart($conn, $input, $user_id);
                break;
            case 'clear':
                clearCart($conn, $user_id);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
        }
    } elseif ($method === 'GET') {
        // Handle GET requests (fetch cart)
        getCart($conn, $user_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
    
    if (isset($conn)) {
        closeDbConnection($conn);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function addToCart($conn, $input, $user_id) {
    $product_id = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 1);
    $delivery_method = isset($input['delivery_method']) && in_array($input['delivery_method'], ['delivery', 'collection']) ? $input['delivery_method'] : 'delivery';
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    // Check if product exists and is in stock
    $stmt = $conn->prepare("SELECT id, product_name, price, quantity, image FROM products WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    if ($product['quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock. Only ' . $product['quantity'] . ' available.']);
        return;
    }
    
    // Check if product with SAME delivery method already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND delivery_method = ?");
    $stmt->bind_param("sis", $user_id, $product_id, $delivery_method);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();
    $stmt->close();
    
    if ($cart_item) {
        // Update existing cart item
        $newQuantity = $cart_item['quantity'] + $quantity;
        
        if ($newQuantity > $product['quantity']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Cannot add more than available stock. Maximum ' . $product['quantity'] . ' available.'
            ]);
            return;
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $newQuantity, $cart_item['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new cart item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, delivery_method, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("siis", $user_id, $product_id, $quantity, $delivery_method);
        $stmt->execute();
        $stmt->close();
    }
    
    // Get total cart count
    $cart_count = getCartCount($conn, $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cart_count' => $cart_count,
        'total_items' => $cart_count
    ]);
}

function updateCart($conn, $input, $user_id) {
    $product_id = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        removeFromCart($conn, $input, $user_id);
        return;
    }
    
    // Check stock availability
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
        return;
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    if ($quantity > $product['quantity']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Insufficient stock. Only ' . $product['quantity'] . ' available.'
        ]);
        return;
    }
    
    // Update cart quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("isi", $quantity, $user_id, $product_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        return;
    }
    
    $stmt->close();
    
    // Get total cart count
    $cart_count = getCartCount($conn, $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cart_count' => $cart_count,
        'total_items' => $cart_count
    ]);
}

function removeFromCart($conn, $input, $user_id) {
    $product_id = intval($input['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    // Delete cart item
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("si", $user_id, $product_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        return;
    }
    
    $stmt->close();
    
    // Get total cart count
    $cart_count = getCartCount($conn, $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product removed from cart',
        'cart_count' => $cart_count,
        'total_items' => $cart_count
    ]);
}

function clearCart($conn, $user_id) {
    // Delete all cart items for user
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully',
        'cart_count' => 0,
        'total_items' => 0
    ]);
}

function getCart($conn, $user_id) {
    // First, check what columns exist in the products table
    $columns_check = $conn->query("SHOW COLUMNS FROM products");
    $available_columns = [];
    while ($row = $columns_check->fetch_assoc()) {
        $available_columns[] = $row['Field'];
    }
    
    // Build the SELECT query based on available columns
    $product_name = in_array('product_name', $available_columns) ? 'p.product_name' : 'p.name';
    $sku = in_array('sku_number', $available_columns) ? 'p.sku_number' : (in_array('sku', $available_columns) ? 'p.sku' : 'NULL');
    
    // Fetch cart items with product details
    $sql = "SELECT 
                c.id as cart_id,
                c.product_id,
                c.quantity as cart_quantity,
                c.delivery_method,
                {$product_name} as product_name,
                p.price,
                p.quantity as stock_quantity,
                p.image,
                {$sku} as sku,
                (p.price * c.quantity) as subtotal
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $conn->error, 'sql' => $sql]);
        return;
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_items = [];
    $total_items = 0;
    $subtotal = 0;
    
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = [
            'product_id' => $row['product_id'],
            'name' => $row['product_name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'sku' => $row['sku'],
            'quantity' => $row['cart_quantity'],
            'max_quantity' => $row['stock_quantity'],
            'delivery_method' => $row['delivery_method'] ?? 'delivery',
            'subtotal' => number_format($row['subtotal'], 2, '.', '')
        ];
        
        $total_items += $row['cart_quantity'];
        $subtotal += $row['subtotal'];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'cart' => $cart_items,
        'total_items' => $total_items,
        'cart_count' => $total_items,
        'subtotal' => number_format($subtotal, 2, '.', ''),
        'currency' => '£'
    ]);
}

function getCartCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return intval($row['total'] ?? 0);
}
?>