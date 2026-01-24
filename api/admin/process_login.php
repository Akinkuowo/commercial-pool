<?php
// api/admin/process_login.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Rate limiting check
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin_login.php?error=invalid');
    exit;
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
if (empty($username) || empty($password)) {
    header('Location: ../../admin_login.php?error=empty');
    exit;
}

try {
    // Check if username is email or username
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, first_name, last_name, role, status, 
               login_attempts, locked_until 
        FROM admin_users 
        WHERE (username = :username OR email = :username) 
        LIMIT 1
    ");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Log failed attempt
        logActivityAttempt($pdo, null, 'failed_login', "Failed login attempt for username: $username", $ip_address, $user_agent);
        header('Location: ../../admin_login.php?error=invalid');
        exit;
    }

    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        header('Location: ../../admin_login.php?error=locked');
        exit;
    }

    // Check account status
    if ($user['status'] === 'inactive') {
        header('Location: ../../admin_login.php?error=inactive');
        exit;
    }

    if ($user['status'] === 'suspended') {
        header('Location: ../../admin_login.php?error=suspended');
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

        $stmt = $pdo->prepare("
            UPDATE admin_users 
            SET login_attempts = :attempts, locked_until = :locked_until 
            WHERE id = :id
        ");
        $stmt->execute([
            'attempts' => $attempts,
            'locked_until' => $locked_until,
            'id' => $user['id']
        ]);

        logActivityAttempt($pdo, $user['id'], 'failed_login', "Failed login attempt", $ip_address, $user_agent);
        
        if ($locked_until) {
            header('Location: ../../admin_login.php?error=locked');
        } else {
            header('Location: ../../admin_login.php?error=invalid');
        }
        exit;
    }

    // Successful login - Reset attempts
    $stmt = $pdo->prepare("
        UPDATE admin_users 
        SET login_attempts = 0, locked_until = NULL, last_login = NOW() 
        WHERE id = :id
    ");
    $stmt->execute(['id' => $user['id']]);

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

    $stmt = $pdo->prepare("
        INSERT INTO admin_sessions (admin_id, session_token, ip_address, user_agent, expires_at)
        VALUES (:admin_id, :session_token, :ip_address, :user_agent, :expires_at)
    ");
    $stmt->execute([
        'admin_id' => $user['id'],
        'session_token' => $session_token,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'expires_at' => $expires_at
    ]);

    $_SESSION['session_token'] = $session_token;

    // Log successful login
    logActivity($pdo, $user['id'], 'login', 'User logged in successfully', $ip_address, $user_agent);

    // Redirect to dashboard
    header('Location: ../../admin/dashboard.php');
    exit;

} catch (PDOException $e) {
    error_log("Admin login error: " . $e->getMessage());
    header('Location: ../../admin_login.php?error=system');
    exit;
}

function logActivity($pdo, $admin_id, $action, $description, $ip, $user_agent) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
            VALUES (:admin_id, :action, :description, :ip_address, :user_agent)
        ");
        $stmt->execute([
            'admin_id' => $admin_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
            'user_agent' => $user_agent
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function logActivityAttempt($pdo, $admin_id, $action, $description, $ip, $user_agent) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent)
            VALUES (:admin_id, :action, :description, :ip_address, :user_agent)
        ");
        $stmt->execute([
            'admin_id' => $admin_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
            'user_agent' => $user_agent
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log activity attempt: " . $e->getMessage());
    }
}
?>