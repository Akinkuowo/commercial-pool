<?php
// include/currency_init.php - Initialize currency from session/cookie

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if currency is set in cookie but not in session
if (!isset($_SESSION['currency']) && isset($_COOKIE['currency'])) {
    $currency = strtoupper(trim($_COOKIE['currency']));
    $supportedCurrencies = ['USD', 'GBP', 'EUR'];
    
    if (in_array($currency, $supportedCurrencies)) {
        $_SESSION['currency'] = $currency;
    }
}

// Set default currency if not set
if (!isset($_SESSION['currency'])) {
    $_SESSION['currency'] = 'USD';
}

// Define currency data for use in templates
$currentCurrency = $_SESSION['currency'];

$exchangeRates = [
    'GBP' => 1.0,
    'USD' => 1.27,
    'EUR' => 1.20
];

$currencySymbols = [
    'GBP' => '£',
    'USD' => '$',
    'EUR' => '€'
];

// Helper functions
function getExchangeRate($currency) {
    global $exchangeRates;
    return $exchangeRates[$currency] ?? 1.0;
}

function getCurrencySymbol($currency) {
    global $currencySymbols;
    return $currencySymbols[$currency] ?? '£';
}

// Helper function to format price
function formatPrice($price, $currency = null) {
    global $currentCurrency, $exchangeRates, $currencySymbols;
    
    if ($currency === null) {
        $currency = $currentCurrency;
    }
    
    $rate = $exchangeRates[$currency] ?? 1.0;
    $convertedPrice = $price * $rate;
    $symbol = $currencySymbols[$currency] ?? '£';
    
    return $symbol . number_format($convertedPrice, 2);
}
?>
