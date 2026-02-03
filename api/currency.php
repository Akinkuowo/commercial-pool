<?php
// api/currency.php - Currency management API

error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supported currencies
$supportedCurrencies = ['USD', 'GBP', 'EUR'];

// Exchange rates (base currency: GBP)
$exchangeRates = [
    'GBP' => 1.0,
    'USD' => 1.27,
    'EUR' => 1.20
];

// Currency symbols
$currencySymbols = [
    'GBP' => '£',
    'USD' => '$',
    'EUR' => '€'
];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Get current currency
        $currentCurrency = $_SESSION['currency'] ?? 'GBP';
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'currency' => $currentCurrency,
            'symbol' => $currencySymbols[$currentCurrency],
            'rate' => $exchangeRates[$currentCurrency],
            'supported' => $supportedCurrencies,
            'rates' => $exchangeRates,
            'symbols' => $currencySymbols
        ]);
        
    } elseif ($method === 'POST') {
        // Update currency
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['currency'])) {
            throw new Exception('Currency code is required');
        }
        
        $currency = strtoupper(trim($data['currency']));
        
        // Validate currency
        if (!in_array($currency, $supportedCurrencies)) {
            throw new Exception('Invalid currency code. Supported: ' . implode(', ', $supportedCurrencies));
        }
        
        // Update session
        $_SESSION['currency'] = $currency;
        
        // Set cookie (expires in 1 year)
        $cookieExpiry = time() + (365 * 24 * 60 * 60);
        setcookie('currency', $currency, $cookieExpiry, '/');
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Currency updated successfully',
            'currency' => $currency,
            'symbol' => $currencySymbols[$currency],
            'rate' => $exchangeRates[$currency]
        ]);
        
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
