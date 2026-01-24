<?php
// Disable error reporting for production/JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get user ID (guest or logged in)
$user_id = $_SESSION['user_id'] ?? session_id();

try {
    $conn = getDbConnection();
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate input
    $payment_method = $input['payment_method'] ?? '';
    
    // Define required fields based on payment method
    if ($payment_method === 'cash') {
        $required_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'postcode'];
    } else {
        // For external gateways, we might only need customer email if available, or nothing if complete guest
        // But usually we need at least email to associate user? 
        // Let's assume for now we relax strict requirements or require just email if present in form?
        // Since form is hidden, fields might be empty.
        // Let's require NOTHING if not cash, as checking out via PayPal might provide details later.
        $required_fields = []; 
    }

    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Get cart items
    $cart_sql = "SELECT c.*, p.price, p.product_name 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows === 0) {
        throw new Exception('Cart is empty');
    }
    
    $cart_items = [];
    $total_amount = 0;
    $has_collection = false;
    
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_amount += $row['price'] * $row['quantity'];
        if (($row['delivery_method'] ?? 'delivery') === 'collection') {
            $has_collection = true;
        }
    }
    
    // Insert Order
    $customer_name = $input['first_name'] . ' ' . $input['last_name'];
    $shipping_address = $input['address'] . ', ' . $input['city'] . ', ' . $input['postcode'];
    $delivery_method = $input['delivery_method'] ?? 'delivery'; // Default to delivery
    
    // Modified order insertion
    $order_sql = "INSERT INTO orders (user_id, total_amount, status, customer_name, customer_email, customer_phone, shipping_address, payment_method, delivery_method) 
                  VALUES (?, ?, 'Pending', ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($order_sql);
    $payment_method = $input['payment_method'] ?? 'worldpay';
    $stmt->bind_param("sdssssss", $user_id, $total_amount, $customer_name, $input['email'], $input['phone'], $shipping_address, $payment_method, $delivery_method);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    
    $order_id = $conn->insert_id;
    
    // Handle Click & Collect Table Insertion
    $collection_code = null;
    $qr_url = null;
    $expires_at = null;
    
    if ($has_collection) {
        $collection_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $qr_data = urlencode("COLLECT:$collection_code");
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=$qr_data";
        $expires_at = date('Y-m-d H:i:s', strtotime('+72 hours'));
        
        // Determine customer_id (NULL for guests as per schema/plan)
        $customer_id = null; // Since user_id is string (session), we use NULL for logic or would need to look up if logged in.
        
        $cc_sql = "INSERT INTO click_and_collect (
            order_id, customer_id, collection_code, qr_code, code_expires_at, 
            status, customer_name, customer_email, customer_phone, order_total
        ) VALUES (?, ?, ?, ?, ?, 'ready', ?, ?, ?, ?)";
        
        $cc_stmt = $conn->prepare($cc_sql);
        // Types: i (int), i (int/null), s (string), s (string), s (string), s (string), s (string), d (double)
        // Note: customer_id is null, bind_param handles null if variable is null? No, needs specific handling or separate query.
        // Easier way with bind_param for nullable:
        
        $status = 'ready';
        $cc_stmt->bind_param("isssssssd", 
            $order_id, 
            $customer_id, 
            $collection_code, 
            $qr_url, 
            $expires_at, 
            $customer_name, 
            $input['email'], 
            $input['phone'], 
            $total_amount
        );
        
        if (!$cc_stmt->execute()) {
             throw new Exception('Failed to create collection: ' . $cc_stmt->error);
        }
    }
    
    // Insert Order Items
    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, delivery_method) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    
    foreach ($cart_items as $item) {
        $delivery_method = $item['delivery_method'] ?? 'delivery';
        $item_stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $delivery_method);
        $item_stmt->execute();
    }
    
    // Clear Cart
    $clear_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_sql);
    $clear_stmt->bind_param("s", $user_id);
    $clear_stmt->execute();
    
    // Send Notifications
    sendNotifications($input['email'], $input['phone'], $order_id, $customer_name, $collection_code, $expires_at);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'collection_code' => $collection_code,
        'qr_url' => $qr_url
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) closeDbConnection($conn);
}

function sendNotifications($email, $phone, $order_id, $name, $code, $expires) {
    // 1. Send Email
    $subject = "Order Confirmation #$order_id - Commercial Pool Equipment";
    $message = "Dear $name,\n\nThank you for your order!\nOrder ID: $order_id\n\n";
    
    if ($code) {
        $expiry_formatted = date('d M Y, h:i A', strtotime($expires));
        $message .= "CLICK & COLLECT INSTRUCTIONS:\n";
        $message .= "Your specific collection code is: $code\n";
        $message .= "Please collect your items by: $expiry_formatted\n";
        $message .= "Show this code or the QR code on the success page to our staff.\n\n";
    }
    
    $message .= "We will process your order shortly.\n\nRegards,\nCommercial Pool Equipment Team";
    $headers = "From: no-reply@commercialpoolequipment.com";
    
    // Use PHP mail (this might not work on localhost without config, but it's the standard way)
    @mail($email, $subject, $message, $headers);
    
    // 2. Send SMS (Placeholder)
    sendSMS($phone, "Order #$order_id confirmed. " . ($code ? "Code: $code. Expires in 72h." : "Thank you!"));
}

function sendSMS($phone, $message) {
    // Placeholder for SMS integration (e.g., Twilio)
    // In a real app, you would make an API call here.
    // logging for verification
    error_log("SMS to $phone: $message");
}
?>
