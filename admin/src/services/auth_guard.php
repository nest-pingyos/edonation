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
    // Development bypass (REMOVE or comment out to enable login page)
    // if (defined('APP_ENV') && APP_ENV === 'development')
    //     return;

    // Pages that don't need authentication
    $bypassPages = [
        'login.php',
        'dev-login.php',
        'logout.php',
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
        header("Location: {$base}/admin/src/login.php");
        exit;
    }
}

// Execute guard
authGuard();
