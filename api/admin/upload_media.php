<?php
// api/admin/upload_media.php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$upload_dir = '../../assets/img/Products/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Sanitize filename
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
// Prevent overwriting
$i = 1;
$original_filename = pathinfo($filename, PATHINFO_FILENAME);
while (file_exists($upload_dir . $filename)) {
    $filename = $original_filename . '_' . $i . '.' . $file_extension;
    $i++;
}

if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
    echo json_encode([
        'success' => true, 
        'message' => 'File uploaded successfully',
        'file' => [
            'name' => $filename,
            'path' => 'assets/img/Products/' . $filename
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>
