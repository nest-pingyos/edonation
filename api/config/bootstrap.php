<?php
/**
 * Bootstrap - eDonation API
 */

// 1. Load Environment Configuration
require_once __DIR__ . '/env.php';

// 2. Load Database Service
require_once __DIR__ . '/database.php';

// 3. Load Common Helpers & Middlewares
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// 4. Set Session Configuration (Unify with Admin)
$sessionName = 'edonation_admin';
if (session_name() !== $sessionName) {
    session_name($sessionName);
}

// Session security parameters
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$isLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $isHttps && !$isLocal,
        'cookie_samesite' => 'Lax'
    ]);
}