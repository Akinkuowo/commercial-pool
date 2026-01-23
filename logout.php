<?php
// logout.php - User Logout Handler
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session before any output
session_start();

require_once 'config.php';

try {
    // If user has a remember token, remove it
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        try {
            $conn = getDbConnection();
            
            if ($conn) {
                // Delete the token from database
                $sql = "DELETE FROM remember_tokens WHERE token = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                    $stmt->close();
                }
                
                closeDbConnection($conn);
            }
        } catch (Exception $e) {
            error_log('Logout token deletion error: ' . $e->getMessage());
        }
        
        // Delete the cookie - Use array format for better compatibility
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => false, // Set to true only if using HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
    // Log the logout
    if (isset($_SESSION['user_id'])) {
        error_log('User logged out: ' . $_SESSION['user_id']);
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 3600,
            $params["path"],
            $params["domain"],
            false, // secure - set to true for HTTPS
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page with logout success message
    header('Location: index.php?logout=success');
    exit;
    
} catch (Exception $e) {
    // Log the error
    error_log('Logout error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Try to destroy session anyway
    session_destroy();
    
    // Redirect to home page
    header('Location: index.php');
    exit;
}
?>