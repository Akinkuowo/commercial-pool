<?php
// api/admin/check_username.php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_GET['username'])) {
    echo json_encode(['error' => 'Username parameter required']);
    exit;
}

$username = trim($_GET['username']);

try {
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $exists = $stmt->fetch() !== false;
    
    echo json_encode([
        'exists' => $exists,
        'available' => !$exists
    ]);
} catch (PDOException $e) {
    error_log("Error checking username: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>

---

<?php
// api/admin/check_email.php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_GET['email'])) {
    echo json_encode(['error' => 'Email parameter required']);
    exit;
}

$email = trim($_GET['email']);

try {
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $exists = $stmt->fetch() !== false;
    
    echo json_encode([
        'exists' => $exists,
        'available' => !$exists
    ]);
} catch (PDOException $e) {
    error_log("Error checking email: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>