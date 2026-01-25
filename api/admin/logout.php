<?php
// api/admin/logout.php
session_start();
require_once '../../config.php';

// Get database connection
$conn = getDbConnection();

// Log logout activity if user is logged in
if (isset($_SESSION['admin_id']) && isset($_SESSION['session_token'])) {
    $admin_id = $_SESSION['admin_id'];
    $session_token = $_SESSION['session_token'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Log logout activity
    try {
        $stmt = $conn->prepare("
            INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
            VALUES (?, 'logout', 'User logged out', ?, ?)
        ");
        $stmt->bind_param("iss", $admin_id, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        // Delete session from database
        $stmt = $conn->prepare("DELETE FROM admin_sessions WHERE session_token = ?");
        $stmt->bind_param("s", $session_token);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Close database connection
closeDbConnection($conn);

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../../admin/admin_login.php?logout=success');
exit;
?>