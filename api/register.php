<?php
// api/register.php - User Registration Handler

error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit;
}

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
    
    // Get POST data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $businessName = isset($_POST['business_name']) ? trim($_POST['business_name']) : '';
    $businessType = isset($_POST['business_type']) ? trim($_POST['business_type']) : '';
    $websiteUrl = isset($_POST['website_url']) ? trim($_POST['website_url']) : '';
    $vatNumber = isset($_POST['vat_number']) ? trim($_POST['vat_number']) : '';
    $companyRegNo = isset($_POST['company_registration_no']) ? trim($_POST['company_registration_no']) : '';
    $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $phoneNumber = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $mobileNumber = isset($_POST['mobile_number']) ? trim($_POST['mobile_number']) : '';
    $addressLine1 = isset($_POST['address_line1']) ? trim($_POST['address_line1']) : '';
    $addressLine2 = isset($_POST['address_line2']) ? trim($_POST['address_line2']) : '';
    $townCity = isset($_POST['town_city']) ? trim($_POST['town_city']) : '';
    $county = isset($_POST['county']) ? trim($_POST['county']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $postcode = isset($_POST['postcode']) ? trim($_POST['postcode']) : '';
    $customerType = isset($_POST['customer_type']) ? $_POST['customer_type'] : 'trade';
    $isTrade = ($customerType === 'trade') ? 1 : 0;
    
    // Validation
    $errors = [];
    
    // ... (omitting some lines for clarity but keep logic)
    
    // Required field validation (Only for Trade)
    if ($isTrade) {
        if (empty($businessName)) $errors[] = 'Business name is required';
        if (empty($businessType)) $errors[] = 'Business type is required';
    }
    
    // ... (rest of validation)
    
    // INSERT logic
    $isTradeValue = $isTrade ? 1 : 0;
    $sql = "INSERT INTO users (
        email, 
        password, 
        business_name, 
        business_type, 
        website_url, 
        vat_number, 
        company_registration_no, 
        first_name, 
        last_name, 
        company_name, 
        phone_number, 
        mobile_number, 
        address_line1, 
        address_line2, 
        town_city, 
        county, 
        country, 
        postcode, 
        is_active, 
        is_trade_customer,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssssi",
        $email,
        $hashedPassword,
        $businessName,
        $businessType,
        $websiteUrl,
        $vatNumber,
        $companyRegNo,
        $firstName,
        $lastName,
        $companyName,
        $phoneNumber,
        $mobileNumber,
        $addressLine1,
        $addressLine2,
        $townCity,
        $county,
        $country,
        $postcode,
        $isTradeValue
    );
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        
        // Optional: Start a session for the new user
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['is_trade_customer'] = 1;
        $_SESSION['logged_in'] = true;
        
        // Merge session wishlist to database
        if (isset($_SESSION['wishlist']) && !empty($_SESSION['wishlist'])) {
            try {
                $wishSql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
                $wishStmt = $conn->prepare($wishSql);
                if ($wishStmt) {
                    foreach ($_SESSION['wishlist'] as $productId) {
                        $wishStmt->bind_param("si", $userId, $productId);
                        $wishStmt->execute();
                    }
                    $wishStmt->close();
                }
                // Clear session wishlist after merging
                unset($_SESSION['wishlist']);
            } catch (Exception $e) {
                error_log('Error merging wishlist on registration: ' . $e->getMessage());
            }
        }
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'user_id' => $userId,
            'redirect' => !empty($redirect) ? $redirect : 'dashboard.php'
        ]);
    } else {
        throw new Exception('Failed to create account: ' . $stmt->error);
    }
    
    $stmt->close();
    closeDbConnection($conn);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>