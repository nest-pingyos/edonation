<?php
/**
 * eDonation Admin Configuration
 * 
 * รองรับ API แยก domain
 * 
 * @package eDonation Admin
 * @version 2.0
 */

// Prevent direct access
if (!defined('ADMIN_ROOT')) {
    define('ADMIN_ROOT', dirname(__DIR__));
}

// Load environment file if exists
$envFile = dirname(ADMIN_ROOT, 2) . '/.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $envVars[$name] = $value;
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Handle variable interpolation for env vars
foreach ($envVars as $key => $value) {
    if (preg_match_all('/\$\{([^}]+)\}/', $value, $matches)) {
        foreach ($matches[1] as $varName) {
            if (isset($envVars[$varName])) {
                $value = str_replace('${' . $varName . '}', $envVars[$varName], $value);
            }
        }
        $envVars[$key] = $value;
        putenv("$key=$value");
    }
}

// ===== Domain & URL Configuration =====
// App Domain (Web & Admin)
define('APP_DOMAIN', getenv('APP_DOMAIN') ?: 'http://localhost');

// API Domain (can be separate from App Domain)
define('API_DOMAIN', getenv('API_DOMAIN') ?: APP_DOMAIN);

// Base paths
define('BASE_PATH', getenv('BASE_PATH') ?: '/edonation');
define('API_BASE_PATH', getenv('API_BASE_PATH') ?: BASE_PATH . '/api');

// ===== Full URLs =====
define('APP_URL', APP_DOMAIN . BASE_PATH);
define('API_URL', API_DOMAIN . API_BASE_PATH . '/v1');

// Admin paths
define('ADMIN_PATH', BASE_PATH . '/admin/src');
define('WEB_PATH', BASE_PATH);

// Relative paths (for same-domain navigation)
define('ADMIN_URL_RELATIVE', ADMIN_PATH);
define('API_URL_RELATIVE', BASE_PATH . '/api/v1');

// Application Config
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_NAME', 'eDonation Admin');
define('APP_VERSION', '2.0.0');
define('APP_DESCRIPTION', 'ระบบจัดการการบริจาค มหาวิทยาลัยเชียงใหม่');

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

/**
 * Check if API is on separate domain
 * @return bool
 */
function isApiSeparateDomain(): bool
{
    return API_DOMAIN !== APP_DOMAIN;
}

/**
 * Get API endpoint URL (absolute, for JS/AJAX calls)
 * @param string $endpoint (e.g., '/projects', '/donations/1')
 * @return string Full API URL
 */
function getApiUrl(string $endpoint = ''): string
{
    $endpoint = ltrim($endpoint, '/');
    return API_URL . ($endpoint ? '/' . $endpoint : '');
}

/**
 * Get API base URL for JavaScript
 * @return string
 */
function getApiBaseForJs(): string
{
    return API_URL;
}
