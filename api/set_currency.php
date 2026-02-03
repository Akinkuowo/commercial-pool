<?php
// api/set_currency.php
session_start();
header('Content-Type: application/json');

if (isset($_POST['currency'])) {
    $currency = strtoupper($_POST['currency']);
    if (in_array($currency, ['GBP', 'EUR', 'USD'])) {
        $_SESSION['currency'] = $currency;
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false]);
?>
