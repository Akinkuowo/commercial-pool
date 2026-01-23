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
    $terms = isset($_POST['terms']) ? true : false;
    $marketing = isset($_POST['marketing']) ? 1 : 0;
    $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';
    
    // Validation
    $errors = [];
    
    // Email validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email already exists
        $emailCheckSql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($emailCheckSql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'An account with this email already exists';
        }
        $stmt->close();
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = 'Password must contain uppercase, lowercase, and numbers';
    }
    
    // Confirm password
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Required field validation
    if (empty($businessName)) {
        $errors[] = 'Business name is required';
    }
    
    if (empty($businessType)) {
        $errors[] = 'Business type is required';
    }
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($companyName)) {
        $errors[] = 'Company name is required';
    }
    
    if (empty($phoneNumber)) {
        $errors[] = 'Phone number is required';
    }
    
    if (empty($addressLine1)) {
        $errors[] = 'Address line 1 is required';
    }
    
    if (empty($townCity)) {
        $errors[] = 'Town/City is required';
    }
    
    if (empty($country)) {
        $errors[] = 'Country is required';
    }
    
    if (empty($postcode)) {
        $errors[] = 'Postcode is required';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the terms and conditions';
    }
    
    // Website URL validation (optional)
    if (!empty($websiteUrl) && !filter_var($websiteUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Invalid website URL format';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'errors' => $errors,
            'error' => implode(', ', $errors)
        ]);
        closeDbConnection($conn);
        exit;
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare SQL statement
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param(
        "ssssssssssssssssss",
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
        $postcode
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