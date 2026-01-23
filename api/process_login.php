<?php
// process_login.php - User Login Handler
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set session parameters BEFORE starting session
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 3600);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php?error=invalid');
    exit;
}

// Adjust path based on where process_login.php is located
$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        error_log('Config file not found');
        header('Location: ../login.php?error=config');
        exit;
    }
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
    $remember = isset($_POST['remember']) ? true : false;
    $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';
    
    // Validation
    if (empty($email) || empty($password)) {
        error_log('Login failed: Empty email or password');
        header('Location: ../login.php?error=empty');
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('Login failed: Invalid email format');
        header('Location: ../login.php?error=invalid');
        exit;
    }
    
    // Check for user in database
    $sql = "SELECT id, email, password, first_name, last_name, is_active, is_trade_customer, 
                   failed_login_attempts, last_failed_login 
            FROM users 
            WHERE email = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User not found
        error_log('Login failed: User not found - ' . $email);
        $stmt->close();
        closeDbConnection($conn);
        header('Location: ../login.php?error=not_found');
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Check if account is active
    if ($user['is_active'] != 1) {
        error_log('Login failed: Account inactive - ' . $email);
        closeDbConnection($conn);
        header('Location: ../login.php?error=inactive');
        exit;
    }
    
    // Check if account is locked (5 failed attempts in last 15 minutes)
    if ($user['failed_login_attempts'] >= 5) {
        $lockoutTime = strtotime($user['last_failed_login']) + (15 * 60); // 15 minutes
        if (time() < $lockoutTime) {
            error_log('Login failed: Account locked - ' . $email);
            closeDbConnection($conn);
            header('Location: ../login.php?error=locked');
            exit;
        } else {
            // Reset failed attempts after lockout period
            $resetSql = "UPDATE users SET failed_login_attempts = 0, last_failed_login = NULL WHERE id = ?";
            $resetStmt = $conn->prepare($resetSql);
            $resetStmt->bind_param("i", $user['id']);
            $resetStmt->execute();
            $resetStmt->close();
        }
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Increment failed login attempts
        error_log('Login failed: Invalid password - ' . $email);
        $updateSql = "UPDATE users 
                      SET failed_login_attempts = failed_login_attempts + 1,
                          last_failed_login = NOW() 
                      WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        closeDbConnection($conn);
        header('Location: ../login.php?error=invalid');
        exit;
    }
    
    // Successful login - Reset failed attempts
    $resetSql = "UPDATE users 
                 SET failed_login_attempts = 0, 
                     last_failed_login = NULL,
                     last_login = NOW() 
                 WHERE id = ?";
    $resetStmt = $conn->prepare($resetSql);
    $resetStmt->bind_param("i", $user['id']);
    $resetStmt->execute();
    $resetStmt->close();
    
    // REGENERATE SESSION ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    $_SESSION['is_trade_customer'] = $user['is_trade_customer'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Merge session wishlist to database
    if (isset($_SESSION['wishlist']) && !empty($_SESSION['wishlist'])) {
        try {
            $wishSql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
            $wishStmt = $conn->prepare($wishSql);
            if ($wishStmt) {
                foreach ($_SESSION['wishlist'] as $productId) {
                    $wishStmt->bind_param("si", $user['id'], $productId);
                    $wishStmt->execute();
                }
                $wishStmt->close();
            }
            // Clear session wishlist after merging
            unset($_SESSION['wishlist']);
        } catch (Exception $e) {
            error_log('Error merging wishlist: ' . $e->getMessage());
        }
    }
    
    // Log the session data for debugging
    error_log('Login successful for: ' . $email);
    error_log('Session ID: ' . session_id());
    error_log('Session Data: ' . print_r($_SESSION, true));
    
    // Handle "Remember Me" functionality
    if ($remember) {
        // Generate a secure token
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database (if table exists)
        $tokenSql = "INSERT INTO remember_tokens (user_id, token, expires_at) 
                     VALUES (?, ?, FROM_UNIXTIME(?))
                     ON DUPLICATE KEY UPDATE token = ?, expires_at = FROM_UNIXTIME(?)";
        $tokenStmt = $conn->prepare($tokenSql);
        if ($tokenStmt) {
            $tokenStmt->bind_param("isisi", $user['id'], $token, $expiry, $token, $expiry);
            $tokenStmt->execute();
            $tokenStmt->close();
            
            // Set cookie
            setcookie('remember_token', $token, [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => false, // Set to true if using HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }
    
    closeDbConnection($conn);
    
    // Force session write
    session_write_close();
    
    // Restart session immediately to verify
    session_start();
    
    // Verify session was set
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        error_log('ERROR: Session not set after login!');
        error_log('Session after restart: ' . print_r($_SESSION, true));
        header('Location: ../login.php?error=session');
        exit;
    }
    
    // Redirect to index page
    // Redirect to intended page or index
    if (!empty($redirect)) {
        header('Location: ../' . $redirect);
    } else {
        header('Location: ../dashboard.php');
    }
    exit;
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    header('Location: ../login.php?error=system');
    exit;
}
?>