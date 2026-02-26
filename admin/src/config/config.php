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
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Determine BASE_PATH
$envBasePath = getenv('BASE_PATH') ?: ($_ENV['BASE_PATH'] ?? null);

if ($envBasePath === null) {
    // Auto-detect BASE_PATH if not set in .env
    $script_path = str_replace('\\', '/', dirname(ADMIN_ROOT, 2));
    $root_path = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');

    if (empty($root_path)) {
        $envBasePath = '/edonation';
    } else {
        $envBasePath = str_replace($root_path, '', $script_path);
        if ($envBasePath && $envBasePath[0] !== '/') {
            $envBasePath = '/' . $envBasePath;
        }
    }
}
define('BASE_PATH', rtrim($envBasePath ?: '/edonation', '/'));

// Full URLs
define('APP_URL', "{$protocol}://{$host}" . BASE_PATH);
define('API_URL', APP_URL . '/api/v1');

// Deprecated constants (keep for compatibility if needed, but aim to use APP_URL/API_URL)
define('APP_DOMAIN', "{$protocol}://{$host}");
define('API_DOMAIN', APP_DOMAIN);
define('API_BASE_PATH', BASE_PATH . '/api');

// Admin paths
if (!defined('ADMIN_PATH'))
    define('ADMIN_PATH', BASE_PATH . '/admin/src');
if (!defined('WEB_PATH'))
    define('WEB_PATH', BASE_PATH);

// Relative paths (for same-domain navigation)
if (!defined('ADMIN_URL_RELATIVE'))
    define('ADMIN_URL_RELATIVE', ADMIN_PATH);
if (!defined('API_URL_RELATIVE'))
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
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', (int) (getenv('SESSION_LIFETIME') ?: 28800)); // 8 hours default
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'edonation_admin');
}

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// ===== JWT Configuration (CRITICAL SECURITY) =====
// JWT_SECRET must be a strong random string (32+ bytes / 64+ hex characters)
$jwtSecret = getenv('JWT_SECRET');

// Validate JWT_SECRET in production
if (APP_ENV === 'production') {
    if (empty($jwtSecret) || strlen($jwtSecret) < 32) {
        error_log('SECURITY ERROR: JWT_SECRET is missing or too short! Generate with: php -r "echo bin2hex(random_bytes(32));"');
        die('Security configuration error. Please contact administrator.');
    }

    // Check for default/weak secrets
    $weakSecrets = [
        'your-dev-secret-key-for-edonation-2025',
        'secret',
        'password',
        'changeme',
        'GENERATE_NEW_SECRET_WITH_COMMAND_ABOVE'
    ];
    if (in_array($jwtSecret, $weakSecrets, true)) {
        error_log('SECURITY ERROR: JWT_SECRET is using a weak default value!');
        die('Security configuration error. Please contact administrator.');
    }
}

define('JWT_SECRET', $jwtSecret ?: 'dev-only-secret-' . md5(__FILE__));
define('JWT_EXPIRE', (int) (getenv('JWT_EXPIRE') ?: 28800)); // 8 hours default

// ===== Secure Cookie Configuration =====
// Force HTTPS cookies in production
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$forceSecureCookie = getenv('COOKIE_SECURE') === 'true' || (APP_ENV === 'production' && $isHttps);

define('COOKIE_SECURE', $forceSecureCookie);
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', getenv('COOKIE_SAMESITE') ?: 'Lax');

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
