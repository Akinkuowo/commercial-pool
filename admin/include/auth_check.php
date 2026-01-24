<?php
// admin/includes/auth_check.php

function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin_login.php?session=expired');
        exit;
    }
    
    // Check session timeout (24 hours)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
        session_unset();
        session_destroy();
        header('Location: ../admin_login.php?session=expired');
        exit;
    }
    
    $_SESSION['last_activity'] = time();
}

function isAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

function isEditor() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'editor';
}

function requireAdmin() {
    checkAdminAuth();
    if (!isAdmin()) {
        header('Location: dashboard.php?error=insufficient_permissions');
        exit;
    }
}

function getAdminName() {
    return $_SESSION['admin_name'] ?? 'User';
}

function getAdminRole() {
    return $_SESSION['admin_role'] ?? 'editor';
}

function getAdminInitials() {
    $name = getAdminName();
    $parts = explode(' ', $name);
    if (count($parts) >= 2) {
        return strtoupper($parts[0][0] . $parts[1][0]);
    }
    return strtoupper(substr($name, 0, 1));
}
?>