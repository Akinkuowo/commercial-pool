<?php
// api/admin/update_settings.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

$success = true;
$error = '';

foreach ($_POST as $key => $value) {
    // Only allow specific keys for security (optional) or just save all
    // Ideally sanitize
    $clean_value = trim($value);
    $stmt->bind_param("sss", $key, $clean_value, $clean_value);
    if (!$stmt->execute()) {
        $success = false;
        $error = $stmt->error;
        break;
    }
}

$stmt->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update settings: ' . $error]);
}
?>
