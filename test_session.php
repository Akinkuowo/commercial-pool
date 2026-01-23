<?php
// session_test.php - Place this in your root directory to test sessions
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set session parameters
ini_set('session.cookie_path', '/');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Session Debug Information</h1>";
echo "<hr>";

echo "<h2>Session Status</h2>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";

echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookies</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>Session Configuration</h2>";
echo "<p>session.cookie_path: " . ini_get('session.cookie_path') . "</p>";
echo "<p>session.cookie_httponly: " . ini_get('session.cookie_httponly') . "</p>";
echo "<p>session.use_only_cookies: " . ini_get('session.use_only_cookies') . "</p>";
echo "<p>session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "</p>";
echo "<p>session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "</p>";

echo "<h2>Test Actions</h2>";
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'set') {
        $_SESSION['test_value'] = 'Session is working!';
        $_SESSION['test_time'] = time();
        echo "<p style='color: green;'>✓ Session variables set!</p>";
        echo "<p><a href='session_test.php'>Refresh to check if it persists</a></p>";
    } elseif ($_GET['action'] === 'destroy') {
        session_destroy();
        echo "<p style='color: red;'>✓ Session destroyed!</p>";
        echo "<p><a href='session_test.php'>Refresh to start new session</a></p>";
    }
} else {
    echo "<p><a href='session_test.php?action=set'>Set Test Session</a></p>";
    echo "<p><a href='session_test.php?action=destroy'>Destroy Session</a></p>";
}

echo "<h2>Login Status Check</h2>";
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "<p style='color: green; font-weight: bold;'>✓ USER IS LOGGED IN</p>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
    echo "<p>User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "</p>";
    echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ USER IS NOT LOGGED IN</p>";
}

echo "<hr>";
echo "<p><small>Current Time: " . date('Y-m-d H:i:s') . "</small></p>";
?>