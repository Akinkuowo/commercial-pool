<?php
// api/admin/get_media.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$directory = '../../assets/img/Products/';
$public_path = 'assets/img/Products/';

if (!is_dir($directory)) {
    if (!mkdir($directory, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Directory not found and cannot be created']);
        exit;
    }
}

$files = [];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

try {
    if ($handle = opendir($directory)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed_extensions)) {
                    $files[] = [
                        'name' => $entry,
                        'path' => $public_path . $entry,
                        'size' => filesize($directory . $entry),
                        'modified' => filemtime($directory . $entry)
                    ];
                }
            }
        }
        closedir($handle);
    }
    
    // Sort by newest first
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    echo json_encode(['success' => true, 'files' => $files]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
