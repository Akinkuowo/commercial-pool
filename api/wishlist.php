<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once('../config.php');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'] ?? null;

try {
    $conn = getDbConnection();
    
    if ($method === 'POST') {
        // Handle POST requests (add, remove)
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add':
                addToWishlist($conn, $user_id, $input);
                break;
            case 'remove':
                removeFromWishlist($conn, $user_id, $input);
                break;
            case 'clear':
                clearWishlist($conn, $user_id);
                break;
            case 'get_ids':
                getWishlistIds($conn, $user_id);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } elseif ($method === 'GET') {
        // Handle GET requests (fetch wishlist items)
        getWishlist($conn, $user_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
    
    closeDbConnection($conn);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function addToWishlist($conn, $user_id, $input) {
    $product_id = intval($input['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    // Check if product exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    $stmt->close();
    
    if ($user_id) {
        // Database storage for logged-in users
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("si", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
        
        // Return updated list
        getWishlistIds($conn, $user_id, true);
    } else {
        // Session storage for guests
        if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];
        if (!in_array($product_id, $_SESSION['wishlist'])) {
            $_SESSION['wishlist'][] = $product_id;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Added to wishlist (Guest session)',
            'wishlist_count' => count($_SESSION['wishlist'])
        ]);
    }
}

function removeFromWishlist($conn, $user_id, $input) {
    $product_id = intval($input['product_id'] ?? 0);
    
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("si", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
        
        getWishlistIds($conn, $user_id, true);
    } else {
        if (isset($_SESSION['wishlist'])) {
            $key = array_search($product_id, $_SESSION['wishlist']);
            if ($key !== false) {
                unset($_SESSION['wishlist'][$key]);
                $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
            }
        }
        echo json_encode([
            'success' => true,
            'message' => 'Removed from wishlist',
            'wishlist_count' => count($_SESSION['wishlist'] ?? [])
        ]);
    }
}

function clearWishlist($conn, $user_id) {
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    $_SESSION['wishlist'] = [];
    echo json_encode(['success' => true, 'wishlist_count' => 0]);
}

function getWishlistIds($conn, $user_id, $return_json = true) {
    $ids = [];
    if ($user_id) {
        $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ids[] = intval($row['product_id']);
        }
        $stmt->close();
        // Keep session in sync for display consistency
        $_SESSION['wishlist'] = $ids;
    } else {
        $ids = $_SESSION['wishlist'] ?? [];
    }
    
    if ($return_json) {
        echo json_encode([
            'success' => true,
            'wishlist_ids' => $ids,
            'wishlist_count' => count($ids)
        ]);
    }
    return $ids;
}

function getWishlist($conn, $user_id) {
    $wishlist_ids = getWishlistIds($conn, $user_id, false);
    $wishlist_items = [];
    
    if (!empty($wishlist_ids)) {
        $placeholders = implode(',', array_fill(0, count($wishlist_ids), '?'));
        $stmt = $conn->prepare("
            SELECT id, product_name, price, image, quantity, sku_number, category
            FROM products
            WHERE id IN ($placeholders)
        ");
        $types = str_repeat('i', count($wishlist_ids));
        $stmt->bind_param($types, ...$wishlist_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $wishlist_items[] = [
                'id' => $row['id'],
                'product_name' => $row['product_name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'sku_number' => $row['sku_number'],
                'in_stock' => $row['quantity'] > 0,
                'category_name' => $row['category']
            ];
        }
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'wishlist' => $wishlist_items,
        'wishlist_count' => count($wishlist_items)
    ]);
}
?>
?>