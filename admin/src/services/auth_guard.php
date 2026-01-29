<?php
/**
 * Auth Guard - Strict Access Control for Admin
 * 
 * Enforces authentication recursively for all admin pages.
 * Redirects to login if no valid session exists.
 */

// 1. Ensure session is started
require_once __DIR__ . '/session.php';

function authGuard(): void
{
    // Development bypass
    if (defined('APP_ENV') && APP_ENV === 'development')
        return;

    // Pages that don't need authentication
    $bypassPages = [
        'auth/login.php',
        'auth-callback.php',
        'auth-signin.php'
    ];

    $currentPath = $_SERVER['PHP_SELF'];
    foreach ($bypassPages as $page) {
        if (strpos($currentPath, $page) !== false) {
            return;
        }
    }

    // 2. Check if user is logged in
    if (!isLoggedIn() || isSessionExpired()) {
        // Clear session and redirect to login
        logoutSession();

        $base = defined('BASE_PATH') ? BASE_PATH : '';
        header("Location: {$base}/admin/src/auth/login.php");
        exit;
    }
}

// Execute guard
authGuard();
