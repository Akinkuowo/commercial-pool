<?php
// api/admin/process_login.php
session_start();
require_once '../../config.php';

// Get database connection
$conn = getDbConnection();

// Rate limiting check
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    closeDbConnection($conn);
    header('Location: ../../admin/admin_login.php?error=invalid');
    exit;
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
if (empty($username) || empty($password)) {
    closeDbConnection($conn);
    header('Location: ../../admin/admin_login.php?error=empty');
    exit;
}

try {
    // Check if username is email or username
    $stmt = $conn->prepare("
        SELECT id, username, email, password, first_name, last_name, role, status, 
               login_attempts, locked_until 
        FROM admin_users 
        WHERE (username = ? OR email = ?) 
        LIMIT 1
    ");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        // Log failed attempt
        logActivityAttempt($conn, null, 'failed_login', "Failed login attempt for username: $username", $ip_address, $user_agent);
        closeDbConnection($conn);
        header('Location: ../../admin/admin_login.php?error=invalid');
        exit;
    }

    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        closeDbConnection($conn);
        header('Location: ../../admin/admin_login.php?error=locked');
        exit;
    }

    // Check account status
    if ($user['status'] === 'inactive') {
        closeDbConnection($conn);
        header('Location: ../../admin/admin_login.php?error=inactive');
        exit;
    }

    if ($user['status'] === 'suspended') {
        closeDbConnection($conn);
        header('Location: ../../admin/admin_login.php?error=suspended');
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Increment login attempts
        $attempts = $user['login_attempts'] + 1;
        $locked_until = null;

        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $locked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }

        $stmt = $conn->prepare("
            UPDATE admin_users 
            SET login_attempts = ?, locked_until = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("isi", $attempts, $locked_until, $user['id']);
        $stmt->execute();
        $stmt->close();

        logActivityAttempt($conn, $user['id'], 'failed_login', "Failed login attempt", $ip_address, $user_agent);
        closeDbConnection($conn);
        
        if ($locked_until) {
            header('Location: ../../admin/admin_login.php?error=locked');
        } else {
            header('Location: ../../admin/admin_login.php?error=invalid');
        }
        exit;
    }

    // Successful login - Reset attempts
    $stmt = $conn->prepare("
        UPDATE admin_users 
        SET login_attempts = 0, locked_until = NULL, last_login = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();

    // Create session
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['admin_logged_in'] = true;

    // Create session token
    $session_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

    if ($remember) {
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    }

    $stmt = $conn->prepare("
        INSERT INTO admin_sessions (admin_id, session_token, ip_address, user_agent, expires_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $user['id'], $session_token, $ip_address, $user_agent, $expires_at);
    $stmt->execute();
    $stmt->close();

    $_SESSION['session_token'] = $session_token;

    // Log successful login
    logActivity($conn, $user['id'], 'login', 'User logged in successfully', $ip_address, $user_agent);

    // Close connection
    closeDbConnection($conn);

    // Redirect to dashboard
    header('Location: ../../admin/admin-dashboard.php');
    exit;

} catch (Exception $e) {
    error_log("Admin login error: " . $e->getMessage());
    closeDbConnection($conn);
    header('Location: ../../admin/admin_login.php?error=system');
    exit;
}

function logActivity($conn, $admin_id, $action, $description, $ip, $user_agent) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $admin_id, $action, $description, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function logActivityAttempt($conn, $admin_id, $action, $description, $ip, $user_agent) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $admin_id, $action, $description, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Failed to log activity attempt: " . $e->getMessage());
    }
}
?>