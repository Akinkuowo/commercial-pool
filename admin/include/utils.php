<?php
/**
 * Log an administrative activity
 * 
 * @param mysqli $conn Database connection
 * @param string $action Action name (e.g., 'create_product', 'update_order')
 * @param string $description Detailed description of the action
 * @return bool Success or failure
 */
function logActivity($conn, $action, $description) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    $admin_id = $_SESSION['admin_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Ensure table exists
    $conn->query("
        CREATE TABLE IF NOT EXISTS admin_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT,
            action VARCHAR(50),
            description TEXT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (admin_id),
            INDEX (created_at)
        )
    ");

    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $admin_id, $action, $description, $ip_address, $user_agent);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    return false;
}
?>
