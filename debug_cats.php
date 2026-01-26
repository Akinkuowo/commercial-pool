<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
$conn = getDbConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();
echo "Total Products: " . $row['count'] . "<br>";

$result = $conn->query("SELECT id, product_name, category FROM products LIMIT 5");
if (!$result) {
    echo "Error: " . $conn->error;
} else {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Cat: [" . $row['category'] . "]<br>";
    }
}
?>
