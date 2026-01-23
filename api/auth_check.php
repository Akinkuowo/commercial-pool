<?php
// auth_check.php - Authentication Helper
// Include this at the top of protected pages to ensure user is logged in

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

function checkAuth($redirectToLogin = true) {
    // Check if user is logged in via session
    if (isLoggedIn()) {
        return true;
    }
    
    // Check if user has a remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        try {
            $conn = getDbConnection();
            
            // Verify token
            $sql = "SELECT rt.user_id, u.email, u.first_name, u.last_name, u.is_active, u.is_trade_customer
                    FROM remember_tokens rt
                    JOIN users u ON rt.user_id = u.id
                    WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Restore session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['is_trade_customer'] = $user['is_trade_customer'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Update last login
                $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("i", $user['user_id']);
                $updateStmt->execute();
                $updateStmt->close();
                
                $stmt->close();
                closeDbConnection($conn);
                
                return true;
            }
            
            $stmt->close();
            closeDbConnection($conn);
            
            // Invalid or expired token - delete it
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            
        } catch (Exception $e) {
            error_log('Auth check error: ' . $e->getMessage());
        }
    }
    
    // Not logged in - redirect if required
    if ($redirectToLogin) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
    
    return false;
}

function requireAuth() {
    checkAuth(true);
}

function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function getUserEmail() {
    return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
}

function getUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

function isTradeCustomer() {
    return isset($_SESSION['is_trade_customer']) && $_SESSION['is_trade_customer'] == 1;
}

// Check session timeout (optional - 2 hours)
function checkSessionTimeout($timeout = 7200) {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            // Session expired
            session_unset();
            session_destroy();
            header('Location: login.php?error=timeout');
            exit;
        }
        // Update last activity time
        $_SESSION['login_time'] = time();
    }
}
?>