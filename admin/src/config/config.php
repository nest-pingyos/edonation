<?php
/**
 * eDonation Admin Configuration
 * 
 * @package eDonation Admin
 */

// Prevent direct access
if (!defined('ADMIN_ROOT')) {
    define('ADMIN_ROOT', dirname(__DIR__));
}

// Load environment file if exists
$envFile = dirname(ADMIN_ROOT, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Application Config
define('APP_NAME', 'eDonation Admin');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'ระบบจัดการการบริจาค มหาวิทยาลัยเชียงใหม่');

// Paths
define('BASE_PATH', getenv('BASE_PATH') ?: '/edonation');
define('ADMIN_PATH', BASE_PATH . '/admin/src');
define('API_PATH', BASE_PATH . '/api/v1');
define('WEB_PATH', BASE_PATH . '/web');

// URLs
define('ADMIN_URL', ADMIN_PATH);
define('API_URL', API_PATH);

// Database Config (MySQL)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'edonation_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Session Config
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'edonation_admin');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Thai Buddhist Year offset
define('BE_OFFSET', 543);

// Pagination defaults
define('ITEMS_PER_PAGE', 20);
