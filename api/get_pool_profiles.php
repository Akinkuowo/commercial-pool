<?php
// api/get_pool_profiles.php

error_reporting(E_ALL);
ini_set('display_errors', 0); // JSON response, don't break with errors

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../config.php';

try {
    $conn = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    // Fetch pool profiles with equipment count
    $sql = "SELECT 
                id,
                pool_name,
                pool_type,
                pool_size,
                pool_volume,
                pool_usage,
                pool_material,
                filter_type,
                filter_model,
                created_at,
                updated_at
            FROM pool_profiles 
            WHERE user_id = ? 
            ORDER BY updated_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $profiles = [];
    while ($row = $result->fetch_assoc()) {
        // Count equipment/components saved for this pool (if you have a related table)
        // For now, we'll use a simple count based on non-null filter fields
        $equipmentCount = 0;
        if (!empty($row['filter_type'])) $equipmentCount++;
        if (!empty($row['filter_model'])) $equipmentCount++;
        
        $profiles[] = [
            'id' => $row['id'],
            'name' => $row['pool_name'] ?: 'Unnamed Pool',
            'type' => ucfirst($row['pool_type'] ?: 'Unknown'),
            'size' => $row['pool_size'] ?: 'N/A',
            'volume' => $row['pool_volume'] ? number_format($row['pool_volume']) . ' litres' : 'N/A',
            'usage' => ucfirst($row['pool_usage'] ?: 'General'),
            'material' => ucfirst($row['pool_material'] ?: 'N/A'),
            'filter_type' => $row['filter_type'],
            'filter_model' => $row['filter_model'],
            'equipment_count' => $equipmentCount,
            'created' => date('d M Y', strtotime($row['created_at'])),
            'updated' => date('d M Y', strtotime($row['updated_at']))
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'profiles' => $profiles,
        'count' => count($profiles)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) closeDbConnection($conn);
}
?>