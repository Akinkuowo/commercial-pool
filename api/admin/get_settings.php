<?php
// api/admin/get_settings.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

// Create table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

$sql = "SELECT setting_key, setting_value FROM site_settings";
$result = $conn->query($sql);

$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Default values if empty
$defaults = [
    'site_name' => 'Commerial Pool Equipment Leisure',
    'support_email' => 'support@Commerial Pool Equipmentleisure.com',
    'contact_phone' => '+44 151 334 0222',
    'address' => 'Unit 2, Woodway Ct, Thursby Rd, Croft Business Park, Bromborough, Wirral CH62 3PR',
    'currency_symbol' => '£',
    'seo_title' => 'Caravan & Camping Supplies | Commerial Pool Equipment Leisure',
    'seo_description' => 'Your one-stop shop for caravan, camping, and pool supplies.',
    'google_analytics_id' => ''
];

$response = array_merge($defaults, $settings);

echo json_encode(['success' => true, 'settings' => $response]);
$conn->close();
?>
