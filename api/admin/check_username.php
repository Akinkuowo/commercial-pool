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
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->fetch_assoc() !== null;
    $stmt->close();
    closeDbConnection($conn);
    
    echo json_encode([
        'exists' => $exists,
        'available' => !$exists
    ]);
} catch (Exception $e) {
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
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->fetch_assoc() !== null;
    $stmt->close();
    closeDbConnection($conn);
    
    echo json_encode([
        'exists' => $exists,
        'available' => !$exists
    ]);
} catch (Exception $e) {
    error_log("Error checking email: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>